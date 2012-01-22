<?php

namespace DP\Core\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PluginType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'game.name'))
            ->add('downloadUrl', 'text', array('label' => 'plugin.downloadUrl'))
            ->add('archiveType', 'text', array('label' => 'plugin.archiveType'))
            ->add('scriptName', 'text', array('label' => 'plugin.scriptName'))
        ;
    }

    public function getName()
    {
        return 'dp_core_gamebundle_plugintype';
    }
}
