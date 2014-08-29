<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\SteamServerBundle\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use DP\GameServer\SteamServerBundle\Entity\SteamServer;
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
            if ($args->hasChangedField('port') 
            || ($args->hasChangedField('maxplayers') && $entity->getGame()->getLaunchName() != "csgo") 
            || $args->hasChangedField('core') || $args->hasChangedField('mode')) {
                try {
                    $entity->uploadHldsScript($this->getTwig());
                }
                catch (\Exception $e) {}
            }
            if ($args->hasChangedField('maxplayers') && $entity->getGame()->getLaunchName() == "csgo") {
                try {
                    $entity->modifyGameModesCfg();
                }
                catch (\Exception $e) {}
            }
            if ($args->hasChangedField('dir')) {
                try {
                    $entity->uploadShellScripts($this->getTwig());
                }
                catch (\Exception $e) {}
            }
            if ($args->hasChangedField('rebootAt')) {
                // Suppression du reboot auto si la valeur du champ vaut null
                // Sinon ajout/modif
                if ($args->getNewValue('rebootAt') == null) {
                    $entity->removeAutoReboot();
                }
                else {
                    $entity->addAutoReboot();
                }
            }
            if ($args->hasChangedField('name') || $args->hasChangedField('rconPassword')) {
                try {
                    $entity->modifyServerCfgFile();
                }
                catch (\Exception $e) {}
            }
        }
        elseif ($entity instanceof Machine) {
            // Upload des scripts si l'IP publique ou le home de la machine a été modifié
            if ($args->hasChangedField('publicIp') || $args->hasChangedField('home')) {
                $servers = $entity->getGameServers();
                
                foreach ($servers AS $server) {
                    if (!$server instanceof SteamServer) continue;
                    
                    try {
                        $server->uploadShellScripts($this->getTwig());
                    }
                    catch (\Exception $e) {}
                }
            }
        }
    }
}
