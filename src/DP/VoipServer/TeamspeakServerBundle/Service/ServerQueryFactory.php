<?php

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
