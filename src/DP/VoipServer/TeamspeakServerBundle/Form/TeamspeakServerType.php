<?php

namespace DP\VoipServer\TeamspeakServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TeamspeakServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('machine', 'dedipanel_machine_entity')
            ->add('voice_port', 'number', array('label' => 'voip.voice_port'))
            ->add('query_port', 'number', array('label' => 'voip.query_port'))
            ->add('query_password', 'password', array('label' => 'voip.query_password'))
            ->add('filetransfer_port', 'number', array('label' => 'teamspeak.filetransfer_port'))
            ->add('dir', 'text', array('label' => 'voip.dir'))
            ->add('licence_file', 'file', array(
                'label'    => 'teamspeak.licence',
                'required' => false,
            ))
            ->add('already_installed', 'choice', array(
                'choices'   => array(1 => 'game.yes', 0 => 'game.no'),
                'label'     => 'game.isAlreadyInstalled',
                'mapped'    => true,
                'expanded'  => true
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form      = $event->getForm();
            /** @var DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer $teamspeak */
            $teamspeak = $event->getData();

            if ($teamspeak->getId() === null) {
                $form->add('already_installed', 'choice', array(
                    'choices'  => array(1 => 'game.yes', 0 => 'game.no'),
                    'label'    => 'game.isAlreadyInstalled',
                    'expanded' => true,
                ));
            }
            else {
                $form->add('dir', 'text', array('label' => 'voip.dir', 'disabled' => true));
                $form->add('query_password', 'password', array('label' => 'voip.query_password', 'required' => false));

                if ($teamspeak->getMachine()->getNbCore() != null) {
                    $choices = array_combine(
                        range(0, $teamspeak->getMachine()->getNbCore()-1),
                        range(1, $minecraft->getMachine()->getNbCore())
                    );

                    $form->add('core', 'choice', array(
                        'label'    => 'game.core',
                        'choices'  => $choices,
                        'multiple' => true,
                        'required' => false,
                    ));
                }
            }
        });
    }

    public function getName()
    {
        return 'dedipanel_teamspeak';
    }
}
