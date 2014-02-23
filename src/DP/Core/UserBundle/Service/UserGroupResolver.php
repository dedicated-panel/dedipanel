<?php

namespace DP\Core\UserBundle\Service;

use Symfony\Component\Security\Core\SecurityContextInterface;
use DP\Core\UserBundle\Entity\GroupRepository;

class UserGroupResolver
{
    protected $repo;
    protected $context;
    
    public function __construct(GroupRepository $repo, SecurityContextInterface $context)
    {
        $this->repo    = $repo;
        $this->context = $context;
    }
    
    public function getAccessibleGroups()
    {
        $groups = $this->context->getToken()->getUser()->getGroups();
        
        return $this->repo->getAccessibleGroupsOrAll($groups);
    }
    
    public function getAccessibleGroupsId()
    {
        $ids = array();
        $groups = $this->getAccessibleGroups();
        
        foreach ($groups AS $group) {
            $ids[] = $group->getId();
        }
        
        return $ids;
    }
}
