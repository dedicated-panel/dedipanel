<?php

namespace DP\Core\UserBundle\Breadcrumb\Builder;

use Knp\Menu\FactoryInterface;
use DP\Core\UserBundle\Breadcrumb\Bag\BreadcrumbItemsBagInterface;
use Symfony\Component\HttpFoundation\Request;

class BreadcrumbBuilder
{
    private $factory;
    
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }
    
    public function createBreadcrumb(Request $request, BreadcrumbItemsBagInterface $items)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'breadcrumb');
        
        foreach ($items AS $item) {
            $itemOptions = array();
            
            $itemOptions['route']  = $item->getRoute();
            $itemOptions['extras'] = $item->getExtras();
            
            $menu->addChild($item->getLabel(), $itemOptions);
        }
        
        $menu->setCurrentUri($request->getRequestUri());
        
        return $menu;
    }
}
