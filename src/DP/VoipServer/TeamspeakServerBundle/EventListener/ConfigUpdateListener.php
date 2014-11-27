<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\TeamspeakServerBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use DP\Core\CoreBundle\Exception\MaxSlotsLimitReachedException;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerInstance;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;

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
    }

    public function preUpdateTeamspeakInstance(ResourceEvent $event)
    {
        $entity = $event->getSubject();

        try {
            $entity->getQuery()->updateInstanceConfig($entity);
        }
        catch (MaxSlotsLimitReachedException $e) {
            $event->stop('dedipanel.voip.max_slots');
        }
    }
}
