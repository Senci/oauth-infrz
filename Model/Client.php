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
    public $scope;

    public function __construct()
    {
        $this->scope = json_decode($this->scope);
        $this->scope->info = get_object_vars(($this->scope->info));
        $this->host = json_decode($this->host);
        if (!$this->scope->info) {
            $this->scope->info = array();
        }
    }
}
