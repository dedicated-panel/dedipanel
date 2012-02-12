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

namespace DP\Core\GameBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;
class GameAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('installName')
            ->add('launchName')
            ->add('orangebox')
            ->add('bin')
            ->add('map')
            ->add('available', 'boolean')
        ;
    }
    
    protected function configureDatagridFilters(DatagridMapper $filterMapper) 
    {
        $filterMapper
            ->add('name')
            ->add('installName')
            ->add('launchName')
            ->add('orangebox')
            ->add('bin')
            ->add('available')
            ->add('id')
        ;
    }
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('installName')
            ->add('launchName')
            ->add('bin')
            ->add('binDir')
            ->add('orangebox', null, array('required' => false))
            ->add('map')
            ->add('available', null, array('required' => false))
            ->add('sourceImagesMaps', null, array('required' => false))
//            ->add('plugin')
        ;
    }
}
