<?php

namespace DP\Admin\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'game_admin.fields.name'))
            ->add('installName', null, array('label' => 'game_admin.fields.installName'))
            ->add('launchName', null, array('label' => 'game_admin.fields.launchName'))
            ->add('bin', null, array('label' => 'game_admin.fields.bin'))
            ->add('binDir', null, array('label' => 'game_admin.fields.binDir'))
            ->add('orangebox', null, array('label' => 'game_admin.fields.isOrangebox', 'required' => false))
            ->add('source', null, array('label' => 'game_admin.fields.source', 'required' => false))
            ->add('steamCmd', null, array('label' => 'game_admin.fields.isSteamCmd', 'required' => false))
            ->add('appId', null, array('label' => 'game_admin.fields.appId'))
            ->add('appMod', null, array('label' => 'game_admin.fields.appMod'))
            ->add('map', null, array('label' => 'game_admin.fields.map'))
            ->add('configTemplate', null, array('label' => 'game_admin.fields.configTemplate'))
            ->add('sourceImagesMaps', null, array('label' => 'game_admin.fields.sourceImagesMaps'))
            ->add('plugins', null, array('label' => 'game_admin.fields.plugins'))
            ->add('type', 'choice', array(
                'choices' => array('steam' => 'Steam', 'minecraft' => 'Minecraft'), 
                'label' => 'game_admin.fields.type', 
            ))->add('available', null, array('label' => 'game_admin.fields.available', 'required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DP\Core\GameBundle\Entity\Game'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dp_core_gamebundle_game';
    }
}
