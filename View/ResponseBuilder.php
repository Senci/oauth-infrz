<?php

namespace Infrz\OAuth\View;

use Infrz\OAuth\Model\ErrorCodes;
use Infrz\OAuth\Control\Security\AuthFactoryInterface;

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
     * Builds an error.
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
     * @param array $client
     */
    public function buildClientPage($client)
    {
        exit($this->twig->render('client_page.html.twig', array('client' => $client)));
    }

    /**
     * Builds the new client page.
     */
    public function buildNewClient()
    {
        exit($this->twig->render('client_new.html.twig'));
    }

    /**
     * Builds the client edit page.
     *
     * @param array $client
     */
    public function buildClientEditPage($client)
    {
        exit($this->twig->render('client_edit_page.html.twig', array('client' => $client)));
    }

    /**
     * Builds an authorize page.
     *
     * @param $client
     * @param $scopes
     */
    public function buildAuthorize($client, $scopes)
    {
        exit($this->twig->render('authorize.html.twig', array('client' => $client, 'scopes' => $scopes)));
    }

    /**
     * Builds a login page.
     *
     * @param $redirect
     * @param $error_code
     */
    public function buildLogin($redirect = '%2F', $error_code = false)
    {
        $error = $error_code ? $this->getError($error_code) : null;

        $args = array('redirect' => $redirect);
        if ($error) {
            $args['error'] = $error;
        }

        exit($this->twig->render('login.html.twig', $args));
    }

    /**
     * Builds a login success page.
     *
     * @param $redirect
     * @param $error_code
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
     *
     * @param AuthFactoryInterface $authFactory
     */
    public function addTwigGlobals()
    {
        $this->twig->addGlobal('user', $this->authFactory->getUser());
        $this->twig->addGlobal('is_client_moderator', $this->authFactory->isClientModerator());
    }
}
