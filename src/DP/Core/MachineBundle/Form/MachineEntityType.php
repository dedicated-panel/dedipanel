<?php

namespace DP\Core\MachineBundle\Form;

use DP\Core\MachineBundle\Entity\MachineRepository;
use DP\Core\UserBundle\Service\UserGroupResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

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
        if ($this->context->isGranted('ROLE_SUPER_ADMIN')) {
            $choices = $this->repository->findAll();
        }
        else {
            $groups = array_map(function ($group) {
                return $group->getId();
            }, $this->groupResolver->getAccessibleGroups());
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
