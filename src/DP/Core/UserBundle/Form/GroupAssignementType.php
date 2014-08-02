<?php

namespace DP\Core\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DP\Core\UserBundle\Service\UserGroupResolver;

class GroupAssignementType extends AbstractType
{
    private $groupResolver;
    
    public function __construct(UserGroupResolver $groupResolver)
    {
        $this->groupResolver = $groupResolver;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'label' => 'user.fields.groups', 
                'class' => 'DPUserBundle:Group',
                'multiple' => true,
                'required' => true,
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
