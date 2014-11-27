<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\SteamServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use DP\GameServer\SteamServerBundle\Entity\SteamServer;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SteamServerType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'game.name'))
            ->add('port', 'integer', array('label' => 'game.port'))
            ->add('mode', 'choice', array(
                'choices'     => SteamServer::getModeList(),
                'empty_value' => 'steam.chooseGameMode',
                'label'       => 'steam.gameMode',
                'required'    => false,
            ))
            ->add('maxplayers', 'integer', array('label' => 'game.maxplayers'))
            ->add('rconPassword', 'text', array(
                'label'    => 'game.rcon.password',
                // 'required' => empty($options['data']),
            ))
            ->add('svPassword', 'text', array('label' => 'steam.svPassword', 'required' => false))
            ->add('alreadyInstalled', 'choice', array(
                'choices'  => array(1 => 'game.yes', 0 => 'game.no'),
                'label'    => 'game.isAlreadyInstalled',
                'expanded' => true,
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Symfony\Component\Form\FormInterface $form */
            $form  = $event->getForm();
            /** @var DP\GameServer\SteamServerBundle\Entity\SteamServer $steam */
            $steam = $event->getData();

            $isUpdateForm = ($steam->getId() != null);

            $form
                ->add('machine', 'dedipanel_machine_entity', array(
                    'disabled' => $isUpdateForm,
                ))
                ->add('game', 'entity', array(
                    'label' => 'game.selectGame',
                    'class' => 'DPGameBundle:Game',
                    'query_builder' => function($repo) {
                        return $repo->getQBAvailableSteamGames();
                    },
                    'disabled' => $isUpdateForm,
                ))
                ->add('dir', 'text', array(
                    'label' => 'game.dir',
                    'disabled' => $isUpdateForm,
                ))
            ;

            if ($steam->getId() != null) {
                $form->remove('alreadyInstalled');

                if ($steam->getInstallationStatus() > 100) {
                    $form->add('rebootAt', 'time', array(
                        'label' => 'steam.rebootAt',
                        'required' => false
                    ));
                }
                if ($steam->getMachine()->getNbCore() != null) {
                    $choices = array_combine(
                        range(0, $steam->getMachine()->getNbCore()-1),
                        range(1, $steam->getMachine()->getNbCore())
                    );

                    $form->add('core', 'choice', array(
                        'label'    => 'game.core',
                        'choices'  => $choices,
                        'multiple' => true,
                        'required' => false,
                        'expanded' => true,
                    ));
                };
            }
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var Symfony\Component\Form\FormInterface $form */
            $form  = $event->getForm();
            /** @var DP\GameServer\SteamServerBundle\Entity\SteamServer $steam */
            $steam = $event->getData();

            if ($steam->getGame() !== null && $steam->getGame()->getAppId() == 740) { // == csgo
                $form->add('mode', 'choice', array(
                    'choices' => SteamServer::getModeList(),
                    'empty_value' => 'steam.chooseGameMode',
                    'label' => 'steam.gameMode',
                ));
            }
            elseif ($steam->getGame() !== null) {
                $form->remove('mode');
            }
        });
    }

    public function getName()
    {
        return 'dedipanel_steam';
    }
}
