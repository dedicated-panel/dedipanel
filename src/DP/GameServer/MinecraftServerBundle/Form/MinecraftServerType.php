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

namespace DP\GameServer\MinecraftServerBundle\Form;

use DP\Core\GameBundle\Entity\GameRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class MinecraftServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('machine', 'dedipanel_machine_entity')
            ->add('name', 'text', array('label' => 'game.name'))
            ->add('port', 'integer', array('label' => 'game.port'))
            ->add('queryPort', 'integer', array('label' => 'minecraft.queryPort'))
            ->add('rconPort', 'integer', array('label' => 'minecraft.rcon.port'))
            ->add('rconPassword', 'text', array('label' => 'game.rcon.password'))
            ->add('game', 'entity', array(
                'label' => 'game.selectGame', 'class' => 'DPGameBundle:Game', 
                'query_builder' => function(GameRepository $repo) {
                    return $repo->getQBAvailableMinecraftGames();
                }))
            ->add('dir', 'text', array('label' => 'game.dir'))
            ->add('maxplayers', 'integer', array('label' => 'game.maxplayers'))
            ->add('minHeap', 'integer', array('label' => 'minecraft.minHeap'))
            ->add('maxHeap', 'integer', array('label' => 'minecraft.maxHeap'))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form      = $event->getForm();
            /** @var DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer $minecraft */
            $minecraft = $event->getData();

            if ($minecraft->getId() === null) {
                $form->add('alreadyInstalled', 'choice', array(
                    'choices'  => array(1 => 'game.yes', 0 => 'game.no'),
                    'label'    => 'game.isAlreadyInstalled',
                    'expanded' => true,
                ));
            }
            elseif ($minecraft->getMachine()->getNbCore() != null) {
                $choices = array_combine(
                    range(0, $minecraft->getMachine()->getNbCore()-1),
                    range(1, $minecraft->getMachine()->getNbCore())
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
        return 'dedipanel_minecraft';
    }
}
