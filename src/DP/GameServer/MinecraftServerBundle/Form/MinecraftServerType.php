<?php

namespace DP\GameServer\MinecraftServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MinecraftServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('machine', 'entity', array(
                'label' => 'steam.selectMachine', 'class' => 'DPMachineBundle:Machine'))
            ->add('name', 'text', array('label' => 'game.name'))
            ->add('port', 'number', array('label' => 'game.port'))
            ->add('queryPort', 'number', array('label' => 'minecraft.queryPort'))
            ->add('game', 'entity', array(
                'label' => 'game.selectGame', 'class' => 'DPGameBundle:Game', 
                'query_builder' => function($repo) {
                    return $repo->getQBAvailableMinecraftGames();
                }))
            ->add('dir', 'text', array('label' => 'game.dir'))
            ->add('maxplayers', 'number', array('label' => 'game.maxplayers'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DP\GameServer\MinecraftServerBundle\Entity\MinecraftServer'
        ));
    }

    public function getName()
    {
        return 'dp_gameserver_minecraftserverbundle_minecraftservertype';
    }
}
