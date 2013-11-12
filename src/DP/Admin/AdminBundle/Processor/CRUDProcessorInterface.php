<?php

namespace DP\Admin\AdminBundle\Processor;

interface CRUDProcessorInterface
{
    public function createProcess($entity);
    
    public function updateProcess($entity);
    
    public function deleteProcess($entity);
    
    public function batchDeleteProcess($elements);
}
