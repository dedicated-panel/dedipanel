<?php

namespace DP\Core\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DP\Core\UserBundle\Form\Modifier\SecurityRolesModifier;

class SecurityRolesType extends AbstractType
{
    private $formModifier;
    private $roles;
    
    public function __construct(SecurityRolesModifier $modifier, array $roles)
    {
        $this->formModifier = $modifier;
        $this->roles = array_keys($roles);
    }    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->formModifier);
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['role_hierarchy'] = $this->formModifier->getHierarchy();
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {        
        $resolver->setDefaults(array(
            'label' => 'user.security.roles', 
            'multiple' => true, 
            'expanded' => true, 
            'choices' => array_combine($this->roles, array_map(function ($role) {
                return 'user.role.' . $role;
            }, $this->roles)), 
            'attr' => array('class' => 'dp-security-roles'), 
        ));
    }
    
    public function getParent()
    {
        return 'choice';
    }
    
    public function getName()
    {
        return 'dp_security_roles';
    }
}
