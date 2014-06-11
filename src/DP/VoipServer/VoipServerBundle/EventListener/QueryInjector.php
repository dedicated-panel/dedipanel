<?php

namespace DP\VoipServer\VoipServerBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer;
use DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServerInstance;
use Symfony\Component\DependencyInjection\ContainerAware;

class QueryInjector extends ContainerAware
{
    /**
     * Inject the SteamQuery service into entity
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof TeamspeakServer) {
            $entity->setQuery($this->getFactory()->getServerQuery($entity));
        }
        elseif ($entity instanceof TeamspeakServerInstance) {
            $entity->setQuery($this->getFactory()->getServerQuery($entity->getServer()));
        }
    }

    /**
     * @return DP\VoipServer\TeamspeakServerBundle\Service\ServerQueryFactory
     */
    private function getFactory()
    {
        return $this->container->get('dedipanel.factory.teamspeak.query');
    }
}
