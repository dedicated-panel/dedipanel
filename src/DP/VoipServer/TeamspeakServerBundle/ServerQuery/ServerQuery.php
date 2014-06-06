<?php

namespace DP\VoipServer\TeamspeakServerBundle\ServerQuery;

use DP\Core\CoreBundle\Socket\Socket;
use DP\Core\CoreBundle\Socket\Exception\SocketException;

class ServerQuery
{
    /** @var Socket $socket */
    private $socket;
    /** @var string $login */
    private $login;
    /** @var string $pass */
    private $pass;
    /** @var boolean $error */
    private $error;
    /** @var boolean $connected */
    private $connected;
    /** @var PacketFactory $factory */
    private $factory;


    public function __construct(Socket $socket, $login, $pass)
    {
        $this->socket    = $socket;
        $this->login     = $login;
        $this->pass      = $pass;
        $this->error     = false;
        $this->connected = false;
        $this->factory   = new PacketFactory();

        try {
            $this->socket->connect();
            $this->login();
        }
        catch (SocketException $e) {
            $this->error = true;
        }
    }

    public function login()
    {
        try {
            $this->socket->send($this->factory->getLoginPacket($this->login, $this->pass));
            $ret = $this->socket->recv();

            if (strpos($ret, "Welcome to the TeamSpeak 3 ServerQuery interface") === false) {
                $this->error = true;

                return false;
            }
        }
        catch (SocketException $e) {
            $this->error = true;

            return false;
        }

        $this->connected = true;

        return true;
    }

    public function isConnected()
    {
        return $this->connected;
    }
}
