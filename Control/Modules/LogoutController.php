<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;
use Infrz\OAuth\Model\User;

class LogoutController extends AbstractController
{
    /**
     * @inheritdoc
     *
     * @Route("/logout")
     */
    public function mainAction()
    {
        $this->isGetRequest();

        $this->authFactory->signOut();

        header('Location: /');
    }
}
