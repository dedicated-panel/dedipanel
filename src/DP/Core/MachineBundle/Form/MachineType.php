<?php

namespace DP\Core\MachineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('privateIp', 'text', array('label' => 'machine.privateIp'))
            ->add('publicIp', 'text', array('label' => 'machine.publicIp', 'required' => false))
            ->add('port', 'number', array('label' => 'machine.port'))
            ->add('user', 'text', array('label' => 'machine.user'))
            ->add('passwd', 'password', array('label' => 'machine.passwd'))
        ;
    }

    public function getName()
    {
        return 'dp_machinebundle_machinetype';
    }
}
