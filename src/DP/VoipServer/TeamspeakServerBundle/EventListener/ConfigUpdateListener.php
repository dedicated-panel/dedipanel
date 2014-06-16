<?php

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
