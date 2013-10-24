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

namespace DP\Core\MachineBundle\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use DP\Core\MachineBundle\Entity\Machine;

/**
 * Les classes ConfigModifier modifie automatiquement les configs des serveurs
 * selon la configuration des machines/jeux/serveurs.
 */
class ConfigModifier
{
    protected $container;
    
    /**
     * Set service container
     * 
     * @param ServiceContainer $container
     */
    public function setServiceContainer($container)
    {
        $this->container = $container;
    }
    
    /**
     * Get steam query service
     * 
     * @return \DP\GameServer\SteamServerBundle\Service\Query
     * @throws Exception 
     */
    protected function getTwig()
    {
        if (is_null($this->container)) {
            throw new Exception('The service container is not yet set.');
        }
        
        return $this->container->get('twig');
    }
    
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof Machine) {
            // Réinstallation de la machine si l'IP privé ou l'utilisateur a été modifié
            if ($args->hasChangedField('privateIp') || $args->hasChangedField('user') || $args->hasChangedField('home')) {
                $em = $args->getManager();
                $uow = $em->getUnitOfWork();
                $servers = $entity->getGameServers();
                
                foreach ($servers AS $server) {
                    try {
                        $server->installServer($this->getTwig());
                    }
                    catch (\Exception $e) {
                        $server->setInstallationStatus(0);
                    }
                    
                    $meta = $em->getClassMetadata(get_class($server));
                    $uow->recomputeSingleEntityChangeSet($meta, $server);
                }
                
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }
}