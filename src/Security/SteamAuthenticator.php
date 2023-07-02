<?php declare(strict_types=1);

namespace App\Security;

use danielburger1337\SteamOpenId\Exception\ExceptionInterface;
use danielburger1337\SteamOpenId\SteamOpenID;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class SteamAuthenticator extends AbstractAuthenticator implements InteractiveAuthenticatorInterface, AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly SteamOpenID $steamOpenID,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('GET') && $request->attributes->get('_route') === 'app_login_callback';
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $steamId = $this->steamOpenID->verifyCallback($request->query->all());
        } catch (ExceptionInterface $e) {
            throw new AuthenticationException($e->getMessage(), previous: $e);
        }

        return new SelfValidatingPassport(new UserBadge($steamId), [
            new RememberMeBadge(),
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->getPreferredFormat() === 'html') {
            return new RedirectResponse($this->steamOpenID->constructCheckIdSetupUri());
        }

        return new Response(null, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        if (null !== $authException) {
            return $this->onAuthenticationFailure($request, $authException);
        }

        if ($request->getPreferredFormat() === 'html') {
            return new RedirectResponse($this->steamOpenID->constructCheckIdSetupUri());
        }

        return new Response(null, Response::HTTP_UNAUTHORIZED);
    }

    public function isInteractive(): bool
    {
        return true;
    }
}
