<?php

namespace DP\Core\UserBundle\Breadcrumb\Item;

class BreadcrumbItem implements BreadcrumbItemInterface
{
    private $label;
    private $route;
    private $extras;
    private $routeParameters;
    
    
    public function __construct($label = null, $route = null, array $routeParameters = array(), array $extras = array())
    {
        $this->label = $label;
        $this->route = $route;
        $this->routeParameters = $routeParameters;
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
    
    public function setRouteParameters(array $parameters = array())
    {
        $this->routeParameters = $parameters;
        
        return $this;
    }
    
    public function getRouteParameters()
    {
        return $this->routeParameters;
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
