<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PluginType extends AbstractType
{
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DP\Core\GameBundle\Entity\Plugin'
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'plugin.fields.name'))
            ->add('version', null, array('label' => 'plugin.fields.version'))
            ->add('downloadUrl', null, array('label' => 'plugin.fields.download_url'))
            ->add('scriptName', null, array('label' => 'plugin.fields.install_script'))
            ->add('games', null, array('label' => 'plugin.fields.games', 'by_reference' => false, 'required' => false))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dedipanel_plugin';
    }
}
