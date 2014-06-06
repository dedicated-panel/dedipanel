<?php

namespace DP\VoipServer\TeamspeakServerBundle\Service;

use DP\VoipServer\TeamspeakServerBundle\ServerQuery\ServerQuery;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer;
use DP\Core\CoreBundle\Service\SocketFactory;

class ServerQueryFactory
{
    private $socketFactory;


    /**
     * @param SocketFactory $socketFactory
     */
    public function __construct(SocketFactory $socketFactory)
    {
        $this->socketFactory = $socketFactory;
    }

    /**
     * @param TeamspeakServer $server
     * @return ServerQuery
     */
    public function getServerQuery(TeamspeakServer $server)
    {
        $socket = $this
            ->socketFactory
            ->getTCPSocket($server->getHost(), $server->getQueryPort())
        ;

        return new ServerQuery(
            $socket, $server->getQueryLogin(), $server->getQueryPassword(), true
        );
    }
}
