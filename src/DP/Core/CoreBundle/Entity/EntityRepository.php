<?php

namespace DP\Core\CoreBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository as BaseEntityRepository;

class EntityRepository extends BaseEntityRepository
{
    protected function cleanupCriteria(array $criteria = null)
    {
        // isset($criteria['groups']) => renvoie false si la valeur est null
        // pourtant la clé existant bien, le critère group = null est utilisé dans la requête sql
        // on s'assure donc ici de détruire ce critère
        if (in_array('groups', array_keys($criteria)) && $criteria['groups'] === null) {
            unset($criteria['groups']);
        }

        return $criteria;
    }
}
