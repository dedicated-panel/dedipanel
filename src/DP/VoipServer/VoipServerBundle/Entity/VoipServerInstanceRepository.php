<?php

namespace DP\VoipServer\VoipServerBundle\Entity;

use DP\Core\CoreBundle\Entity\MachineRelatedRepository;

abstract class VoipServerInstanceRepository extends MachineRelatedRepository
{
    abstract protected function validate(VoipServer $server);

    public function createNewInstance(VoipServer $server)
    {
        $this->validate($server);

        $className = $this->getClassName();

        return new $className($server);
    }
}
