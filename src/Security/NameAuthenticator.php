<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class NameAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === self::LOGIN_ROUTE
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = trim((string) $request->request->get('email', ''));
        $rawPassword = trim((string) $request->request->get('password', ''));

        if ($email === '' || $rawPassword === '') {
            throw new AuthenticationException('Invalid email or password.');
        }

        $normalizedEmail = strtolower($email);
        if (!str_ends_with($normalizedEmail, '@superoffice.com')) {
            throw new AuthenticationException('Invalid email or password.');
        }

        // Accept DOB as YYYY-MM-DD, YYYY/MM/DD or YYYYMMDD and normalize to YYYYMMDD
        if (\preg_match('#^\d{4}-\d{2}-\d{2}$#', $rawPassword)) {
            $password = \str_replace('-', '', $rawPassword);
        } elseif (\preg_match('#^\d{4}/\d{2}/\d{2}$#', $rawPassword)) {
            $password = \str_replace('/', '', $rawPassword);
        } elseif (\preg_match('#^\d{8}$#', $rawPassword)) {
            $password = $rawPassword;
        } else {
            throw new AuthenticationException('Invalid email or password.');
        }

        $user = $this->userRepository->findByWorkEmail($normalizedEmail);

        if (!$user) {
            throw new AuthenticationException('Invalid email or password.');
        }

        $storedPassword = (string) $user->getLoginPassword();
        $normalizedStored = \preg_replace('#[/-]#', '', $storedPassword);

        if ($normalizedStored !== $password) {
            throw new AuthenticationException('Invalid email or password.');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), fn () => $user)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }
}
