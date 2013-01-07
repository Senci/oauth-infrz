<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Security;

use Infrz\OAuth\Model\User;
use Infrz\OAuth\Model\DatabaseWrapper;
use Infrz\OAuth\Model\WebToken;

class LDAPAuthFactory implements AuthFactoryInterface
{
    protected $host;
    protected $port;
    protected $db;

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
        $this->db = new DatabaseWrapper();
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
        $user = $this->db->getUserByAlias('2king');

        $web_token = $this->db->insertWebToken($user);
        $_SESSION['web_token'] = $web_token->token;

        return $user;
    }

    /**
     * Destroys the current session and signs out the user
     *
     * @return bool true if successful false when there is no open session.
     */
    public function signOut()
    {
        session_destroy();

        return $this->db->deleteWebToken($_SESSION['web_token']);
    }

    /**
     * Returns whether the user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        $web_token = $_SESSION['web_token'];
        $web_token = $this->db->getWebTokenByToken($web_token);
        if (!$web_token instanceof WebToken) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether the user has rights to create and moderate clients.
     *
     * @return bool
     */
    public function isClientModerator()
    {
        return true;
    }

    /**
     * Returns the currently logged in user.
     *
     * @return bool|User false when there is no open session.
     */
    public function getUser()
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        $web_token = $_SESSION['web_token'];
        $web_token = $this->db->getWebTokenByToken($web_token);

        return $this->db->getUserById($web_token->user_id);
    }
}
