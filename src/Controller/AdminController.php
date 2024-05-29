<?php

namespace App\Controller;

use App\Entity\Connect;
use App\Entity\Notification;
use App\Entity\Plan;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\EmailSender;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Flasher\Symfony\Http\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $users = $doctrine->getRepository(User::class)->findAll();
        $deposits = $doctrine->getRepository(Transaction::class)->findBy(["type"=>"deposit", "status"=>"pending"]);
        $withdrawals = $doctrine->getRepository(Transaction::class)->findBy(["type"=>"withdrawal", "status"=>"pending"]);
        $plans = $doctrine->getRepository(Plan::class)->findBy(['complete'=> false]);

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
            'plans' => $plans
        ]);
    }

    #[Route('/profile/{id}', name: 'profileview')]
    public function profile(User $id, ManagerRegistry $doctrine, HttpFoundationRequest $request): Response
    {
        $em = $doctrine->getManager();
        if(null != $request->get('update')){
            $id->setBalance($request->get('balance'))
               ->setBonus($request->get('bonus'))
               ->setProfit($request->get('profit'))
               ->setWithdrawalerrormessage($request->get('error'));
            $em->persist($id);
            $em->flush();
           

           noty()->addSuccess("profile was updated successfully");
            $u = $id->getId();
            return $this->redirectToRoute("admin");

        }
        if(null != $request->get('delete')){
            $em->remove($id);
            $em->flush();

            $this->addFlash(
               'success',
               'user successfully deleted'
            );
            return $this->redirectToRoute('admin');
        }
        return $this->render('admin/profile.html.twig', [
            'user' => $id
        ]);
    }

    #[Route('/withdrawallist', name: 'withdrawallist')]
    public function withdrawals(ManagerRegistry $doctrine, HttpFoundationRequest $request, EmailSender $emailSender): Response
    {
        $withdrawals = $doctrine->getRepository(Transaction::class)->findBy(["type"=>"withdrawal", "status"=>"pending"]);

        $em = $doctrine->getManager();
        if(null != $request->get('approve')){
            $transaction = $doctrine->getRepository(Transaction::class)->find($request->get('id'));
            $transaction->setStatus('approved');
            $em->persist($transaction);
            $user = $transaction->getUser();
            
            $amount = $transaction->getAmount();
            $user->setTotalWithdrawal( $user->getTotalWithdrawal() + $amount );
            $em->persist($user);
            $em->flush();

            $noti = new Notification();
            $noti->setTitle('Withdrawal Complete')
                ->setMessage("your withdrawal was confirmed successfully")
                ->setDate(new DateTime())
                ->setUser($this->getUser());
            $em->persist($noti);
            $em->flush();
            $emailSender->sendDepEmail($user->getEmail(), 'Withdrawal Confirmed', "your withdrawal was confirmed successfully", ['name'=>$user->getFullname(), 'message'=>"your withdrawal of $$amount has been confirmed and deposited to your wallet successfuly"]);
            
            noty()->addSuccess("wihdrawal was successfuly approved");
            return $this->redirectToRoute('withdrawallist');
            
        }
        if(null != $request->get('delete')){
            $transaction = $doctrine->getRepository(Transaction::class)->find($request->get('id')); 
            $transaction->setStatus('declined');
            $em->persist($transaction);

            $noti = new Notification();
            $noti->setTitle('Withdrawal Declined')
                ->setMessage("your withdrawal was declined, please contact support")
                ->setDate(new DateTime())
                ->setUser($this->getUser());
            $em->persist($noti);
            $em->flush();

            noty()->addError("wihdrawal was successfuly declined");
            return $this->redirectToRoute('withdrawallist');
            $em->flush();

            return $this->redirectToRoute('admin');
        }
        return $this->render('admin/withdrawals.html.twig', [
            'withdrawals' => $withdrawals
        ]);
    }

    #[Route('/depositlist', name: 'depositlist')]
    public function deposits(ManagerRegistry $doctrine, HttpFoundationRequest $request, EmailSender $emailSender): Response
    {
        $em = $doctrine->getManager();
        if(null != $request->get('approve')){
            $transaction = $doctrine->getRepository(Transaction::class)->find($request->get('id'));
            $user = $transaction->getUser();
            
            $amount = $transaction->getAmount();
            $user->setBalance($user->getBalance() + $amount)
                 ->setTotalDeposit( $user->getTotalDeposit() + $amount );
            $em->persist($user);
            $transaction->setStatus('approved');
            $em->persist($transaction);

            $em->flush();
            $noti = new Notification();
            $noti->setTitle('Deposit Complete')
                ->setMessage("your deposit was confirmed successfully")
                ->setDate(new DateTime())
                ->setUser($this->getUser());
            $em->persist($noti);
            $em->flush();

            $emailSender->sendDepEmail($user->getEmail(), 'Deposit Confirmed', "your deposit was confirmed successfully", ['name'=>$user->getFullname(), 'message'=>"your deposit of $$amount has been confirmed and deposited to your account successfuly"]);
                   

            noty()->addSuccess("deposit was successfuly approved");
            return $this->redirectToRoute('depositlist');

            
        }
        if($request->get('delete')){
            $transaction = $doctrine->getRepository(Transaction::class)->find($request->get('id')); 
            $transaction->setStatus('declined');
            $em->persist($transaction);
            $noti = new Notification();
            $noti->setTitle('Transaction Decined')
                 ->setMessage("your deposit was declined, please contact support")
                 ->setDate(new DateTime())
                 ->setUser($this->getUser());
            $em->persist($noti);
            $em->flush();

            noty()->addError("deposit was successfuly declined");
            return $this->redirectToRoute('depositlist');

            
        }
        $deposits = $doctrine->getRepository(Transaction::class)->findBy(["type"=>"deposit", "status"=>"pending"]);

        return $this->render('admin/deposits.html.twig', [
            'deposits' => $deposits
        ]);
    }

    
    #[Route('/investments', name: 'investments')]
    public function investments(ManagerRegistry $doctrine, HttpFoundationRequest $request, PaginatorInterface $paginator): Response
    {

        $plans = $doctrine->getRepository(Plan::class)->findAll(); 
        
        $pagination = $paginator->paginate($plans, $request->query->getInt('page', 1), 10);
        return $this->render('admin/invest.html.twig', [
          'plans' => $pagination 
        ]);
    }

}
