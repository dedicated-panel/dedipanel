<?php

namespace DP\Core\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DP\Core\UserBundle\EventListener\RolesTypeSubscriber;
use DP\Core\UserBundle\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Form type used for displaying a list of checkboxes corresponding to each role
 */
class RolesType extends AbstractType
{
    private $subscriber;
    private $roles;
    private $translator;
    
    public function __construct(RolesTypeSubscriber $subscriber, array $roles, TranslatorInterface $translator)
    {
        $this->subscriber = $subscriber;
        $this->roles = array_keys($roles);
        $this->translator = $translator;
    }    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->subscriber);
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
                return 'user.role.' . $role;
            }, $this->roles)), 
            'attr' => array('class' => 'dp-security-roles'), 
            'required' => false, 
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
    
    /**
     * Désactive les checkbox correspondant aux roles associés aux groupes de l'utilisateur
     * et ajoute un title sur les labels de ces checkbox pour indiquer quels groupes sont associés à ce rôle
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($view->parent->vars['value'] instanceof User) {
            $user = $view->parent->vars['value'];
            $groups = $user->getGroups();
            $roles = array();
            
            foreach ($groups AS $group) {
                $groupRoles = $group->getRoles();
                
                if (count($groupRoles) > 0) {
                    $groupRoles = array_combine($groupRoles, array_fill(0, count($groupRoles), array($group->getName())));
                    $roles = array_merge_recursive($roles, $groupRoles);
                }
            }
            
            foreach ($view->children AS &$roleCheckbox) {
                if (array_key_exists($roleCheckbox->vars['value'], $roles)) {
                    $role = $roleCheckbox->vars['value'];
                    
                    $roleCheckbox->vars['disabled'] = true;
                    $roleCheckbox->vars['label_attr'] = array(
                        'title' => $this->translator->transChoice(
                            'user.rolesAssociatedToGroup', 
                            count($roles[$role]), 
                            array('%groups%' => implode(', ', $roles[$role]))
                        ),  
                    );
                }
            }
        }
    }
}
