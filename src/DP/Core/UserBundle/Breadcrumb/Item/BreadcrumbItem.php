<?php

namespace DP\Core\UserBundle\Breadcrumb\Item;

class BreadcrumbItem implements BreadcrumbItemInterface
{
    private $label;
    private $route;
    private $extras;
    
    
    public function __construct($label = null, $route = null, array $extras = array())
    {
        $this->label = $label;
        $this->route = $route;
        $this->extras = $extras;
    }
    
    public function setLabel($label)
    {
        $this->label = $label;
        
        return $this;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setRoute($route)
    {
        $this->route = $route;
        
        return $this;
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    public function setExtras(array $extras)
    {
        $this->extras = $extras;
        
        return $this;
    }
    
    public function getExtras()
    {
        return $this->extras;
    }
}
