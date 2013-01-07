<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Control\Modules;

use Infrz\OAuth\Control\Modules\AbstractController;

class ClientController extends AbstractController
{
    public function mainAction()
    {
        if ($this->authFactory->isClientModerator()) {
            $this->responseBuilder->buildError('no_permission');
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {

    }
}
