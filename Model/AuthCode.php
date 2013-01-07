<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Model;

class AuthCode
{
    public $id;
    public $user_id;
    public $client_id;
    public $code;
    public $scope;
}
