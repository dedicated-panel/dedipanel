<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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

    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = null)
    {
        if (isset($criteria['groups'])) {
            $queryBuilder
                ->innerJoin($this->getAlias() . '.group', 'g', 'WITH', $queryBuilder->expr()->in('g.id', $criteria['groups']))
            ;
            
            unset($criteria['groups']);
        }
        
        parent::applyCriteria($queryBuilder, $criteria);
    }
}
