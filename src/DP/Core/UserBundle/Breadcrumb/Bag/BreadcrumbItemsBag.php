<?php

namespace DP\Core\UserBundle\Breadcrumb\Bag;

class BreadcrumbItemsBag implements BreadcrumbItemsBagInterface
{
    private $pos = 0;
    private $items = array();
    
    
    public function setItems(array $items)
    {
        $this->items = $items;
        
        $this->rewind();
        
        return $this;
    }
    
    public function getItems()
    {
        return $this->items;
    }
    
    public function rewind()
    {
        $this->pos = 0;
    }
    
    public function current()
    {
        return $this->items[$this->pos];
    }
    
    public function key()
    {
        return $this->pos;
    }
    
    public function next()
    {
        ++$this->pos;
    }
    
    public function valid()
    {
        return isset($this->items[$this->pos]);
    }
}
