<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Model;

class RefreshToken
{
    public $id;
    public $access_token_id;
    public $token;
    public $expires_at;
}
