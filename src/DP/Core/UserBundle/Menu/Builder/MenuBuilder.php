<?php

namespace DP\Core\UserBundle\Menu\Builder;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class MenuBuilder
{
    private $factory;
    private $context;
    
    public function __construct(FactoryInterface $factory, SecurityContextInterface $context)
    {
        $this->factory = $factory;
        $this->context = $context;
    }
    
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        
        $menu
            ->addChild('menu.home', array('route' => '_welcome', 'extras' => array('icon' => 'P'), 'pattern_strict' => true))
        ;
        $menu
            ->addChild('menu.steam', array('route' => 'steam', 'extras' => array('icon' => 'o')))
            ->setDisplay($this->context->isGranted('ROLE_DP_STEAM_SHOW'))
        ;
        $menu
            ->addChild('menu.minecraft', array('route' => 'minecraft', 'extras' => array('icon' => 'R')))
            // ->setDisplay($this->context->isGranted('ROLE_DP_MINECRAFT_SHOW'))
        ;
        $menu
            ->addChild('menu.machine', array('route' => 'machine', 'extras' => array('icon' => 'Q')))
        ;
        
        $admin = $this->factory->createItem('menu.admin.admin', array('extras' => array('icon' => '%')));
        $admin->addChild('menu.admin.user', array('route' => 'user_admin'));
        $admin->addChild('menu.admin.group', array('route' => 'group_admin'));
        $admin->addChild('menu.admin.game', array('route' => 'game_admin'));
        $admin->addChild('menu.admin.plugin', array('route' => 'plugin_admin'));
        $menu->addChild($admin);
        
        $menu->setCurrentUri($request->getRequestUri());
        
        return $menu;
    }
}
