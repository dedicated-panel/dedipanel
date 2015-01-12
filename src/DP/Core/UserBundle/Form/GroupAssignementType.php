<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DP\Core\UserBundle\Service\UserGroupResolver;
use Symfony\Component\Security\Core\SecurityContext;
use DP\Core\UserBundle\Entity\User;

class GroupAssignementType extends AbstractType
{
    private $groupResolver;
    private $context;
    
    public function __construct(UserGroupResolver $groupResolver, SecurityContext $context)
    {
        $this->groupResolver = $groupResolver;
        $this->context       = $context;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'label'    => 'machine.fields.groups',
                'class'    => 'DPUserBundle:Group',
                'multiple' => true,
                'required' => !$this->context->isGranted(User::ROLE_SUPER_ADMIN),
                'choices'  => $this->groupResolver->getAccessibleGroups(),
            ))
        ;
    }
    
    public function getParent()
    {
        return 'entity';
    }
    
    public function getName()
    {
        return 'dedipanel_group_assignement';
    }
}
