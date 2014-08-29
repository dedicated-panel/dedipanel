<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Menu;

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

        $this->addRootMenuItems($menu);
        
        $admin = $this->factory->createItem('menu.admin.admin', array('extras' => array('icon' => 'icon-option')));
        $this->addAdminMenuItems($admin);
        $admin->setDisplay($admin->hasChildren());
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
                'route'          => '_welcome',
                'extras'         => array('icon' => 'icon-home'),
                'pattern_strict' => true
            ))
        ;
        $menu
            ->addChild('menu.steam', array(
                'route'  => 'dedipanel_steam_index',
                'extras' => array('icon' => ' icon-steam'),
            ))
            ->setDisplay($context->isGranted('ROLE_DP_GAME_STEAM_INDEX'))
        ;
        $menu
            ->addChild('menu.minecraft', array(
                'route'  => 'dedipanel_minecraft_index',
                'extras' => array('icon' => 'icon-minecraft'),
            ))
            ->setDisplay($context->isGranted('ROLE_DP_GAME_MINECRAFT_INDEX'))
        ;
        $menu
            ->addChild('menu.teamspeak', array(
                'route'  => 'dedipanel_teamspeak_index',
                'extras' => array('icon' => 'icon-headphone'),
            ))
            ->setDisplay($context->isGranted('ROLE_DP_VOIP_TEAMSPEAK_INDEX'))
        ;
        $menu
            ->addChild('menu.machine', array(
                'route'  => 'dedipanel_machine_index',
                'extras' => array('icon' => 'icon-monitor'),
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
                'route' => 'dedipanel_core_config',
                'extras' => array('icon' => 'icon-cogs'),
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_CONFIG'))
        ;
        $admin
            ->addChild('menu.admin.user', array(
                'route' => 'dedipanel_user_index',
                'extras' => array('icon' => 'icon-uni2F'),
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_USER_INDEX'))
        ;
        $admin
            ->addChild('menu.admin.group', array(
                'route' => 'dedipanel_group_index',
                'extras' => array('icon' => 'icon-users')
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_GROUP_INDEX'))
        ;
        $admin
            ->addChild('menu.admin.game', array(
                'route' => 'dedipanel_game_index',
                'extras' => array('icon' => 'icon-steam3')
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_GAME_INDEX'))
        ;
        $admin
            ->addChild('menu.admin.plugin', array(
                'route' => 'dedipanel_plugin_index',
                'extras' => array('icon' => 'icon-uni34')
            ))
            ->setDisplay($context->isGranted('ROLE_DP_ADMIN_PLUGIN_INDEX'))
        ;
    }
}
