<?php

namespace DP\VoipServer\TeamspeakServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AddTeamspeakServerInstanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'game.name'
            ))
            ->add('port', 'number', array(
                'label' => 'game.port',
            ))
            ->add('maxClients', 'number', array(
                'label' => 'voip.max_clients'
            ))
            ->add('autostart', 'checkbox', array(
                'label'    => 'voip.autostart',
                'required' => false,
            ))
        ;
    }

    public function getName()
    {
        return 'dedipanel_teamspeak_instance_add';
    }
}
