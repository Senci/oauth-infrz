<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class IndexController extends AbstractController
{
    public function mainAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->responseBuilder->buildError('not_found');
        }

        $this->responseBuilder->buildHome();
    }
}
