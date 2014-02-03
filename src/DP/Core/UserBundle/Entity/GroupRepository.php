<?php

namespace DP\Core\UserBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use DP\Core\UserBundle\Entity\Group;

class GroupRepository extends EntityRepository
{
    public function createNew()
    {
        $className = $this->getClassName();

        return new $className('');
    }
    
    public function getQBFindIsNot(Group $group)
    {
        $qb = $this->getQueryBuilder();
        $id = $group->getId();
        
        if (!empty($id)) {
            $qb->andWhere($this->getAlias().'.id != '.intval($id));
        }
        
        return $qb;
    }
}
