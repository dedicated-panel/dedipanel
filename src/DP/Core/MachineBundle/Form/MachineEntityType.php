<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\MachineBundle\Form;

use DP\Core\MachineBundle\Entity\MachineRepository;
use DP\Core\UserBundle\Service\UserGroupResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use DP\Core\UserBundle\Entity\User;

class MachineEntityType extends AbstractType
{
    private $repository;
    private $groupResolver;
    private $context;
    private $choices;

    public function __construct(MachineRepository $repository, UserGroupResolver $groupResolver, SecurityContext $context)
    {
        $this->repository    = $repository;
        $this->groupResolver = $groupResolver;
        $this->context       = $context;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = array();

        if ($this->context->isGranted(User::ROLE_SUPER_ADMIN)) {
            $choices = $this->repository->findAll();
        }
        else {
            $groups  = $this->groupResolver->getAccessibleGroupsId();
            $choices = $this->repository->findByGroups($groups);
        }

        $resolver
            ->setDefaults(array(
                'label'   => 'game.selectMachine',
                'class'   => 'DPMachineBundle:Machine',
                'choices' => $choices,
            ))
        ;
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'dedipanel_machine_entity';
    }
}
