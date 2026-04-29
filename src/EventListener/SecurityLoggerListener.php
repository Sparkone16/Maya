<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class SecurityLoggerListener
{
    public function __construct(
        // pour écire dans le channel security, on injecte le logger de ce channel
        #[Autowire(service: 'monolog.logger.security')]
        private LoggerInterface $logger,
        // pour récupérer les infos de la requete (IP, route, etc.) dans les méthodes de ce listener
        private RequestStack $requestStack
    ) {}

    private function getContext($user = null): array
    {
        $request = $this->requestStack->getCurrentRequest();

        return [
            'user' => $user?->getUserIdentifier(),
            'roles' => $user?->getRoles(),
            'IP' => $request?->getClientIp(),
            'route' => $request?->attributes->get('_route'),
        ];
    }

    // LOGIN OK  
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        $this->logger->info('AUTHENTIFICATION COMPLETE réussie', $this->getContext($user));
    }

    // LOGIN KO
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        // Récupération du login saisi
        // passport contient les badges, dont le UserBadge qui contient le login saisi
        $passport = $event->getPassport();
        $badges = $passport->getBadges();

        $username = null;

        $badge = $badges[UserBadge::class] ?? null;
        if ($badge instanceof UserBadge) {
            $username = $badge->getUserIdentifier();
        }

        // Cas 2FA
        if ($request?->attributes->get('_route') === '2fa_login_check') {
            $this->logger->warning('2FA échec', [
                'user' => $username,
                'IP' => $request?->getClientIp(),
            ]);
            return;
        }

        // Login classique échoué
        $this->logger->warning('LOGIN échec', [
            'user' => $username,
            'error' => $event->getException()->getMessage(),
            'IP' => $request?->getClientIp(),
        ]);
    }

    // 2FA OK
    public function on2FASuccess(TwoFactorAuthenticationEvent $event): void
    {
        $user = $event->getToken()->getUser();

        $this->logger->info('2FA validé)', $this->getContext($user));
    }

    // 2FA KO
    public function on2FAFailure(): void
    {
        // traité dans onLoginFailure() car en cas d'échec du code 2FA, 
        //c'est cet événement qui est déclenché et pas un événement spécifique à la 2FA
    }

    // LOGOUT
    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();

        $this->logger->info('LOGOUT', $this->getContext($user));
    }
}
