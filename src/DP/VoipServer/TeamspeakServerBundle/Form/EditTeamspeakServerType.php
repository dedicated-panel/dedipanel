<?php

namespace DP\VoipServer\TeamspeakServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
            ->add('licence_file', 'file', array(
                'label'    => 'teamspeak.licence',
                'required' => false,
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form      = $event->getForm();
            /** @var DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer $teamspeak */
            $teamspeak = $event->getData();

            if ($teamspeak->getId() !== null
            && $teamspeak->getMachine()->getNbCore() != null) {
                $choices = array_combine(
                    range(0, $teamspeak->getMachine()->getNbCore()-1),
                    range(1, $teamspeak->getMachine()->getNbCore())
                );

                $form->add('core', 'choice', array(
                    'label'    => 'game.core',
                    'choices'  => $choices,
                    'multiple' => true,
                    'required' => false,
                ));
            }
        });
    }

    public function getName()
    {
        return 'dedipanel_teamspeak_edit';
    }
}
