<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\TeamspeakServerBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerInstance;

class ConfigUpdateListener
{
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof TeamspeakServer) {
            $entity->uploadConfigFile();

            if ($entity->hasLicenceFile()) {
                $entity->uploadLicenceFile();
            }

            $entity->changeState('restart');
        }
        elseif ($entity instanceof TeamspeakServerInstance) {
            $entity->getQuery()->updateInstanceConfig($entity);
        }
    }
}
