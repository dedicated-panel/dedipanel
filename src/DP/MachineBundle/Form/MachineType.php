<?php

namespace DP\MachineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('privateIp')
            ->add('publicIp')
            ->add('port')
            ->add('user')
            ->add('pubkeyHash')
        ;
    }

    public function getName()
    {
        return 'dp_machinebundle_machinetype';
    }
}
