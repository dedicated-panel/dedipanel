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

namespace Application\Sonata\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType AS BaseProfileFormType;

class ProfileFormType extends BaseProfileFormType
{
    private $class;
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $child = $builder->create('user', 'form', array('data_class' => $this->class));
        $this->buildUserForm($child, $options);

        $builder
            ->add($child)
            ->add('current', 'password', array('label' => 'profile.currentPasswd'))
        ;
    }
    
    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilder $builder
     * @param array       $options
     */
    protected function buildUserForm(FormBuilder $builder, array $options)
    {
        parent::buildUserForm($builder, $options);
        
        $builder
            ->add('password', 'password')
        ;
    }

    public function getName()
    {
        return 'app_user_profile';
    }
}
