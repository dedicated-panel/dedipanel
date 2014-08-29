<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $groups = iterator_to_array($this->context->getToken()->getUser()->getGroups());

        if ($this->context->isGranted('ROLE_ADMIN')) {
            $groups = $this->repo->getAccessibleGroups($groups, $this->context->isGranted('ROLE_SUPER_ADMIN'));
        }

        return $groups;
    }
    
    public function getAccessibleGroupsIdOrEmpty()
    {
        $ids = array();
        
        $groups = $this->context->getToken()->getUser()->getGroups();
        $groups = $this->repo->getAccessibleGroups($groups);
        
        foreach ($groups AS $group) {
            $ids[] = $group->getId();
        }
        
        return $ids;
    }
}
