<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Security;

use Infrz\OAuth\Model\User;

class LDAPAuthFactory implements AuthFactoryInterface
{
    protected $host;
    protected $port;

    /**
     * Sets the host and port for the required LDAP-Connection.
     *
     * @param $ldap_host
     * @param $ldap_port
     */
    public function __construct($ldap_host, $ldap_port)
    {
        $this->host = sprintf("%s:%s", $ldap_host, $ldap_port);
        $this->port = $ldap_port;
    }

    /**
     * Signs in a user by his credentials
     *
     * @param string $username
     * @param string $password
     * @return User either the User-Object if successful or false on error.
     */
    public function signIn($username, $password)
    {
        // TODO: Implement signIn() method.
        $user = new User();
        $user->alias = '7licina';
        $user->first_name = 'Senad';
        $user->last_name = 'Licina';
        $user->email = '7licina@informatik.uni-hamburg.de';
        $user->id = 1337;

        return $user;
    }

    /**
     * Returns whether the user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        // TODO: Implement isAuthenticated() method.
        return true;
    }

    /**
     * Returns whether the user has rights to create and moderate clients.
     *
     * @return bool
     */
    public function isClientModerator()
    {
        // TODO: Implement isClientModerator() method.
        return true;
    }
}
