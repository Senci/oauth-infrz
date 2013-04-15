<?php

namespace Infrz\OAuth\View;

use Infrz\OAuth\Model\ErrorCodes;
use Infrz\OAuth\Control\Security\AuthFactoryInterface;
use Infrz\OAuth\Model\AccessToken;
use Infrz\OAuth\Model\Client;
use Infrz\OAuth\Model\RefreshToken;
use Infrz\OAuth\Model\User;

/**
 * ResponseGenerator generates Responses
 *
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */
class ResponseBuilder
{
    protected $loader;
    protected $twig;
    /* @var AuthFactoryInterface $authFactory */
    protected $authFactory;

    public function __construct(AuthFactoryInterface $authFactory)
    {
        $this->loader = new \Twig_Loader_Filesystem('View');
        $this->twig = new \Twig_Environment($this->loader, array('/cache' => 'cache'));
        $this->authFactory = $authFactory;
        if ($authFactory->isAuthenticated()) {
            $this->addTwigGlobals($authFactory);
        }
    }

    /**
     * Builds an error web page.
     *
     * @param  string $error_code        The error that was triggered
     * @param  string $error_description Additional Information about the error that was produced
     */
    public function buildError($error_code, $error_description = null)
    {
        $error = $this->getError($error_code, $error_description);
        http_response_code($error['http_status']);

        exit($this->twig->render('error.html.twig', array('error' => $error)));
    }

    /**
     * Builds an error as a JSON encoded string.
     *
     * @param  string $error_code        The error that was triggered
     * @param  string $error_description Additional Information about the error that was produced
     */
    public function buildJsonError($error_code, $error_description = null)
    {
        $error = $this->getError($error_code, $error_description);
        $error = array_merge(array('type' => "Error"), $error);
        http_response_code($error['http_status']);

        exit(json_encode($error));
    }


    /**
     * Builds the home page.
     */
    public function buildHome()
    {
        exit($this->twig->render('home.html.twig'));
    }

    /**
     * Builds the clients overview page.
     *
     * @param array $clients
     * @param bool $success an optional success message as an array with 'title' and 'message'
     */
    public function buildClientOverview($clients, $success = false)
    {
        $args = array('clients' => $clients);
        if ($success) {
            $args['success'] = $success;
        }
        exit($this->twig->render('client_overview.html.twig', $args));
    }

    /**
     * Builds the client page.
     *
     * @param Client $client
     * @param string $page_token
     */
    public function buildClient($client, $page_token)
    {
        exit($this->twig->render('client.html.twig', array('client' => $client, 'page_token' => $page_token)));
    }

    /**
     * Builds the new client page.
     * @param string $page_token
     */
    public function buildNewClient($page_token)
    {
        exit($this->twig->render('client_new.html.twig', array('page_token' => $page_token)));
    }

    /**
     * Builds the client edit page.
     *
     * @param Client $client
     * @param string $page_token
     */
    public function buildClientEdit($client, $page_token)
    {
        exit($this->twig->render('client_edit.html.twig', array('client' => $client, 'page_token' => $page_token)));
    }

    /**
     * Builds an authorize page.
     *
     * @param Client $client
     * @param string $page_token
     * @param string $redirect_uri
     */
    public function buildAuthorize($client, $page_token, $redirect_uri)
    {
        $args = array('client' => $client, 'page_token' => $page_token, 'redirect_uri' => $redirect_uri);
        exit($this->twig->render('authorize.html.twig', $args));
    }

    /**
     * Displaying information about the access grant and redirecting to client-site with auth_code.
     *
     * @param Client $client
     * @param string $redirect
     * @param array $scope
     */
    public function buildAuthorizeGranted($client, $redirect, $scope)
    {
        $args = array('client' => $client, 'scope' => $scope, 'redirect' => $redirect);
        exit($this->twig->render('authorize_granted.html.twig', $args));
    }

    /**
     * Builds a JSON encoded access_token response.
     *
     * @param AccessToken $access_token
     * @param RefreshToken $refresh_token
     */
    public function buildAccessToken($access_token, $refresh_token)
    {
        $response = new \StdClass();
        $response->type = 'Access-Token';
        $response->access_token = $access_token->token;
        $response->refresh_token = $refresh_token->token;
        $response->scope = $access_token->scope;
        $response->expires_at = (int) $access_token->expires_at;
        exit(json_encode($response));
    }

    /**
     * Builds a JSON encoded user response.
     *
     * @param User $user
     * @param array $scope
     */
    public function buildUser($user, $scope)
    {
        $response = new \StdClass();
        $response->type = 'User';
        foreach ($scope as $attribute) {
            $response->$attribute = $user->$attribute;
        }
        $response->scope = $scope;
        exit(json_encode($response));
    }

    /**
     * Builds a login page.
     *
     * @param string $redirect
     * @param string $error_code
     * @param Client $client
     */
    public function buildLogin($redirect = '%2F', $error_code = false, $client = false)
    {
        $error = $error_code ? $this->getError($error_code) : null;

        $args = array('redirect' => $redirect);
        if ($error) {
            $args['error'] = $error;
        }
        if($client) {
            $args['client'] = $client;
        }

        exit($this->twig->render('login.html.twig', $args));
    }

    /**
     * Builds a login success page.
     *
     * @param string $redirect
     */
    public function buildLoginSuccess($redirect)
    {
        exit($this->twig->render('login_successful.html.twig', array('redirect' => $redirect)));
    }

    /**
     * Returns an Error by the error_code, alters the description if given.
     *
     * @param string $error_code
     * @param string $error_description
     * @return array The desired Error as array
     */
    protected function getError($error_code, $error_description = false)
    {
        $errors = ErrorCodes::getErrors();
        $error = $errors[$error_code];
        if ($error) {
            if ($error_description) {
                $error['error_description'] = $error_description;
            }
        } else {
            $error = $errors['error_not_found'];
        }

        return $error;
    }

    /**
     * Adds all needed twig-globals
     */
    public function addTwigGlobals()
    {
        $this->twig->addGlobal('user', $this->authFactory->getUser());
        $this->twig->addGlobal('is_client_moderator', $this->authFactory->isClientModerator());
    }
}
