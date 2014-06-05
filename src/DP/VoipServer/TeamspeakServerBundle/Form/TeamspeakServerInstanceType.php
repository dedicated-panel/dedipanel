<?php

namespace DP\VoipServer\TeamspeakServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TeamspeakServerInstanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'game.name'))
            ->add('server', 'entity', array(
                'label' => 'voip.selectServer', 'class' => 'DPTeamspeakServerBundle:TeamspeakServer'
            ))
        ;
    }

    public function getName()
    {
        return 'dedipanel_teamspeak_instance';
    }
}
