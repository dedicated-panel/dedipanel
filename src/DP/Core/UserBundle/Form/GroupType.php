<?php

namespace DP\Core\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContext;

class GroupType extends AbstractType
{
    private $context;

    public function __construct(SecurityContext $context)
    {
        $this->context = $context;
    }

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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if ($this->context->isGranted('ROLE_SUPER_ADMIN')) {
                $form->add('parent', 'dedipanel_group_assignement', array(
                    'label'    => 'group.fields.parent',
                    'multiple' => false,
                    'required' => false,
                ));
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
