<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Client\Model;

class User
{
    public $kennung;
    public $name;
    public $email;
    public $groups;
    public $scope;

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
