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
            ->add('name', 'text', array('label' => 'steam.name'))
            ->add('port', 'number', array('label' => 'steam.port'))
            ->add('game', 'entity', array(
                'label' => 'steam.selectGame', 'class' => 'DPGameBundle:Game', 
                'query_builder' => function($repo) {
                    return $repo->getQBAvailableMinecraftGames();
                }))
            ->add('dir', 'text', array('label' => 'steam.dir'))
            ->add('maxplayers', 'number', array('label' => 'steam.maxplayers'))
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
