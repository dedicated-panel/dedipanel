<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use DP\GameServer\GameServerBundle\Entity\GameServer;
use DP\GameServer\SteamServerBundle\Entity\SteamServer;
use DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer;
use DP\GameServer\SteamServerBundle\SteamQuery\Exception\UnexpectedServerTypeException;
use DP\GameServer\SteamServerBundle\SteamQuery\SteamQuery;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * @author Albin Kerouanton 
 */
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
        
        if ($entity instanceof GameServer) {
            // Détection du type de serveur pour appeler le query et le rcon adéquat
            if ($entity instanceof SteamServer) {
                $type = SteamQuery::TYPE_GOLDSRC;
                
                if ($entity->getGame()->getSource() == true) {
                    $type = SteamQuery::TYPE_SOURCE;
                }
                
                $query = $this->container->get('query.steam')
                    ->getServerQuery(
                        $entity->getMachine()->getPublicIp(),
                        $entity->getPort(),
                        $type
                    );
                
                $rcon = $this->container->get('rcon.steam')
                    ->getRcon(
                        $entity->getMachine()->getPublicIp(),
                        $entity->getPort(),
                        $entity->getRconPassword(),
                        $type
                    );
            }
            elseif ($entity instanceof MinecraftServer) {
                $query = $this->container->get('query.minecraft')
                    ->getServerQuery(
                        $entity->getMachine()->getPublicIp(),
                        $entity->getQueryPort()
                    );
                
                $rcon = $this->container->get('rcon.minecraft')
                    ->getRcon(
                        $entity->getMachine()->getPublicIp(),
                        $entity->getRconPort(),
                        $entity->getRconPassword()
                    );
            }
            else {
                return false;
            }
            
            if (!empty($query)) {
                $entity->setQuery($query);

                try {
                    $query->verifyStatus();
                }
                catch (UnexpectedServerTypeException $e) {}
            }
            
            if (!empty($rcon)) {
                $entity->setRcon($rcon);
            }
        }
    }
}
