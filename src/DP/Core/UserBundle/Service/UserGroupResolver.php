<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Service;

use DP\Core\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;
use DP\Core\UserBundle\Entity\GroupRepository;

class UserGroupResolver
{
    /**
     * @var GroupRepository
     */
    protected $groupRepo;

    /**
     * @var SecurityContextInterface
     */
    protected $context;
    
    public function __construct(GroupRepository $groupRepo, SecurityContextInterface $context)
    {
        $this->groupRepo = $groupRepo;
        $this->context   = $context;
    }
    
    public function getAccessibleGroups()
    {
        $groups = [];
        $user   = $this->context->getToken()->getUser();

        if ($this->isGranted(User::ROLE_SUPER_ADMIN)) {
            $groups = $this
                ->groupRepo
                ->getChildren(null)
            ;
        }
        elseif ($this->isGranted(User::ROLE_ADMIN)) {
            $groups = $this
                ->groupRepo
                ->getChildren($user->getGroup(), false, null, "asc", true)
            ;
        }
        elseif ($user->getGroup() !== null) {
            $groups = [$user->getGroup()];
        }

        if (empty($groups) && !$this->isGranted(User::ROLE_ADMIN)) {
            throw new \RuntimeException('Security error! This user should not have empty group access. This can lead to security breach.');
        }

        return $groups;
    }
    
    public function getAccessibleGroupsId()
    {
        return array_map(function ($group) {
            return $group->getId();
        }, $this->getAccessibleGroups());
    }

    private function isGranted($role)
    {
        return $this->context->isGranted($role);
    }
}
