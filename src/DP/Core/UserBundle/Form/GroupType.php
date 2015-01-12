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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
