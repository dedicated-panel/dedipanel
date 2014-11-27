<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class MachineRelatedRepository extends EntityRepository
{
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = null)
    {
        if (isset($criteria['groups'])) {
            $queryBuilder
                ->innerJoin($this->getAlias() . '.machine', 'm', 'WITH', $this->getAlias() . '.machine = m.id')
                ->innerJoin('m.groups', 'g', 'WITH', $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->in('g.id', $criteria['groups'])
                ))
            ;

            unset($criteria['groups']);
        }

        parent::applyCriteria($queryBuilder, $criteria);
    }
}

