<?php

namespace DP\Core\UserBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use DP\Core\UserBundle\Entity\GroupRepository;
use DP\Core\UserBundle\Entity\Group;

class GroupAssignableVoter implements VoterInterface
{
    protected $repo;
    
    public function __construct(GroupRepository $repo)
    {
        $this->repo = $repo;
    }
    
    public function supportsAttribute($attribute)
    {
        return preg_match('#^ROLE_DP_#', $attribute);
    }
    
    public function supportsClass($class)
    {
        return in_array($class, array(
            'DP\Core\UserBundle\Entity\User',
            'DP\Core\UserBundle\Entity\Group',  
            'DP\Core\MachineBundle\Entity\Machine', 
        ));
    }
    
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($this->supportsClass(get_class($object))) {
            $roles = array_map(function ($el) {
                return $el->getRole();
            }, $token->getRoles());
            
            $groups = array($object);
            
            if (!$object instanceof Group) {
                $groups = iterator_to_array($object->getGroups());
            }
            // Deny access if the user try to edit/delete group on which he is assigned
            elseif (array_intersect($groups, iterator_to_array($token->getUser()->getGroups())) !== array()
            && (in_array('ROLE_DP_ADMIN_GROUP_UPDATE', $attributes)
            || in_array('ROLE_DP_ADMIN_GROUP_DELETE', $attributes))) {
                return VoterInterface::ACCESS_DENIED;
            }

            if (array_intersect($groups, $this->repo->getAccessibleGroups($token->getUser()->getGroups())) !== array() 
            || in_array('ROLE_SUPER_ADMIN', $roles)) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }
        
        return VoterInterface::ACCESS_ABSTAIN;
    }
}
