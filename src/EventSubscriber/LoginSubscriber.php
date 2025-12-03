<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        // On injecte le logger de Symfony
        $this->logger = $logger;
    }

    /**
     * On déclare les événements que l'on souhaite écouter
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    /**
     * Cette méthode est appelée quand une connexion réussit (mot de passe valide)
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        // On vérifie que l'utilisateur est bien du type attendu pour récupérer ses infos
        if ($user instanceof UserInterface) {
            // On récupère l'identifiant (souvent l'email)
            $identifier = $user->getUserIdentifier();
            
            // On écrit dans les logs (niveau INFO)
            $this->logger->info(sprintf('Connexion réussie pour l\'utilisateur : %s', $identifier));
        }
    }

    /**
     * Cette méthode est appelée quand une connexion échoue (mauvais mot de passe, utilisateur inconnu...)
     */
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        // On tente de récupérer le nom d'utilisateur saisi (si disponible)
        $passport = $event->getPassport();
        $username = 'inconnu';
        
        if ($passport) {
            $username = $passport->getUser()->getUserIdentifier();
        }

        // On récupère l'erreur pour savoir pourquoi ça a échoué
        $exception = $event->getException();
        $reason = $exception ? $exception->getMessage() : 'Raison inconnue';

        // On écrit dans les logs (niveau ERROR ou WARNING)
        $this->logger->error(sprintf('Échec de connexion pour l\'utilisateur "%s". Raison : %s', $username, $reason));
    }
}