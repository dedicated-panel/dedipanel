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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use DP\GameServer\SteamServerBundle\Entity\SteamServer;

class EditSteamServerType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('machine', 'entity', array(
                'label' => 'game.selectMachine', 'class' => 'DPMachineBundle:Machine'))
            ->add('name', 'text', array('label' => 'game.name'))
            ->add('port', 'integer', array('label' => 'game.port'))
            ->add('game', 'entity', array(
                'label' => 'game.selectGame', 
                'class' => 'DPGameBundle:Game',
                'query_builder' => function($repo) {
                    return $repo->getQBAvailableSteamGames();
                }, 
                'read_only' => true, 
            ))
            ->add('dir', 'text', array('label' => 'game.dir'))
            ->add('maxplayers', 'integer', array('label' => 'game.maxplayers'))
        ;
        
        if (isset($options['data'])) {
            $entity = $options['data'];
            
            if ($entity->getGame()->getLaunchName() == 'csgo') {
                $builder->add('mode', 'choice', array(
                    'choices' => SteamServer::getModeList(), 
                    'empty_value' => 'steam.chooseGameMode',
                    'label' => 'steam.gameMode', 
                ));
            }
        }
        
        $builder
            ->add('rconPassword', 'text', array('label' => 'game.rcon.password'))
            ->add('svPassword', 'text', array('label' => 'steam.svPassword', 'required' => false))
            ->add('hltvPort', 'integer', array('label' => 'steam.hltv.port', 'required' => false))
        ;
        
        if (isset($options['data'])) {
            $entity = $options['data'];
            
            if ($entity->getMachine()->getNbCore() != null) {
                $choices = array_combine(
                    range(0, $entity->getMachine()->getNbCore()-1),
                    range(1, $entity->getMachine()->getNbCore())
                );

                $form->add('core', 'choice', array(
                    'label'    => 'game.core',
                    'choices'  => $choices,
                    'multiple' => true,
                    'required' => false,
                ));
            }
        }
        
        $builder
            ->add('rebootAt', 'time', array('label' => 'steam.rebootAt', 'required' => false))
        ;
    }

    public function getName()
    {
        return 'dedipanel_steam_edit';
    }
}
