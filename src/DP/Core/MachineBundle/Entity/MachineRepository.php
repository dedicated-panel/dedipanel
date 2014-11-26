<?php

namespace DP\Core\MachineBundle\Entity;

use DP\Core\CoreBundle\Entity\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class MachineRepository extends EntityRepository
{
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = null)
    {
        $criteria = $this->cleanupCriteria($criteria);

        if (isset($criteria['groups'])) {
            $queryBuilder
                ->innerJoin($this->getAlias() . '.groups', 'g', 'WITH', $queryBuilder->expr()->in('g.id', $criteria['groups']))
            ;
            
            unset($criteria['groups']);
        }
        
        parent::applyCriteria($queryBuilder, $criteria);
    }

    public function findByGroups(array $groups)
    {
        return $this
            ->findByGroupsQB($groups)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByGroupsQB(array $groups)
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $this->applyCriteria($qb, array('groups' => $groups));

        return $qb;
    }
}
