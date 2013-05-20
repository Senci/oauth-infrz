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
    /**
     * @inheritdoc
     *
     * @Route("/")
     */
    public function mainAction()
    {
        $this->isGetRequest();

        $this->responseBuilder->buildHome();
    }
}
