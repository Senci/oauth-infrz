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
    protected $database;
    /* @var $authFactory AuthFactoryInterface */
    protected $authFactory;

    public function __construct($authFactory)
    {
        $this->database = new DatabaseWrapper();
        $this->authFactory = $authFactory;
        $this->responseBuilder = new ResponseBuilder($this->authFactory);
    }

    /**
     * The main action. Gets executed on "/{module-name}" call
     */
    abstract public function mainAction();
}
