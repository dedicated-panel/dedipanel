<?php

namespace DP\Core\UserBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class GroupRepository extends EntityRepository
{
    public function createNew()
    {
        $className = $this->getClassName();

        return new $className('');
    }
}
