<?php

namespace DP\Core\UserBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use DP\Core\UserBundle\Entity\User;

class UserRegistrationListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em     = $args->getEntityManager();
        
        if ($entity instanceof User) {
            // Si l'entité n'a pas d'ID c'est qu'elle n'a pas encore été sauvegardé en bdd
            // on lui ajoute donc sa date de création
            if ($entity->getId() == null) {
                $entity->setCreatedAt(new \DateTime);
            }
        }
    }
}
