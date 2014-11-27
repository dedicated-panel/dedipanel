<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\MachineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ip', 'text', array('label' => 'machine.fields.ip'))
            ->add('publicIp', 'text', array('label' => 'machine.fields.public_ip', 'required' => false))
            ->add('port', 'number', array('label' => 'machine.fields.port'))
            ->add('username', 'text', array('label' => 'machine.fields.username'))
            ->add('password', 'password', array('label' => 'machine.fields.password'))
            ->add('groups', 'dedipanel_group_assignement')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form    = $event->getForm();
            $machine = $event->getData();

            if ($machine->getId() !== null) {
                $form->add('password', 'password', array(
                    'label'    => 'machine.fields.password',
                    'required' => false,
                ));
            }
        });
    }

    public function getName()
    {
        return 'dedipanel_machine';
    }
}
