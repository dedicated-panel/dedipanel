<?php

namespace DP\JeuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class JeuType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'jeu.name'))
            ->add('installName', 'text', array('label' => 'jeu.installName'))
            ->add('launchName', 'text', array('label' => 'jeu.launchName'))
            ->add('bin', 'choice', array(
                'choices' => array('hlds_run' => 'hlds_run', 'srcds_run' => 'srcds_run'), 
                'label' => 'jeu.bin'))
            ->add('orangebox', 'checkbox', array(
                'label' => 'jeu.orangebox', 'required' => false))
            ->add('map', 'text', array('label' => 'jeu.map'))
            ->add('available', 'checkbox', array(
                'label' => 'jeu.available', 'required' => false))
        ;
    }

    public function getName()
    {
        return 'dp_jeubundle_jeutype';
    }
}
