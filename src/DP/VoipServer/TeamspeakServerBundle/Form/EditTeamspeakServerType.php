<?php

namespace DP\VoipServer\TeamspeakServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EditTeamspeakServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('machine', 'entity', array(
                'label'    => 'game.selectMachine',
                'class'    => 'DPMachineBundle:Machine',
                'disabled' => true,
            ))
            ->add('voice_port', 'number', array(
                'label' => 'voip.voice_port',
            ))
            ->add('query_port', 'number', array(
                'label' => 'voip.query_port',
            ))
            ->add('query_password', 'password', array(
                'label'    => 'voip.query_password',
                'required' => false,
            ))
            ->add('filetransfer_port', 'number', array(
                'label' => 'teamspeak.filetransfer_port',
            ))
            ->add('dir', 'text', array(
                'label'    => 'game.dir',
                'disabled' => true,
            ))
        ;
    }

    public function getName()
    {
        return 'dedipanel_teamspeak_edit';
    }
}
