<?php

namespace DP\Core\UserBundle\Breadcrumb\Item;

interface BreadcrumbItemInterface
{
    /**
     * Set label
     * 
     * @param string $label
     * @return BreadcrumbItemInterface
     */
    public function setLabel($label);
    
    /**
     * Get label
     * 
     * @return string
     */
    public function getLabel();
    
    /**
     * Set route
     * 
     * @param string $route
     * @return BreadcrumbItemInterface
     */
    public function setRoute($route);
    
    /**
     * Get route
     * 
     * @return string
     */
    public function getRoute();
    
    /**
     * Set route parameters
     * 
     * @param array $parameters
     * @return BreadcrumItemInterface
     */
    public function setRouteParameters(array $parameters = array());
    
    /**
     * Get route parameters
     * 
     * @return arrray
     */
    public function getRouteParameters();
    
    /**
     * Set extras
     * 
     * @param array $extras
     * @return BreadcrumbItemInterface
     */
    public function setExtras(array $extras);
    
    /**
     * Get extras
     * 
     * @return array
     */
    public function getExtras();
}
