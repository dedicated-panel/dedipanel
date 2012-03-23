<?php

namespace DP\Core\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class GameType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'game.name'))
            ->add('installName', 'text', array('label' => 'game.installName'))
            ->add('launchName', 'text', array('label' => 'game.launchName'))
            ->add('binDir', 'text', array('label' => 'game.binDir', 'required' => false))
            ->add('bin', 'choice', array(
                'choices' => array('hlds_run' => 'hlds_run', 'srcds_run' => 'srcds_run'), 
                'label' => 'game.bin'))
            ->add('orangebox', 'checkbox', array(
                'label' => 'game.orangebox', 'required' => false))
            ->add('map', 'text', array('label' => 'game.map'))
            ->add('available', 'checkbox', array(
                'label' => 'game.available', 'required' => false))
            ->add('sourceImagesMaps', 'text', array('label' => 'game.sourceImagesMaps', 'required' => false))
            ->add('plugins', 'entity', array(
                'multiple' => true, 'class' => 'DP\Core\GameBundle\Entity\Plugin', 
                'label' => 'game.plugins', 'required' => false))
        ;
    }

    public function getName()
    {
        return 'dp_gamebundle_gametype';
    }
}
