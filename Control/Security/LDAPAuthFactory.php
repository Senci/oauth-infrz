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
        if (!$username or !$password) {
            return false;
        }
        $link = ldap_connect($this->host, $this->port);
        ldap_set_option($link, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($link, LDAP_OPT_REFERRALS, 0);
        $mail = sprintf('%s@informatik.uni-hamburg.de');
        if (!ldap_bind($link, $mail, $password)) {
            return false;
        }
        $base_dn = 'dc=informatik,dc=uni-hamburg,dc=de';
        $filter = sprintf('uid=%s', $username);
        $fields = array('uid', 'sn', 'givenname', 'memberof', 'userprincipalname');

        $ldap_result = ldap_search($link, $base_dn, $filter, $fields);
        if (!$ldap_result or (ldap_count_entries($link, $ldap_result) != 1)) {
            return false;
        }

        $ldap_user = ldap_get_entries($link, $ldap_result);
        $ldap_user = $ldap_user[0];
        ldap_close($link);
        $user = $this->generateUser($ldap_user);

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
        $web_token = isset($_SESSION['web_token']) ? $_SESSION['web_token'] : false;
        if (!$web_token) {
            return false;
        }
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
        if (!$this->isAuthenticated() or !$this->getUser()->isMemberOf('oauth_client')) {
            return false;
        }
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

    protected function generateUser($ldap_user)
    {
        $alias = $ldap_user['uid'];
        $first_name = $ldap_user['givenname'];
        $last_name = $ldap_user['sn'];
        $email = strtolower($ldap_user['userprincipalname']);
        $groups = $ldap_user['memberof'];

        if ($user = $this->db->getUserByAlias($alias)) {
            $user->groups = $groups;
            $user = $this->db->updateUser($user);
        } else {
            $user = $this->db->insertUser($alias, $first_name, $last_name, $email, $groups);
        }

        return  $user;
    }
}
