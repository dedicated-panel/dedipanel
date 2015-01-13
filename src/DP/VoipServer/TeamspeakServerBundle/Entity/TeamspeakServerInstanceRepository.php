<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\TeamspeakServerBundle\Entity;

use DP\VoipServer\VoipServerBundle\Entity\VoipServerInstanceRepository;
use DP\VoipServer\VoipServerBundle\Entity\VoipServer;

class TeamspeakServerInstanceRepository extends VoipServerInstanceRepository
{
    protected function validate(VoipServer $server)
    {
        if (!$server instanceof TeamspeakServer) {
            throw new \InvalidArgumentException('You need to provide a teamspeak server if you want to create a teamspeak instance.');
        }
    }
}
