<?php

namespace DP\Core\MachineBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class MachineRepository extends EntityRepository
{
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = null)
    {
        if (isset($criteria['groups'])) {
            $queryBuilder
                ->innerJoin($this->getAlias() . '.groups', 'g', 'WITH', $queryBuilder->expr()->in('g.id', $criteria['groups']))
            ;
            
            unset($criteria['groups']);
        }
        
        parent::applyCriteria($queryBuilder, $criteria);
    }
}
