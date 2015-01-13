<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\MachineBundle\Security;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use DP\Core\UserBundle\Entity\GroupRepository;
use DP\Core\UserBundle\Security\AbstractObjectVoter;

/**
 * Voter pour les classes liés à une/des machines du panel.
 * Celui-ci vérifie que l'utilisateur à accès à la machine 
 * lié à l'objet pour lequel le voter agit.
 */
class MachineRelatedVoter extends AbstractObjectVoter
{
    /**
     * Toutes les classes ayant une propriété "machine" 
     * sont supportés par ce voter
     */
    public function supportsClass($class)
    {
        return property_exists($class, 'machine');
    }

    /** {@inheritdoc} */
    protected function getSupportedClasses()
    {
        // Does not return anything as supportsClass() method is rewritten
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($this->supportsClass(get_class($object))) {
            $objectGroups     = iterator_to_array($object->getMachine()->getGroups());
            $user             = $token->getUser();
            $accessibleGroups = $this->getUserAccessibleGroups($user);

            if (array_intersect($objectGroups, $accessibleGroups) !== array()
                || $user->isSuperAdmin()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
