<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Model;

class Client
{
    public $id;
    public $name;
    public $user_id;
    public $description;
    public $client_id;
    public $client_secret;
    public $host;
    public $redirect_uri;
    public $default_scope;

    public function __construct()
    {
        $this->default_scope = json_decode($this->default_scope);
        $this->host = json_decode($this->host);
    }
}
