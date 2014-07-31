<?php

namespace DP\Core\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DP\Core\UserBundle\Entity\GroupRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class GroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'group.fields.name'))
            ->add('parent', 'dedipanel_group_assignement', array(
                'label'    => 'group.fields.parent', 
                'multiple' => false, 
                'required' => true, 
            ))
            ->add('roles', 'dp_security_roles', array(
                'label' => 'user.fields.roles',
                'roles' => array(
                    'ROLE_DP_GAME_STEAM_ADMIN',
                    'ROLE_DP_GAME_MINECRAFT_ADMIN',
                    'ROLE_DP_VOIP_TEAMSPEAK_ADMIN',
                    'ROLE_DP_VOIP_TEAMSPEAK_INSTANCE_ADMIN',
                )
            ))
        ;
        
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form  = $event->getForm();
            $field = $form->get('parent');
            
            $parent      = $field->getData();
            $fieldValues = $field->getConfig()->getOption('choices');
            
            if (!in_array($parent, $fieldValues)) {
                $form->remove('parent');
            }
        });
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DP\Core\UserBundle\Entity\Group'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dedipanel_group';
    }
}
