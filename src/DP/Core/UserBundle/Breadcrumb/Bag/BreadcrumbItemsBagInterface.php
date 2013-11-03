<?php

namespace DP\Core\UserBundle\Breadcrumb\Bag;

interface BreadcrumbItemsBagInterface extends \Iterator
{
    /**
     * @param array $items
     * @return BreadcrumbItemsBagInterface
     */
    public function setItems(array $items);
    
    /**
     * @return array
     */
    public function getItems();
}
