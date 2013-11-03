<?php

namespace DP\Core\UserBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MenuBuilder extends ContainerAware
{
    public function mainMenu(FactoryInterface $builder, array $options)
    {
        $menu = $builder->createItem('root');
        
        $menu->addChild('menu.home', array('route' => '_welcome', 'extras' => array('icon' => 'P')));
        $menu->addChild('menu.steam', array('route' => 'steam', 'extras' => array('icon' => 'o')));
        $menu->addChild('menu.minecraft', array('route' => 'minecraft', 'extras' => array('icon' => 'R')));
        $menu->addChild('menu.machine', array('route' => 'machine', 'extras' => array('icon' => 'Q')));
        
        $admin = $builder->createItem('menu.admin.admin', array('extras' => array('icon' => '%')));
        $admin->addChild('menu.admin.game', array('route' => 'game_admin'));
        $menu->addChild($admin);
        
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        
        return $menu;
    }
}
