<?php

namespace DP\Admin\AdminBundle\Descriptor;

use DP\Admin\AdminBundle\Entity\Factory\FactoryInterface;
use Doctrine\ORM\EntityRepository;
use DP\Admin\AdminBundle\Processor\CRUDProcessorInterface;
use DP\Admin\AdminBundle\Security\ChildRoleBuilderInterface;

class Descriptor
{
    private $name;
    private $routes;
    private $templates;
    private $forms;
    private $entityFactory;
    private $entityRepository;
    private $processor;
    private $roleBuilder;
    
    
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
        
        return $this;
    }
    
    public function getRoutes()
    {
        return $this->routes;
    }
    
    public function getRoute($route)
    {
        if (!isset($this->routes[$route])) {
            throw new \RuntimeException("Aucune route ne correspondant à l'alias $route n'a été trouvé.");
        }
        
        return $this->routes[$route];
    }
    
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
        
        return $this;
    }
    
    public function getTemplates()
    {
        return $this->templates;
    }
    
    public function getTemplate($template)
    {
        if (!isset($this->templates[$template])) {
            throw new \RuntimeException("Aucun template ne correspondant à l'alias $template n'a été trouvé.");
        }
        
        return $this->templates[$template];
    }
    
    public function setForms(array $forms)
    {
        $this->forms = $forms;
        
        return $this;
    }
    
    public function getForms()
    {
        return $this->forms;
    }
    
    public function getForm($form)
    {
        if ((!isset($this->forms[$form]))) {
            throw new \RuntimeException("Aucun formulaire ne correspondant à l'alias $form n'a été trouvé.");
        }
        
        return $this->forms[$form];
    }
    
    public function setEntityFactory(FactoryInterface $factory)
    {
        $this->entityFactory = $factory;
        
        return $this;
    }
    
    public function getEntityFactory()
    {
        return $this->entityFactory;
    }
    
    public function setEntityRepository(EntityRepository $repository)
    {
        $this->entityRepository = $repository;
        
        return $this;
    }
    
    public function getEntityRepository()
    {
        return $this->entityRepository;
    }
    
    public function getRepository()
    {
        return $this->entityRepository;
    }
    
    public function getFactory()
    {
        return $this->entityFactory;
    }
    
    public function setProcessor(CRUDProcessorInterface $processor)
    {
        $this->processor = $processor;
        
        return $this;
    }
    
    public function getProcessor()
    {
        return $this->processor;
    }
    
    public function setRoleBuilder(ChildRoleBuilderInterface $roleBuilder = null)
    {
        $this->roleBuilder = $roleBuilder;
        
        return $this;
    }
    
    public function getRoleBuilder()
    {
        return $this->roleBuilder;
    } 
}
