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
    public function isGetRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->responseBuilder->buildError('not_found');
        }
    }

    /**
     * Checks if the request method is POST. Builds an error otherwise.
     */
    public function isPostRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->responseBuilder->buildError('not_found');
        }
    }

    /**
     * The main action. Gets executed on "/{module-name}" call
     */
    abstract public function mainAction();
}
