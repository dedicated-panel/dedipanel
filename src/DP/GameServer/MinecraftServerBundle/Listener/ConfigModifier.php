<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\MinecraftServerBundle\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer;
use DP\Core\MachineBundle\Entity\Machine;

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
     * Get twig
     * 
     * @throws Exception 
     * @return \Twig_Environment
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
     * Si des modifs ont été faites sur la machine (IP publique, home, user)
     * Ou si le serveur de jeu n'est plus sur la meme machine
     * Ou si le jeu du serveur est modifié
     * 
     * @param \Doctrine\ORM\Event\PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof MinecraftServer) {
            if ($args->hasChangedField('port') || $args->hasChangedField('maxplayers') 
                || $args->hasChangedField('name') || $args->hasChangedField('queryPort') 
                || $args->hasChangedField('rconPort') || $args->hasChangedField('rconPassword')) {
                try {
                    $entity->modifyServerPropertiesFile();
                }
                catch (\Exception $e) {}
            }
            if ($args->hasChangedField('minHeap') || $args->hasChangedField('maxHeap')
                || $args->hasChangedField('dir') || $args->hasChangedField('core')) {
                try {
                    $entity->uploadShellScripts($this->getTwig());
                }
                catch (\Exception $e) {}
            }
        }
        elseif ($entity instanceof Machine) {
            // Upload des scripts si l'IP public ou le home de la machine a été modifié
            if ($args->hasChangedField('publicIp') || $args->hasChangedField('home')) {
                $servers = $entity->getGameServers();
                
                foreach ($servers AS $server) {
                    if (!$server instanceof MinecraftServer) continue;
                    
                    if ($args->hasChangedField('publicIp')) {
                        try {
                            $server->modifyServerPropertiesFile();
                        }
                        catch (\Exception $e) {}
                    }
                    if ($args->hasChangedField('home')) {
                        try  {
                            $server->uploadShellScripts($this->getTwig());
                        }
                        catch (\Exception $e) {}
                    }
                }
            }
        }
    }
}
