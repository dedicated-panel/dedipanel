<?php

namespace DP\Core\MachineBundle\EventListener;

use Dedipanel\PHPSeclibWrapperBundle\Connection\ConnectionManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use DP\Core\MachineBundle\Entity\Machine;

/**
 * Injecte une instance de Connection (du PHPSeclibWrapperBundle) dans les machines
 * Réinstalle automatiquement les serveurs si l'ip et/ou le dossier "home" de l'utilistauer est modifié
 */
class ModelListener
{
    private $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    /**
     * Inject the SSH/SFTP Connection into entity
     * 
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof Machine) {
            $entity->setConnection(
                $this->container->get('dedipanel.connection_manager')->getConnectionFromServer($entity)
            );
        }
    }
    
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof Machine) {
            // Réinstallation de la machine si l'IP privé ou l'utilisateur a été modifié
            if ($args->hasChangedField('ip') || $args->hasChangedField('home')) {
                $em = $args->getEntityManager();
                $uow = $em->getUnitOfWork();
                $servers = $entity->getGameServers();
                
                foreach ($servers AS $server) {
                    try {
                        $server->installServer($this->container->get('twig'));
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
