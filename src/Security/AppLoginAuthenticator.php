<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;

class AppLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UrlGeneratorInterface $urlGenerator;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public const LOGIN_ROUTE = 'app_login';

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $request->attributes->get('_route') === self::LOGIN_ROUTE;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');

        $this->logger->debug('Authentication attempt', [
            'email' => $email,
            'session_id' => $request->getSession()->getId(),
        ]);
        dump(['email' => $email, 'password' => '***']);

        return new Passport(
            new UserBadge($email, function ($userIdentifier) {
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['user_email' => $userIdentifier]);
                if ($user) {
                    $this->entityManager->refresh($user); // Prevent Doctrine caching
                    $this->logger->debug('User loaded', [
                        'email' => $userIdentifier,
                        'role' => $user->getUserRole(),
                        'symfony_roles' => $user->getRoles(),
                    ]);
                    dump([
                        'user' => $user->getUserEmail(),
                        'role' => $user->getUserRole(),
                        'symfony_roles' => $user->getRoles(),
                    ]);
                } else {
                    $this->logger->error('User not found', ['email' => $userIdentifier]);
                    dump(['user' => null]);
                    throw new UserNotFoundException();
                }
                return $user;
            }),
            new PasswordCredentials($password),
            [new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token'))]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        $roles = $token->getRoleNames();
        $session = $request->getSession();
        $sessionId = $session->getId();

        $this->logger->info('Authentication success', [
            'email' => $user instanceof User ? $user->getUserEmail() : 'Unknown',
            'roles' => $roles,
            'session_id' => $sessionId,
        ]);
        dump([
            'email' => $user instanceof User ? $user->getUserEmail() : 'Unknown',
            'roles' => $roles,
            'session_id' => $sessionId,
        ]);

        // Validate session
        if (!$session->isStarted()) {
            $session->start();
            $this->logger->warning('Session restarted', ['new_session_id' => $session->getId()]);
        }

        if (in_array('ROLE_ADMIN', $roles, true)) {
            $this->logger->info('Redirecting admin', [
                'route' => 'app_admin_dashboard',
                'email' => $user instanceof User ? $user->getUserEmail() : 'Unknown',
            ]);
            dump(['redirect' => 'Admin to app_admin_dashboard']);
            return new RedirectResponse($this->urlGenerator->generate('app_admin_dashboard'));
        }
        if (in_array('ROLE_COACH', $roles, true)) {
            $this->logger->info('Redirecting coach', [
                'route' => 'app_user_index',
                'email' => $user instanceof User ? $user->getUserEmail() : 'Unknown',
            ]);
            dump(['redirect' => 'Coach to app_user_index']);
            return new RedirectResponse($this->urlGenerator->generate('app_user_index'));
        }
        if (in_array('ROLE_ATHLETE', $roles, true) || in_array('ROLE_MEDICAL', $roles, true)) {
            $this->logger->info('Redirecting athlete/medical', [
                'route' => 'app_home',
                'email' => $user instanceof User ? $user->getUserEmail() : 'Unknown',
            ]);
            dump(['redirect' => 'Athlete/Medical to app_home']);
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }

        $this->logger->warning('Fallback redirect', [
            'email' => $user instanceof User ? $user->getUserEmail() : 'Unknown',
            'roles' => $roles,
        ]);
        dump(['redirect' => 'Fallback to app_home']);
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->logger->error('Authentication failure', [
            'error' => $exception->getMessage(),
            'session_id' => $request->getSession()->getId(),
        ]);
        dump(['error' => $exception->getMessage()]);
        return new RedirectResponse(
            $this->urlGenerator->generate(self::LOGIN_ROUTE, ['error' => $exception->getMessageKey()])
        );
    }
}