<?php

// src/Security/TokenAuthenticator.php

/*
 * This authenticator authenticates against DigiSpoof
 *
 */

namespace App\Security;

use App\Security\User\AuthenticationUser;
use App\Service\AuthenticationService;
use Conduction\CommonGroundBundle\Security\User\CommongroundUser;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class BasicAuthAuthenticator extends AbstractGuardAuthenticator
{

    private $params;
    private $commonGroundService;
    private $router;
    private TokenStorageInterface $tokenStorage;
    private SessionInterface $session;
    private AuthenticationService $authenticationService;

    public function __construct(ParameterBagInterface $params, CommonGroundService $commonGroundService, RouterInterface $router, SessionInterface $session, TokenStorageInterface $tokenStorage, AuthenticationService $authenticationService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationService = $authenticationService;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return 'app_user_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'])|| !isset($data['password'])) {
            throw New BadRequestException('Username and password are required');
        }

        return $data;

    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $users = $this->commonGroundService->getResourceList(['component' => 'uc', 'type' => 'users'], ['username' => $credentials['username']]);

        if (count($users) === 0) {
            return;
        }

        $user = $this->commonGroundService->createResource(array($credentials['username'], $credentials['password']), ['component' => 'uc', 'type' => 'login']);

        var_dump($user);
        die;

        return new AuthenticationUser(
            $credentials['username'],
            '',
            $credentials['givenName'],
            $credentials['familyName'],
            $credentials['givenName'] . " " . $credentials['familyName'],
            null,
            ['ROLE_USER'],
            $credentials['email']
        );
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $backUrl = $this->session->get('backUrl');

        $this->session->remove('backUrl');
        $this->session->remove('method');
        $this->session->remove('identifier');

        return new RedirectResponse($backUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse($this->router->generate(
            'app_user_authenticate',
            ['method' => $this->session->get('method'), 'identifier' => $this->session->get('identifier')]));
    }

    /**
     * Called when authentication is needed, but it's not sent.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($this->params->get('app_subpath') && $this->params->get('app_subpath') != 'false') {
            return new RedirectResponse('/'.$this->params->get('app_subpath').$this->router->generate('app_user_digispoof', []));
        } else {
            return new RedirectResponse($this->router->generate('app_user_digispoof', ['response' => $request->request->get('back_url'), 'back_url' => $request->request->get('back_url')]));
        }
    }

    public function supportsRememberMe()
    {
        return true;
    }

    protected function getLoginUrl()
    {
        if ($this->params->get('app_subpath') && $this->params->get('app_subpath') != 'false') {
            return '/'.$this->params->get('app_subpath').$this->router->generate('app_user_digispoof', [], UrlGeneratorInterface::RELATIVE_PATH);
        } else {
            return $this->router->generate('app_user_digispoof', [], UrlGeneratorInterface::RELATIVE_PATH);
        }
    }
}
