<?php

namespace DP\Admin\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DP\Admin\UserBundle\Form\EventListener\UserPasswordSubscriber;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' => 'user_admin.fields.username'))
            ->add('email', 'email', array('label' => 'user_admin.fields.email'))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'required' => false,
                'first_options' => array('label' => 'user_admin.fields.password'),
                'second_options' => array('label' => 'user_admin.fields.repeat_password'),
            ))
            ->add('enabled', null, array('label' => 'user_admin.fields.enabled', 'required' => false))
            ->add('groups', 'entity', array(
                'label' => 'user_admin.fields.groups', 
                'class' => 'DPCoreUserBundle:Group', 
                'multiple' => true,  
                'required' => false, 
            ))
            ->add('roles', 'dp_security_roles')
        ;
        
        // Ajout d'un EventSubscriber permettant de gérer 
        // les propriété password et plainPassword des entités
        // lors de la création via le formulaire
        $builder->addEventSubscriber(new UserPasswordSubscriber);
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DP\Core\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dp_admin_userbundle_user';
    }
}
