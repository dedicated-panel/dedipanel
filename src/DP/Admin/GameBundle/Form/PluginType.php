<?php

namespace DP\Admin\GameBundle\Form;

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
            ->add('name', null, array('label' => 'plugin_admin.fields.name'))
            ->add('version', null, array('label' => 'plugin_admin.fields.version'))
            ->add('download_url', null, array('label' => 'plugin_admin.fields.download_url'))
            ->add('scriptName', null, array('label' => 'plugin_admin.fields.install_script'))
            ->add('games', null, array('label' => 'plugin_admin.fields.games'))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dp_core_gamebundle_plugin';
    }
}
