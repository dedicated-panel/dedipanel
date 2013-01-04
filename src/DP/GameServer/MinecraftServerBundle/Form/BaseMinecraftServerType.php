<?php

/*
** Copyright (C) 2010-2012 Kerouanton Albin, Smedts Jérôme
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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class BaseMinecraftServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('machine', 'entity', array(
                'label' => 'game.selectMachine', 'class' => 'DPMachineBundle:Machine'))
            ->add('name', 'text', array('label' => 'game.name'))
            ->add('port', 'integer', array('label' => 'game.port'))
            ->add('queryPort', 'integer', array('label' => 'minecraft.queryPort', 'required' => false))
            ->add('rconPort', 'integer', array('label' => 'minecraft.rcon.port'))
            ->add('rconPassword', 'text', array('label' => 'game.rcon.password'))
            ->add('game', 'entity', array(
                'label' => 'game.selectGame', 'class' => 'DPGameBundle:Game', 
                'query_builder' => function($repo) {
                    return $repo->getQBAvailableMinecraftGames();
                }))
            ->add('dir', 'text', array('label' => 'game.dir'))
            ->add('maxplayers', 'integer', array('label' => 'game.maxplayers'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer'
        ));
    }
}
