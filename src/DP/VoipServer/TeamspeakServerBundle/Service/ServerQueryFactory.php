<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\TeamspeakServerBundle\Service;

use DP\VoipServer\TeamspeakServerBundle\ServerQuery\QueryGateway;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer;
use DP\Core\CoreBundle\Service\SocketFactory;

class ServerQueryFactory
{
    /**
     * @param TeamspeakServer $server
     * @return ServerQuery
     */
    public function getServerQuery(TeamspeakServer $server)
    {
        return new QueryGateway(
            $server->getHost(),
            $server->getQueryPort(),
            $server->getQueryLogin(),
            $server->getQueryPassword()
        );
    }
}
