<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Client\Model;

class AccessToken
{
    public $token;
    public $scope;
    public $expires_at;
    public $refresh_token;

    /**
     * Determines whether the AccessToken has the given scope.
     *
     * @param $scopeName
     * @return bool
     */
    public function hasScope($scopeName)
    {
        return in_array($scopeName, $this->scope);
    }
}
