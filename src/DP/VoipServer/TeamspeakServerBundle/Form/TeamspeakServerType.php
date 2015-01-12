<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            ->add('voice_port', 'number', array('label' => 'voip.voice_port'))
            ->add('query_port', 'number', array('label' => 'voip.query_port'))
            ->add('filetransfer_port', 'number', array('label' => 'teamspeak.filetransfer_port'))
            ->add('licence_file', 'file', array(
                'label'    => 'teamspeak.licence',
                'required' => false,
            ))
            ->add('alreadyInstalled', 'choice', array(
                'choices'   => array(1 => 'game.yes', 0 => 'game.no'),
                'label'     => 'game.isAlreadyInstalled',
                'expanded'  => true
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form      = $event->getForm();
            /** @var DP\VoipServer\TeamspeakServerBundle\Entity\TeamspeakServer $teamspeak */
            $teamspeak = $event->getData();

            $isUpdateForm = ($teamspeak->getId() != null);

            $form
                ->add('machine', 'dedipanel_machine_entity', array(
                    'disabled' => $isUpdateForm,
                ))
                ->add('dir', 'text', array(
                    'label' => 'game.dir',
                    'disabled' => $isUpdateForm,
                ))
                ->add('query_password', 'password', array(
                    'label' => 'voip.query_password',
                    'required' => $isUpdateForm,
                ))
            ;

            if ($teamspeak->getId() !== null) {
                $form->remove('alreadyInstalled');

                if ($teamspeak->getMachine()->getNbCore() != null) {
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
            }
        });
    }

    public function getName()
    {
        return 'dedipanel_teamspeak';
    }
}
