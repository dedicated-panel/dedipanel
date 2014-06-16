<?php

namespace DP\VoipServer\TeamspeakServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AddTeamspeakServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('machine', 'entity', array(
                'label' => 'game.selectMachine', 'class' => 'DPMachineBundle:Machine'
            ))
            ->add('voice_port', 'number', array(
                'label' => 'voip.voice_port',
            ))
            ->add('query_port', 'number', array(
                'label' => 'voip.query_port',
            ))
            ->add('query_password', 'password', array(
                'label' => 'voip.query_password',
            ))
            ->add('filetransfer_port', 'number', array(
                'label' => 'teamspeak.filetransfer_port',
            ))
            ->add('dir', 'text', array(
                'label' => 'game.dir',
            ))
            ->add('licence_file', 'file', array(
                'label'    => 'teamspeak.licence',
                'required' => false,
            ))
            ->add('alreadyInstalled', 'choice', array(
                'choices'   => array(1 => 'game.yes', 0 => 'game.no'),
                'label'     => 'game.isAlreadyInstalled',
                'mapped'    => true,
                'expanded'  => true
            ))
        ;
    }

    public function getName()
    {
        return 'dedipanel_teamspeak_add';
    }
}
