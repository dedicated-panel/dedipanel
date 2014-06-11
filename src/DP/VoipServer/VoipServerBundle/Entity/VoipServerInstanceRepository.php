<?php

namespace DP\VoipServer\VoipServerBundle\Entity;

use DP\Core\CoreBundle\Entity\MachineRelatedRepository;

abstract class VoipServerInstanceRepository extends MachineRelatedRepository
{
    abstract public function createNewInstance(VoipServer $server);
}
