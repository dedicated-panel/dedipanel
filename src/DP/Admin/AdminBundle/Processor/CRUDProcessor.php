<?php

namespace DP\Admin\AdminBundle\Processor;

use Doctrine\ORM\EntityManager;
use DP\Admin\AdminBundle\Descriptor\Descriptor;

class CRUDProcessor implements CRUDProcessorInterface
{
    private $em;
    private $descriptor;
    
    public function __construct(EntityManager $manager, Descriptor $descriptor)
    {
        $this->em         = $manager;
        $this->descriptor = $descriptor;
    }
    
    public function createProcess($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
    
    public function updateProcess($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
    
    public function deleteProcess($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
    
    public function batchDeleteProcess($elements)
    {
        $repo = $this->descriptor->getRepository();
        $i = 0;
        
        foreach ($elements AS $el) {
            $entity = $repo->find($el);
            
            if ($entity !== null) {
                $this->em->remove($entity);
                
                ++$i;
                
                // Vide le cache de l'ORM afin de ne pas consommer trop de mémoire
                if (($i % 50) == 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }
        }
        
        $this->em->flush();
        $this->em->clear();
    }
}
