<?php

namespace DP\Core\MachineBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use DP\Core\UserBundle\Entity\GroupRepository;

/**
 * Voter pour les classes liés à une/des machines du panel.
 * Celui-ci vérifie que l'utilisateur à accès à la machine 
 * lié à l'objet pour lequel le voter agit.
 */
class MachineAssignableVoter implements VoterInterface
{
    protected $repo;
    
    public function __construct(GroupRepository $repo)
    {
        $this->repo = $repo;
    }
    
    /*
     * @param string $attribute
     * 
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return preg_match('#^ROLE_DP_#', $attribute);
    }
    
    /**
     * Toutes les classes ayant une propriété "machine" 
     * sont supportés par ce voter
     */
    public function supportsClass($class)
    {
        return property_exists($class, 'machine');
    }
    
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($this->supportsClass(get_class($object))) {
            $roles = array_map(function ($el) {
                return $el->getRole();
            }, $token->getRoles());
            
            $objectGroups     = iterator_to_array($object->getMachine()->getGroups());
            $accessibleGroups = $this->repo->getAccessibleGroups($token->getUser()->getGroups());
            
            if (array_intersect($objectGroups, $accessibleGroups) !== array() 
            || in_array('ROLE_SUPER_ADMIN', $roles)) {
                return VoterInterface::ACCESS_GRANTED;
            }
            else {
                return VoterInterface::ACCESS_DENIED;
            }
        }
        
        return VoterInterface::ACCESS_ABSTAIN;
    }
}
