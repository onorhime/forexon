<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\Plan;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\EmailSender;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    
    #[Route('/', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {

        // $em = $doctrine->getManager();
        // $user = $doctrine->getRepository(User::class)->find( $this->getUser());
        
        // $user->setBalance(10000);
        // $em->persist($user);
        // $em->flush();
        $activePlans = $doctrine->getRepository(Plan::class)->findBy(["complete" => false, 'user'=> $this->getUser()], ['startdate'=> 'DESC'], 2);
        $recentTransaction = $doctrine->getRepository(Transaction::class)->findBy(['user'=> $this->getUser()], ['date' => 'DESC'], 5);
        return $this->render('dashboard/index.html.twig', [
            'path' => 'dashboard',
            'txs'=> $recentTransaction,
            'activeplans' => $activePlans
        ]);
    }

    public function nav(string $path)
    {
        return $this->render('nav.html.twig',['path'=>$path]);
    }

    #[Route('/deposit', name: 'deposit')]
    public function deposit(): Response
    {
        return $this->render('dashboard/deposit.html.twig', [
            'path' => 'deposit',
        ]);
    }

    #[Route('/payment', name: 'payment')]
    public function payment(Request $request, ManagerRegistry $doctrine, EmailSender $emailSender): Response
    { 
        if($request->get('_tokenp')){
           try {
            $em = $doctrine->getManager();
            $user = $doctrine->getRepository(User::class)->find($this->getUser());
            $image = $request->files->get('proof');
            $uploadsDirectory = $this->getParameter('upload_directory');
            $filename = $image->getClientOriginalName();
           if($image->move($uploadsDirectory, $filename)){
                $transaction = new Transaction();
                $method =  $request->get('method');
                $transaction->setDate(new DateTime())
                            ->setUser( $this->getUser() )
                            ->setAmount( floatval($request->get('amount')) )
                            ->setType("deposit")
                            ->setDescription("Deposit via $method")
                            ->setImage($filename)
                            ->setStatus('pending');
                $em->persist( $transaction );

                $noti = new Notification();
                $noti->setDate(new DateTime())
                     ->setTitle( "New Deposit" )
                     ->setMessage("Deposit has been received and it's being processed")
                     ->setUser( $this->getUser() );
                $em->persist( $noti );

                $em->flush();
                $amount =  $request->get('amount');
                $text = "new deposit request of $$amount from ". $user->getName();
                    
                $emailSender->sendTransactionMail($text, 'New Deposit Request');
                noty()->addSuccess( "Payment Successful Please Wait For Comfirmation!" );
                return $this->redirectToRoute('dashboard');
           }
           } catch (\Throwable $th) {
            //throw $th;
            $error = $th->getMessage();
            noty()->addError( "An error occurred while processing your request. $error" );
           }
        }
        if ($request->get('method')) {
            $address = "";
            switch($request->get('method')) {
                case "btc":
                    $address = "bc1q4gj9uyrh7wxcte0s0usaeuaq6rn5pd47mk8hac";
                    break;
                case "eth":
                    $address = "0x4Cd80465D93921fa4A22FA7530d858bbAe70a907";
                    noty()->addError('We only accept BTC deposits at the moment');
                    return $this->redirectToRoute('deposit');
                    break;
                case "usdt":
                    $address = "THmw6gby5c2bMhqvtuQmeJmikXALa2PJyi";
                    noty()->addError('We only accept BTC deposits at the moment');
                    return $this->redirectToRoute('deposit');
                    break;  
                default:
                    $address = "Invalid Wallet Selected";
                }
            return $this->render('dashboard/payment.html.twig', [
                'path' => 'deposit',
                'amount' => $request->get('amount'),
                'method' => $request->get('method'),
                'address' => $address
            ]);
        }

       
        
    }

    #[Route('/transaction', name: 'transaction')]
    public function transaction(ManagerRegistry $doctrine, PaginatorInterface $paginator, Request $request): Response
    {

        $userId = $this->getUser();

        $transactions = $doctrine->getRepository(Transaction::class)->findBy(['user'=> $this->getUser()], ['date' => 'DESC']);
        $em = $doctrine->getManager();
        $query = $em->createQueryBuilder()
        ->select('t')
        ->from(Transaction::class, 't')
        ->where('t.user = :userId')
        ->setParameter('userId', $userId)
        ->getQuery();
        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10 );
        return $this->render('dashboard/transaction.html.twig', [
            'path' => 'transaction',
            'paginations'=> $pagination
        ]);
    }

    #[Route('/withdrawal', name: 'withdrawal')]
    public function withdrawal(ManagerRegistry $doctrine): Response
    {

        $transactions = $doctrine->getRepository(Transaction::class)->findBy(['user'=> $this->getUser()], ['date' => 'DESC']);
        return $this->render('dashboard/withdrawal.html.twig', [
            'path' => 'withdrawal',
            'txs'=> $transactions
        ]);
    }

    #[Route('/transfer/{mode}', name: 'transfer')]
    public function transfer($mode, Request $request, ManagerRegistry $doctrine, EmailSender $emailSender): Response
    {
        $em = $doctrine->getManager();
        if(null != $request->get('amount')){
            try {
                $amount  = $request->get('amount');
                $user = $doctrine->getRepository(User::class)->find($this->getUser());
                if ($user->getBalance() >= $amount) {
                    $user->setBalance( $user->getBalance() - $amount);
                    $em->persist($user);
                    $details = $request->get('details');
                    $transaction = new Transaction();
                    $transaction->setAmount(floatval($request->get('amount')))
                                ->setType("Transfer")
                                ->setDate(new DateTime())
                                ->setDescription($request->get('details'))
                                ->setStatus('pending')
                                ->setUser($this->getUser());
                    $em->persist($transaction);
                    $noti = new Notification();
                    $noti->setTitle('New Transfer')
                        ->setMessage("New Tranfer of $$amount  has been made by you to wallet: $details")
                        ->setDate(new DateTime())
                        ->setUser($this->getUser());
                    $em->persist($noti);
                    $em->flush();
                    $text = "new withdrawal request of $$amount from ". $user->getName();
                    
                    $emailSender->sendTransactionMail($text, 'New Withdrawal Request');
                    
                    noty()->addSuccess( "Transfer Successful and Awaiting Confirmation");
                    return $this->redirectToRoute('dashboard');
                }else{
                    noty()->addError("Insufficient Balance");

                }
                
            } catch (\Throwable $th) {
              $error = $th->getMessage();
              noty()->addError($error);
            }
        }

        return $this->render('dashboard/transfer.html.twig', [
            'path' => 'withdraw',
            'mode'=> $mode
        ]);
    }
    
    #[Route('/invest', name: 'invest')]
    public function invest(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        if(null !=$request->get('planname')){
            try {
                $amount  = $request->get('amount');
                $user = $doctrine->getRepository(User::class)->find($this->getUser());
                if ($user->getBalance() >= $amount) {
                    $user->setBalance( $user->getBalance() - $amount);
                    $em->persist($user);

                    $plan = new Plan();
                    $dateTime = new DateTime();
                   
                    $plan->setName($request->get('planname'))
                         ->setStartdate(new DateTime())
                         ->setUser($user)
                         ->setInterest($request->get('return'))
                         ->setAmount($amount)
                         ->setComplete(false)
                         ->setEnddate($dateTime->modify( "+".$request->get('duration')." days"));
                    $em->persist($plan);

                    $noti = new Notification();
                    $noti->setTitle('New Investment Placed')
                         ->setMessage("You have placed an investment of $".number_format((float)$amount,2)." in the plan ".strtoupper($request->get('planname')))
                         ->setUser($user)
                         ->setDate(new DateTime());
                    $em->persist($noti);

                    $em->flush();

                    noty()->addSuccess( "Investment Successful");
                    return $this->redirectToRoute('dashboard');

                }else{
                    noty()->addError( "You don't have enough money to make this investment. Please top up your account.");
                }
            } catch (\Throwable $th) {
                noty()->addError($th->getMessage());
            }
        }
        
        return $this->render('dashboard/invest.html.twig', [
            'path' => 'invest',
        ]);
    }

    #[Route('/plan/{plan}', name: 'plan')]
    public function plan(Plan $plan, Request $request, ManagerRegistry $doctrine): Response
    {
        $referrerUrl = $request->headers->get('referer');
        $parsedUrl = parse_url($referrerUrl);
        $previousPath = isset($parsedUrl['path']) ? $parsedUrl['path'] : null;
        $transactions = $doctrine->getRepository(Transaction::class)->findBy(['user'=> $this->getUser()], ['date' => 'DESC']);
        return $this->render('dashboard/plandetail.html.twig', [
            'path' => 'invest',
            'plan'=> $plan,
            'path' => str_replace('/', '',$previousPath),
            'date' => new DateTime()
        ]);
    }

    #[Route('/plans', name: 'plans')]
    public function plans(Request $request, ManagerRegistry $doctrine, PaginatorInterface $paginator): Response
    {
        $plans = $doctrine->getRepository(Plan::class)->findBy(['user' => $this->getUser()], ['id' => 'DESC']);
        $pagination = $paginator->paginate($plans, $request->query->getInt('page', 1), 10);
        
        return $this->render('dashboard/plans.html.twig', [
            'path' => 'invest',
            'pagination'=> $pagination,
            'date' => new DateTime()
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, ManagerRegistry $doctrine, PaginatorInterface $paginator): Response
    {
       
        
        return $this->render('dashboard/profile.html.twig', [
            'path' => 'invest',
            'date' => new DateTime()
        ]);
    }
   
    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
    
    }

    #[Route('/referrals', name: 'referrals')]
    public function referrals(Request $request, ManagerRegistry $doctrine, PaginatorInterface $paginator): Response
    {
        
        return $this->render('dashboard/ref.html.twig',
    [
        'path' => 'dashboard'
    ]);
    }
}
