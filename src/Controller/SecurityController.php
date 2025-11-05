<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // si après la première étape (login et mdp) nous avons un user
        // alors redirection vers la page de saisie du code d'authentification de Google Authenticator
        if ($this->getUser()) {
            // dans security.yaml, nous avons indiqué la route 2fa_login 
            // dans scheb_2fa.yaml, nous avons indiqué le template: security/2fa_form.html.twig 
            return $this->redirectToRoute('2fa_login');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/2fa/inProgress', name: '2fa_in_progress')]
    public function accessibleDuring2fa(): Response
    {
        // à compléter en cas de besoin
        return new Response('This page is accessible during 2fa');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
