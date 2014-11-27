<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
