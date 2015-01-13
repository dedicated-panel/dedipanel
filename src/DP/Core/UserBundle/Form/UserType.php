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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                'first_options' => array('label'  => 'user.fields.password'),
                'second_options' => array('label' => 'user.fields.repeat_password'),
                'required' => true,
            ))
            ->add('enabled', null, array('label' => 'user.fields.enabled', 'required' => false))
            ->add('group', 'dedipanel_group_assignement', array(
                'multiple' => false,
                'label'    => 'user.fields.group',
            ))
            ->add('admin', 'checkbox', array('label' => 'user.fields.admin', 'required' => false))
            ->add('superAdmin', 'checkbox', array('label' => 'user.fields.super_admin', 'required' => false))
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $user = $event->getData();
                $form = $event->getForm();

                if ($user->getId() !== null) {
                    $form->add('plainPassword', 'repeated', array(
                        'type' => 'password',
                        'first_options' => array('label'  => 'user.fields.password'),
                        'second_options' => array('label' => 'user.fields.repeat_password'),
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
            'data_class'        => 'DP\Core\UserBundle\Entity\User',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                if ($data->getId() === null) {
                    return ['Adding'];
                }

                return 'Editing';
            },
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
