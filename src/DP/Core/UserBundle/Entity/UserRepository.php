<?php

namespace DP\Core\UserBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function createNew()
    {
        $className = $this->getClassName();
        
        $new = new $className;
        $new->setEnabled(true);
        $new->setCreatedAt(new \DateTime);
        
        return $new;
    }
}
