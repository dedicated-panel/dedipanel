<?php

namespace DP\Admin\UserBundle\Processor;

use DP\Admin\AdminBundle\Processor\CRUDProcessorInterface;
use FOS\UserBundle\Model\GroupManagerInterface;
use DP\Admin\AdminBundle\Descriptor\Descriptor;

class GroupProcessor implements CRUDProcessorInterface
{
    private $manager;
    private $descriptor;
    
    public function __construct(GroupManagerInterface $manager, Descriptor $descriptor)
    {
        $this->manager    = $manager;
        $this->descriptor = $descriptor;
    }
    
    public function createProcess($entity)
    {
        $this->manager->updateGroup($entity);
    }
    
    public function updateProcess($entity)
    {
        $this->manager->updateGroup($entity);
    }
    
    public function deleteProcess($entity)
    {
        $this->maanger->deleteGroup($entity);
    }
    
    public function batchDeleteProcess($elements)
    {
        $i = 0;
        
        foreach ($elements AS $el) {
            $this->manager->deleteGroup($el);
            
            ++$i;
            
            // Vide le cache de l'ORM afin de ne pas consommer trop de mémoire
            if (($i % 50) == 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }
        
        $this->em->flush();
    }
}
