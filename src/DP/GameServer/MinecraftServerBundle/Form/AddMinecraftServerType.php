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

use DP\GameServer\MinecraftServerBundle\Form\BaseMinecraftServerType;
use Symfony\Component\Form\FormBuilderInterface;

class AddMinecraftServerType extends BaseMinecraftServerType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder->add('alreadyInstalled', 'choice', array(
                'choices'   => array('game.no', 'game.yes'), 
                'label'     => 'game.isAlreadyInstalled', 
                'mapped'    => false, 
                'expanded'  => true
            ))
        ;
    }

    public function getName()
    {
        return 'dp_gameserver_minecraftserverbundle_addminecraftservertype';
    }
}
