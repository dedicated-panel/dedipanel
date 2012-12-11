<?php

/*
** Copyright (C) 2010-2012 Kerouanton Albin, Smedts Jérôme
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

namespace DP\GameServer\SteamServerBundle\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use DP\GameServer\SteamServerBundle\Entity\SteamServer;
use DP\Core\MachineBundle\Entity\Machine;
use DP\Core\GameBundle\Entity\Game;

/**
 * @author Albin Kerouanton 
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
    
    /**
     * Maj des scripts du serveur si la config du serveur (port, maxplayers, dir) a été modifié
     * Ou si la config du jeu (bin, binDir, installName, launchName, map, orangebox, source) a été modifié
     * Ou si l'IP publique de la machine a été modifié
     * 
     * Réinstallation du serveur
     * Si des modifs ont été faites sur la machine (IP privée, home, user)
     * Ou si le serveur de jeu n'est plus sur la meme machine
     * Ou si le jeu du serveur est modifié
     * 
     * @param \Doctrine\ORM\Event\PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof SteamServer) {            
            // Réinstallation du serveur si modif de l'endroit où il est hébergé
            if ($args->hasChangedField('machine') || $args->hasChangedField('game')) {
                try {
                    $entity->installServer($this->getTwig());
                }
                catch (\Exception $e) {
                    $entity->setInstallationStatus(0);
                }
                
                $em = $args->getEntityManager();
                $uow = $em->getUnitOfWork();
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
            elseif ($args->hasChangedField('port') || $args->hasChangedField('maxplayers') 
                || $args->hasChangedField('dir')) {
                try {
                    $entity->uploadHldsScript($this->getTwig());
                }
                catch (\Exception $e) {}
            }
        }
        elseif ($entity instanceof Machine) {
            // Réinstallation de la machine si l'IP privé ou l'utilisateur a été modifié
            if ($args->hasChangedField('privateIp') || $args->hasChangedField('user')
                || $args->hasChangedField('home')) {
                try {
                    $entity->installServer($this->getTwig());
                }
                catch (\Exception $e) {
                    $entity->setInstallationStatus(0);
                }
                
                $em = $args->getEntityManager();
                $uow = $em->getUnitOfWork();
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
            // Upload des scripts si l'IP public ou le home de la machine a été modifié
            elseif ($args->hasChangedField('publicIp')) {
                try {
                    $entity->uploadHldsScripts($this->getTwig());
                }
                catch (\Exception $e) {}
            }
        }
        // Modif de la config d'un jeu
        elseif ($entity instanceof Game) {
            if ($args->hasChangedField('bin') || $args->hasChangedField('binDir') 
                || $args->hasChangedField('installName') || $args->hasChangedField('launchName') 
                || $args->hasChangedField('map') || $args->hasChangedField('orangebox') 
                || $args->hasChangedField('source')) {
                try {
                    $entity->uploadHldsScripts($this->getTwig());
                }
                catch (\Exception $e) {}
            }
        }
    }
}
