<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\View\ResponseBuilder;
use Infrz\OAuth\Model\DatabaseWrapper;
use Infrz\OAuth\Control\Security\LDAPAuthFactory;

abstract class AbstractController
{
    protected $responseBuilder;
    protected $database;
    protected $authFactory;
    const LDAP_PORT = 636;
    const LDAP_HOST = 'ldaps://fbidc2.informatik.uni-hamburg.de';


    public function __construct()
    {
        $this->responseBuilder = new ResponseBuilder();
        $this->database = new DatabaseWrapper();
        $this->authFactory = new LDAPAuthFactory(self::LDAP_HOST, self::LDAP_PORT);
    }

    /**
     * The main action. Gets executed on "/{module-name}" call
     */
    abstract public function mainAction();
}
