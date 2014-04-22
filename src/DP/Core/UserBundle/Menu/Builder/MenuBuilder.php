<?php

namespace DP\Core\UserBundle\Menu\Builder;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class MenuBuilder
{
    /** @var \Knp\Menu\FactoryInterface $factory **/
    private $factory;
    /** @var \Symfony\Component\Security\Core\SecurityContextInterface $context **/
    private $context;
    
    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $context
     */
    public function __construct(FactoryInterface $factory, SecurityContextInterface $context)
    {
        $this->factory = $factory;
        $this->context = $context;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        
        $admin = $this->factory->createItem('menu.admin.admin', array('extras' => array('icon' => '%')));
        $this->addAdminMenuItems($admin);
        $admin->setDisplay($admin->hasChildren());
        
        $this->addRootMenuItems($menu);
        $menu->addChild($admin);
        
        $menu->setCurrentUri($request->getRequestUri());
        
        return $menu;
    }
    
    /**
     * @param \Knp\Menu\ItemInterface $menu
     */
    private function addRootMenuItems(ItemInterface $menu)
    {
        $context = $this->context;
        
        $menu
            ->addChild('menu.home', array(
                'route' => '_welcome', 
                'extras' => array('icon' => 'P'), 
                'pattern_strict' => true
            ))
        ;
        $menu
            ->addChild('menu.steam', array(
                'route' => 'dedipanel_steam_index', 
                'extras' => array('icon' => 'o')
            ))
            ->setDisplay($context->isGranted('ROLE_DP_GAME_STEAM_INDEX'))
        ;
        $menu
            ->addChild('menu.minecraft', array(
                'route' => 'dedipanel_minecraft_index', 
                'extras' => array('icon' => 'R')
            ))
            ->setDisplay($context->isGranted('ROLE_DP_GAME_MINECRAFT_INDEX'))
        ;
        $menu
            ->addChild('menu.machine', array(
                'route' => 'dedipanel_machine_index', 
                'extras' => array('icon' => 'Q')
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_MACHINE_INDEX'))
        ;
    }
    /**
     * @param \Knp\Menu\ItemInterface $admin
     */
    private function addAdminMenuItems(ItemInterface $admin)
    {
        $context = $this->context;

        $admin
            ->addChild('menu.admin.config', array(
                'route' => 'dedipanel_core_config'
            ))
            ->setDisplay($context->isGranted('ROLE_SUPER_ADMIN'))
        ;
        $admin
            ->addChild('menu.admin.user', array(
                'route' => 'dedipanel_user_index'
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_USER_INDEX'))
        ;
        $admin
            ->addChild('menu.admin.group', array(
                'route' => 'dedipanel_group_index'
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_GROUP_INDEX'))
        ;
        $admin
            ->addChild('menu.admin.game', array(
                'route' => 'dedipanel_game_index'
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_GAME_INDEX'))
        ;
        $admin
            ->addChild('menu.admin.plugin', array(
                'route' => 'dedipanel_plugin_index'
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_PLUGIN_INDEX'))
        ;
    }
}
