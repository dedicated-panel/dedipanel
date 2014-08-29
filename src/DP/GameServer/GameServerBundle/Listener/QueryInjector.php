<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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
