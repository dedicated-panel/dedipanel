<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\SteamServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use DP\GameServer\SteamServerBundle\Entity\SteamServer;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SteamServerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $steam = $builder->getData();

        $builder
            ->add('name', 'text', ['label' => 'game.name'])
            ->add('machine', 'dedipanel_machine_entity')
            ->add('dir', 'text', ['label' => 'game.dir'])
            ->add('port', 'integer', ['label' => 'game.port'])
            ->add('game', 'entity', [
                'label' => 'game.selectGame',
                'class' => 'DPGameBundle:Game',
                'query_builder' => function($repo) {
                    return $repo->getQBAvailableSteamGames();
                },
            ])
            ->add('mode', 'choice', [ // @TODO: Create a GameModeTypEextension
                'choices'     => SteamServer::getModeList(), // @TODO: use KnpDictionaryBundle
                'empty_value' => 'steam.chooseGameMode',
                'label'       => 'steam.gameMode',
                'required'    => false,
            ])
            ->add('maxplayers', 'integer', ['label' => 'game.maxplayers'])
            ->add('rconPassword', 'text', [
                'label'    => 'game.rcon.password',
            ])
            ->add('svPassword', 'text', ['label' => 'steam.svPassword', 'required' => false])
            ->add('core', 'dedipanel_core_assignment', ['machine' => $steam->getMachine()])
            ->add('alreadyInstalled', 'choice', [
                'choices'  => [1 => 'game.yes', 0 => 'game.no'], // @TODO: use KnpDictionaryBundle
                'label'    => 'game.isAlreadyInstalled',
                'expanded' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Symfony\Component\Form\FormInterface $form */
            $form  = $event->getForm();
            /** @var \DP\GameServer\SteamServerBundle\Entity\SteamServer $steam */
            $steam = $event->getData();

            if ($steam->getId() != null && $steam->getInstallationStatus() > 100) {
                $form->add('rebootAt', 'time', [
                    'label' => 'steam.rebootAt',
                    'required' => false
                ]);
            }
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var \Symfony\Component\Form\FormInterface $form */
            $form  = $event->getForm();
            /** @var \DP\GameServer\SteamServerBundle\Entity\SteamServer $steam */
            $steam = $event->getData();

            if ($steam->getGame() !== null && $steam->getGame()->getAppId() == 740) { // == csgo
                $form->add('mode', 'choice', [
                    'choices' => SteamServer::getModeList(),
                    'empty_value' => 'steam.chooseGameMode',
                    'label' => 'steam.gameMode',
                ]);
            }
            elseif ($steam->getGame() !== null) {
                $form->remove('mode');
            }
        });
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
        return 'dedipanel_steam';
    }
}
