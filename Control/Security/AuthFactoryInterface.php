<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Security;

/**
 * An AuthFactory signs in a User and provides authorization information.
 */
interface AuthFactoryInterface
{
    /**
     * Signs in a user by his credentials
     *
     * @param string $username
     * @param string $password
     * @return mixed either the User-Object if successful or false on error.
     */
    public function signIn($username, $password);

    /**
     * Returns whether the user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Returns whether the user has rights to create and moderate clients.
     *
     * @return bool
     */
    public function isClientModerator();
}
