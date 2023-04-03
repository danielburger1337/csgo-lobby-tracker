<?php declare(strict_types=1);

namespace App\Controller;

use danielburger1337\SteamOpenId\SteamOpenID;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: 'GET')]
    public function login(SteamOpenID $steamOpenID): Response
    {
        if ($this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED)) {
            return $this->redirectToRoute('app_home');
        }

        return new RedirectResponse($steamOpenID->constructCheckIdSetupUri());
    }

    #[Route('/login/callback', name: 'app_login_callback', methods: 'GET')]
    public function callback(): void
    {
        throw new \LogicException('This route should have been intercepted by the security layer.');
    }

    #[Route('/logout', name: 'app_logout', methods: 'GET')]
    public function logout(): void
    {
        throw new \LogicException('This route should have been intercepted by the security layer.');
    }
}
