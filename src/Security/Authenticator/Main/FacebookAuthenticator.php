<?php

namespace App\Security\Authenticator\Main;

use App\Entity\User;
use App\Event\UserLoggedInViaSocialNetworkEvent;
use App\Utils\Factory\UserFactory;
use App\Utils\Generator\PasswordGenerator;
use App\Utils\Manager\UserManager;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class FacebookAuthenticator extends OAuth2Authenticator
{
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ClientRegistry $clientRegistry, UserManager $userManager, RouterInterface $router, SessionInterface $session, EventDispatcherInterface $eventDispatcher)
    {
        $this->clientRegistry = $clientRegistry;
        $this->router = $router;
        $this->userManager = $userManager;
        $this->session = $session;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return 'connect_facebook_check' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $client = $this->clientRegistry->getClient('facebook_main');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var FacebookUser $facebookUser */
                $facebookUser = $client->fetchUserFromToken($accessToken);

                $email = $facebookUser->getEmail();

                // 1) have they logged in with Facebook before? Easy!
                $existingUser = $this->userManager->getRepository()
                                                  ->findOneBy(['facebookId' => $facebookUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                // 2) do we have a matching user by email?
                $user = $this->userManager->getRepository()->findOneBy(['email' => $email]);

                if (!$user) {
                    $user = UserFactory::createUserFromFacebookUser($facebookUser);

                    $plainPassword = PasswordGenerator::generatePassword();
                    $this->userManager->encodePassword($user, $plainPassword);

                    $event = new UserLoggedInViaSocialNetworkEvent($user, $plainPassword);
                    $this->eventDispatcher->dispatch($event);

                    $this->session->getFlashBag()
                                  ->add('success', 'An email has been sent. Please check your inbox to find password');
                    $this->userManager->save($user);
                }

                // 3) Maybe you just want to "register" them by creating
                // a User object
                $user->setFacebookId($facebookUser->getId());
                $this->userManager->save($user);

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('main_profile_index');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
