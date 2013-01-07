<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Model;

class User
{
    public $id;
    public $alias;
    public $first_name;
    public $last_name;
    public $email;
    public $groups;

    public function __construct()
    {
        $this->groups = json_decode($this->groups);
    }

    /**
     * Determines whether the user is in a group.
     *
     * @param $groupName
     * @return bool
     */
    public function isMemberOf($groupName)
    {
        return in_array($groupName, $this->groups);
    }
}
