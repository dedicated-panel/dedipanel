<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\VoipServerBundle\Entity;

use DP\Core\CoreBundle\Entity\MachineRelatedRepository;

abstract class VoipServerInstanceRepository extends MachineRelatedRepository
{
    abstract public function createNewInstance(VoipServer $server);
}
