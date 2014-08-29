<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DP\Core\UserBundle\EventListener\RolesTypeSubscriber;
use DP\Core\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Form type used for displaying a list of checkboxes corresponding to each role
 */
class RolesType extends AbstractType
{
    private $subscriber;
    private $roles;
    private $context;
    
    public function __construct(RolesTypeSubscriber $subscriber, array $roles, SecurityContext $context)
    {
        $this->subscriber = $subscriber;
        $this->roles      = array_keys($roles);
        $this->context    = $context;
    }

    /**
     * Supprime de la liste les rôles que n'a pas l'utilisateur
     * Et filtre la liste des rôles par ceux spécifiés
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->subscriber);

        $filteredChoices = array_keys($options['choices']);
        if (!empty($options['roles'])) {
            $filteredChoices = array_intersect($filteredChoices, $options['roles']);
        }

        foreach ($builder AS $key => $value) {
            $role = $builder->get($key)->getOption('value');

            if (!in_array($role, $filteredChoices)
            || !$this->context->isGranted($role)) {
                $builder->remove($key);
            }
        }
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['role_hierarchy'] = $this->subscriber->getHierarchy();
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'user.security.roles', 
            'multiple' => true, 
            'expanded' => true,
            'choices' => array_combine($this->roles, array_map(function ($role) {
                return 'roles.' . $role;
            }, $this->roles)),
            'attr' => array('class' => 'dp-security-roles'), 
            'required' => false,
        ));

        $resolver->setOptional(array('roles'));
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
