<?php

namespace DP\Core\MachineBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use DP\Core\UserBundle\Entity\GroupRepository;

class MachineAssignableVoter implements VoterInterface
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
        return property_exists($class, 'machine');
    }
    
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($this->supportsClass(get_class($object))) {
            $roles = array_map(function ($el) {
                return $el->getRole();
            }, $token->getRoles());
            
            if (array_intersect(iterator_to_array($object->getMachine()->getGroups()), $this->repo->getAccessibleGroups($token->getUser()->getGroups())) !== array() || in_array('ROLE_SUPER_ADMIN', $roles)) {
                return VoterInterface::ACCESS_GRANTED;
            }
            else {
                return VoterInterface::ACCESS_DENIED;
            }
        }
        
        return VoterInterface::ACCESS_ABSTAIN;
    }
}
