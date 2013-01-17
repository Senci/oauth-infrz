<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\View\ResponseBuilder;
use Infrz\OAuth\Model\DatabaseWrapper;
use Infrz\OAuth\Control\Security\AuthFactoryInterface;

abstract class AbstractController
{
    protected $responseBuilder;
    protected $db;
    /* @var $authFactory AuthFactoryInterface */
    protected $authFactory;

    public function __construct($authFactory)
    {
        $this->db = new DatabaseWrapper();
        $this->authFactory = $authFactory;
        $this->responseBuilder = new ResponseBuilder($this->authFactory);
    }

    /**
     * Checks if the request method is GET. Builds an error otherwise.
     */
    protected function isGetRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->responseBuilder->buildError('not_found');
        }
    }

    /**
     * Checks if the request method is POST. Builds an error otherwise.
     *
     * @param bool $checkPageToken checks for a page token if true
     */
    protected function isPostRequest($checkPageToken = true)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->responseBuilder->buildError('not_found');
        }
        if ($checkPageToken) {
            $this->checkPageToken();
        }
    }

    /**
     * Checks whether the page_token is valid. Builds an error otherwise.
     * Checks for the POST-Value 'page_token' (all calls which are changing something should be declared as POST).
     */
    protected function checkPageToken()
    {
        $page_token = isset($_POST['page_token']) ? $_POST['page_token'] : false;
        $page_token = $this->db->getPageTokenByToken($page_token);
        $user = $this->authFactory->getUser();
        if (!$page_token or !$user or $page_token->user_id != $user->id) {
            $this->responseBuilder->buildError('no_permission');
        }
        if ($page_token->expires_at <= time()) {
            $e = 'Unfortunately your Page-Token has expired! Please go back, reload the page and try again';
            $this->responseBuilder->buildError('no_permission', $e);
        }
        $this->db->deletePageToken($page_token->token);
    }

    /**
     * The main action. Gets executed on "/{module-name}" call
     */
    abstract public function mainAction();
}
