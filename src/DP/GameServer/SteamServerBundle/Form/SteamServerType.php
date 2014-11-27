<?php

/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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
