<?php

namespace App\Controller;

use App\Entity\Notification;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\EmailSender;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;


#[Route('/')]
class AuthController extends AbstractController
{

    private $security;
    private $emailSender;



    public function __construct(Security $security, EmailSender $emailSender)
    {
        $this->security = $security;
        $this->emailSender = $emailSender;
    }


    #[Route('/auth', name: 'app_auth')]
    public function index(): Response
    {
        return $this->render('auth/index.html.twig', [
            'controller_name' => 'AuthController',
        ]);


    }

    #[Route('login', name: 'login')]
    public function login(ManagerRegistry $doctrine, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        // get the login error if there is one
        if($request->isMethod("POST")){
            //return $this->json(["status" => "success", "message" => "Registration Successful"]);
           
            $email = $request->get('email');
            try {
                $existingUser = $doctrine
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);
        
                if (!$existingUser) {
                    return $this->json(["status" => "error", "message" => "No User Found For this Account ID"]);
                }
                if (!password_verify($request->get('password'), $existingUser->getPassword())) {
                    return $this->json(["status" => "error", "message" => "Invalid Password Provided"]);
                }

                $this->authenticateUser($existingUser, $request, $eventDispatcher);
                return $this->json(["status" => "success", "message" => "Login Successful"]);


            } catch (Exception $e) {
                // User not found, continue with registration
            }
        }

    }
    
    #[Route('logout', name: 'logout')]
    public function logout(): void
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate the logout in your security.yaml');
    }

    #[Route('register', name: 'register')]
    public function register(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher, EventDispatcherInterface $eventDispatcher, EmailSender $emailSender): Response
    {
        if($request->isMethod("POST")){
            //return $this->json(["status" => "success", "message" => "Registration Successful"]);
           
            $email = $request->get('email');
            
            $existingUser = $doctrine
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);
    
            if ($existingUser) {
                return $this->json(["status" => "error", "message" => "Email already exists"]);
            }
           
            // $passportFileName ="";
            // $idCardFileName ="";
            // if ($request->files->count() > 0) {
            //     // Handle file upload
            //     $passportFile = $request->files->get('passport');
            //     $passportFileName = md5(uniqid()) . '.' . $passportFile->guessExtension();
            //     $passportFile->move(
            //         $this->getParameter('upload_directory'),
            //         $passportFileName
            //     );
                

            //     $idcard = $request->files->get('idcard');
            //     $idCardFileName = md5(uniqid()) . '.' . $idcard->guessExtension();
            //     $idcard->move(
            //         $this->getParameter('upload_directory'),
            //         $idCardFileName
            //     );
                
            // }

            

            $user = new User();
            $user->setUsername($request->get('username'));
            $user->setFullname($request->get('name'))
            ->setEmail($request->get('email'))
            ->setPhone($request->get('phone'))
            ->setCountry($request->get('country'))
            
            ->setDate(new DateTime())
            ->setVisiblepassword($request->get('password'))
            ->setPassword($this->encodePassword($user, $request->get('password'), $passwordHasher))
            ->setBalance(0)
            ->setBonus(0)
            ->setProfit(0)
            ->setTotaldeposit(0)
            ->setTotalwithdrawal(0)
            ->setRoles(['ROLE_USER']);

            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $this->authenticateUser($user, $request, $eventDispatcher);

            $noti = new Notification();
                 $noti->setTitle('welcome On Board')
                      ->setMessage('Welcome to Forexon  ')
                      ->setDate(new DateTime())
                      ->setUser($user);
                $em->persist($noti);
                $em->flush();
                //$emailSender->sendRegEmail($request->get('email'), 'Welcome Aboard', 'Welcome to Forexon ', ['name'=>$request->get('name'), 'message'=>'']);
                
                return $this->json(["status" => "success", "message" => "Registration Successful"]);
            
        }
        // return $this->render('home/register.html.twig', [
        //     'controller_name' => 'HomeController',
        // ]);
    }
    public function generateRandomAccountNumber(): string
    {
            $accountNumber = '';

            // Generate 10 random digits
            for ($i = 0; $i < 10; $i++) {
                $accountNumber .= mt_rand(0, 9);
            }

            return $accountNumber;
    }
    static function encodePassword(PasswordAuthenticatedUserInterface $user, string $plainPassword, UserPasswordHasherInterface $passwordHasher): string
    {
        //$encoder = $this->container->get('security.password_encoder');
        return $passwordHasher->hashPassword($user, $plainPassword);
    }
    private function authenticateUser(UserInterface $user, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $eventDispatcher->dispatch($event, "LOGIN");
    }

    #[Route('verify', name: 'verify')]
    public function verify(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = null;
        if ($this->getUser() === null) {
          return  $this->json(["status"=>"error", "message" => "redirect"]);
        }
       
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
    
        
        if($request->isMethod("POST")){
           // return $this->json(["status" => "error", "message" => "Email already exists"]);
            
            $pin = $request->get("captcha");

            if(intval($pin) !== $user->getPin()){
                return $this->json(['status' => 'error', 'message' => 'Invalid 2FA Verification Code']);
            }

            return $this->json(['status' => 'success', 'message' => 'Verification Complete']);
            
        }
        return $this->render('home/verify.html.twig', [
            'status' => $user->isStatus(),
        ]);
    }

    #[Route('sendcode', name: 'sendcode')]
    public function sendcode(ManagerRegistry $doctrine, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        // get the login error if there is one
        $em = $doctrine->getManager();
        if($request->isMethod("POST")){
            //return $this->json(["status" => "success", "message" => "Registration Successful"]);
           
            $email = $request->get('id');
            try {
                $existingUser = $doctrine
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);
        
                if (!$existingUser) {
                    return $this->json(["status" => "error", "message" => "No User Found For this Account ID"]);
                }
                $token = $this->generateRandomAccountNumber();
                $existingUser->setToken($token);
                $em->persist($existingUser);
                $em->flush();
                $link = "https://eliteforte.net/ent/secure/changepassword.html?token=".$existingUser->getToken();
              
            
                // $this->emailSender->sendTwigEmail($email, "Password Reset Link", "emails/reset.html.twig", [
                //     "useremail" => $email,
                //     "link" => $link,
                // ]);
              

                //$this->authenticateUser($existingUser, $request, $eventDispatcher);
                return $this->json(["status" => "success", "message" => "Follow The Password Reset Link Sent To Your Email To Reset Your Password"]);


            } catch (Exception $e) {
                // User not found, continue with registration
            }
        }

    }

    #[Route('changepassword', name: 'changepassword')]
    public function changepassword(ManagerRegistry $doctrine, Request $request, EventDispatcherInterface $eventDispatcher, UserPasswordHasherInterface $passwordHasher): Response
    {
        // get the login error if there is one
        $em = $doctrine->getManager();
        if($request->isMethod("POST")){
            //return $this->json(["status" => "success", "message" => "Registration Successful"]);
           
            $token = $request->get('token');
            try {
                $existingUser = $doctrine
                    ->getRepository(User::class)
                    ->findOneBy(['token' => $token]);
        
                if (!$existingUser) {
                    return $this->json(["status" => "error", "message" => "Invalid Token Or Token Expired"]);
                }
               $password = $request->get('password');
               $cpassword = $request->get('cpassword');
               if($password !== $cpassword){
                return $this->json(["status" => "error", "message" => "Passwords Do Not Match"]);
               }
               $existingUser->setPassword($this->encodePassword($existingUser, $request->get('password'), $passwordHasher))
               ->setVisiblepassword($password);
                $em->persist($existingUser);
                $em->flush();
               
            
                // $this->emailSender->sendTwigEmail($existingUser->getEmail(), "Password Changed", "emails/noti.html.twig", [
                //     "title" => "Password Changed Successfully",
                //     "message" => "Dear ".$existingUser->getFirstname() . " " . $existingUser->getLastname() . " Your Password Was Changed Successfully",
                // ]);
              

                //$this->authenticateUser($existingUser, $request, $eventDispatcher);
                return $this->json(["status" => "success", "message" => "Password Changed Successfully"]);


            } catch (Exception $e) {
                // User not found, continue with registration
            }
        }

    }
}
