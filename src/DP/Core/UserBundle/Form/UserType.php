<?php

namespace DP\Core\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DP\Core\UserBundle\EventListener\UserPasswordSubscriber;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' => 'user.fields.username'))
            ->add('email', 'email', array('label' => 'user.fields.email'))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'required' => false,
                'first_options' => array('label' => 'user.fields.password'),
                'second_options' => array('label' => 'user.fields.repeat_password'),
            ))
            ->add('enabled', null, array('label' => 'user.fields.enabled', 'required' => false))
            ->add('groups', 'entity', array(
                'label' => 'user.fields.groups', 
                'class' => 'DPCoreUserBundle:Group', 
                'multiple' => true,  
                'required' => false, 
            ))
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
        return 'dedipanel_user';
    }
}
