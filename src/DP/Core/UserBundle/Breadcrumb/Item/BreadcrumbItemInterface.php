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
