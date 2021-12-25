<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    /**
     * @Route("/emailForm", name="emailForm")
     * @Route("/emailSend", name="emailSend")
     */
    public function email(MailerInterface $mailer, Request $request)
    {

        if (!empty($_POST)):

            $mess = $request->request->get('message');
            $nom = $request->request->get('surname');
            $prenom = $request->request->get('name');
            $motif = $request->request->get('need');
            $from = $request->request->get('email');

            $email = (new TemplatedEmail())
                ->from('hello@example.com')
                ->to('jeanmidupuis978@gmail.com')
                ->subject($motif)
                ->text('Sending emails is fun again!')
                ->htmlTemplate('security/template_email.html.twig');
            $cid = $email->embedFromPath('uploads/logo.png', 'logo');

            // pass variables (name => value) to the template
            $email->context([
                'message' => $mess,
                'nom' => $nom,
                'prenom' => $prenom,
                'subject' => $motif,
                'from' => $from,
                'cid' => $cid,
                'liens' => 'https://127.0.0.1:8000',
                'objectif' => 'Accéder au site'

            ]);

            $mailer->send($email);


            return $this->redirectToRoute("home");



        endif;

        return $this->render('security/form_email.html.twig');

    }

    /**
     * @Route("/resetPassword", name="resetPassword")
     */
    public function resetPassword()
    {

        return $this->render('security/resetPassword.html.twig');
    }

    /**
     * @Route("/resetToken", name="resetToken")
     */
    public function resetToken(UserRepository $repository, Request $request, EntityManagerInterface $manager, MailerInterface $mailer)
    {
        $user = $repository->findOneBy(['email' => $request->request->get('email')]);

        if ($user):

            $token = uniqid();
            $user->setToken($token);
            $manager->persist($user);
            $manager->flush();

            $email = (new TemplatedEmail())
                ->from('hello@example.com')
                ->to($request->request->get('email'))
                ->subject('Demande de réinitialisation de mot de passe')
                ->text('Sending emails is fun again!')
                ->htmlTemplate('security/template_email.html.twig');
            $cid = $email->embedFromPath('uploads/logo.png', 'logo');

            // pass variables (name => value) to the template
            $email->context([
                'message' => 'Vous avez fait une demande de réinitialisation de mot de passe, veuillez cliquer sur le liens ci dessous',
                'nom' => "",
                'prenom' => "",
                'subject' => 'demande de réinitialisation',
                'from' => 'onlyMovie@only.com',
                'cid' => $cid,
                'liens' => 'https://127.0.0.1:8000/resetForm?token=' . $token . '&i=' . $user->getId(),
                'objectif' => 'Réinitialiser'

            ]);

            $mailer->send($email);


            $this->addFlash('success', 'Un Email vient de vous être envoyer!');
            return $this->redirectToRoute('login');
        else:
            $this->addFlash('danger', 'Aucun compte existant à cette adresse mail');

            return $this->redirectToRoute('resetPassword');
        endif;


    }


    /**
     * @Route("/resetForm", name="resetForm")
     */
    public function resetForm(UserRepository $repository)
    {

        if (isset($_GET['token'])):
            $user = $repository->findOneBy(['id' => $_GET['i'], 'token' => $_GET['token']]);
            if ($user):

                return $this->render('security/resetForm.html.twig', [
                    'id' => $user->getId()
                ]);

            else:

                $this->addFlash('danger', 'Une erreur s\'est produite, veuillez réiterer votre demande');
                return $this->redirectToRoute('resetPassword');
            endif;


        endif;



    }

    /**
     * @Route("/finalReset", name="finalReset")
     */
    public function finalReset(UserRepository $repository, EntityManagerInterface $manager, Request $request, UserPasswordHasherInterface $hasher)
    {
        $user = $repository->find($request->request->get('id'));
        if ($request->request->get('password') == $request->request->get('confirm_password')):


            $mdp=$hasher->hashPassword($user, $request->request->get('password'));
            $user->setPassword($mdp);
            $user->setToken(null);
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé, connectez vous à présent');
            return $this->redirectToRoute('login');

        else:
            $this->addFlash('danger', 'Les mots de passe ne correspondent pas');
            return $this->render('security/resetForm.html.twig', [
                'id' => $user->getId()
            ]);
        endif;


    }


}
