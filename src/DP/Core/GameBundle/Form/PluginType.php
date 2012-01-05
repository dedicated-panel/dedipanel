<?php

namespace DP\Core\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PluginType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('downloadUrl')
            ->add('archiveType')
            ->add('scriptName')
            ->add('games')
        ;
    }

    public function getName()
    {
        return 'dp_core_gamebundle_plugintype';
    }
}
