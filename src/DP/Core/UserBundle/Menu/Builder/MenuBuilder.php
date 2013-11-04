<?php

namespace DP\Core\UserBundle\Menu\Builder;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;

class MenuBuilder
{
    private $factory;
    
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }
    
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        
        $menu->addChild('menu.home', array('route' => '_welcome', 'extras' => array('icon' => 'P'), 'pattern_strict' => true));
        $menu->addChild('menu.steam', array('route' => 'steam', 'extras' => array('icon' => 'o')));
        $menu->addChild('menu.minecraft', array('route' => 'minecraft', 'extras' => array('icon' => 'R')));
        $menu->addChild('menu.machine', array('route' => 'machine', 'extras' => array('icon' => 'Q')));
        
        $admin = $this->factory->createItem('menu.admin.admin', array('extras' => array('icon' => '%')));
        $admin->addChild('menu.admin.game', array('route' => 'game_admin'));
        $admin->addChild('menu.admin.plugin', array('route' => 'plugin_admin'));
        $menu->addChild($admin);
        
        $menu->setCurrentUri($request->getRequestUri());
        
        return $menu;
    }
}
