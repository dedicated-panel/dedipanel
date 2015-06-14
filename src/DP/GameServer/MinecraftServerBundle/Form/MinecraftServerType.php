<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\MinecraftServerBundle\Form;

use DP\Core\GameBundle\Entity\GameRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MinecraftServerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $minecraft = $builder->getData();

        $builder
            ->add('name', 'text', ['label' => 'game.name'])
            ->add('machine', 'dedipanel_machine_entity')
            ->add('port', 'integer', ['label' => 'game.port'])
            ->add('game', 'entity', [
                'label' => 'game.selectGame',
                'class' => 'DPGameBundle:Game',
                'query_builder' => function(GameRepository $repo) {
                    return $repo->getQBAvailableMinecraftGames();
                },
            ])
            ->add('dir', 'text', ['label' => 'game.dir'])
            ->add('queryPort', 'integer', ['label' => 'minecraft.queryPort'])
            ->add('rconPort', 'integer', ['label' => 'minecraft.rcon.port'])
            ->add('rconPassword', 'text', ['label' => 'game.rcon.password'])
            ->add('maxplayers', 'integer', ['label' => 'game.maxplayers'])
            ->add('minHeap', 'integer', ['label' => 'minecraft.minHeap'])
            ->add('maxHeap', 'integer', ['label' => 'minecraft.maxHeap'])
            ->add('core', 'dedipanel_core_assignment', ['machine' => $minecraft->getMachine()])
            ->add('alreadyInstalled', 'dictionary', [
                'name'     => 'yes_no',
                'label'    => 'game.isAlreadyInstalled',
                'expanded' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'remove_on_create'  => ['core'],
                'remove_on_update'  => ['alreadyInstalled'],
                'disable_on_update' => ['machine', 'game', 'dir'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dedipanel_minecraft';
    }
}
