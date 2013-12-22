<?php

namespace DP\Admin\UserBundle\Processor;

use DP\Admin\AdminBundle\Processor\CRUDProcessorInterface;
use Doctrine\ORM\Entitymanager;
use FOS\UserBundle\Model\UserManagerInterface;
use DP\Admin\AdminBundle\Descriptor\Descriptor;

class UserProcessor implements CRUDProcessorInterface
{
    private $manager;
    private $descriptor;
    
    public function __construct(EntityManager $em, Descriptor $descriptor, UserManagerInterface $manager)
    {
        $this->em         = $em;
        $this->descriptor = $descriptor;
        $this->manager    = $manager;
    }
    
    public function createProcess($entity)
    {
        $this->manager->updateUser($entity);
    }
    
    public function updateProcess($entity)
    {        
        $this->manager->updateUser($entity);
    }
    
    public function deleteProcess($entity)
    {
        $this->maanger->deleteUser($entity);
    }
    
    public function batchDeleteProcess($elements)
    {
        $i = 0;
        
        foreach ($elements AS $el) {
            $entity = $this->manager->findUserBy(array('id' => $el));
            
            if ($entity !== null) {
                $this->manager->deleteUser($entity);
                
                ++$i;
                
                // Vide le cache de l'ORM afin de ne pas consommer trop de mémoire
                if (($i % 50) == 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }
        }
        
        $this->em->flush();
    }
}
