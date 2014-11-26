<?php

namespace DP\Core\MachineBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use DP\Core\UserBundle\Entity\GroupRepository;
use DP\Core\UserBundle\Security\AbstractObjectVoter;

/**
 * Voter pour les classes liés à une/des machines du panel.
 * Celui-ci vérifie que l'utilisateur à accès à la machine 
 * lié à l'objet pour lequel le voter agit.
 */
class MachineAssignableVoter extends AbstractObjectVoter
{
    /**
     * Toutes les classes ayant une propriété "machine" 
     * sont supportés par ce voter
     */
    public function supportsClass($class)
    {
        return property_exists($class, 'machine');
    }

    protected function getSupportedClasses()
    {
        return [];
    }
    
    protected function voting(TokenInterface $token, $object, array $attributes)
    {
        $objectGroups     = iterator_to_array($object->getMachine()->getGroups());
        $user             = $token->getUser();
        $accessibleGroups = $this->getUserAccessibleGroups($user);

        if (array_intersect($objectGroups, $accessibleGroups) !== array()
        || $user->isSuperAdmin()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
