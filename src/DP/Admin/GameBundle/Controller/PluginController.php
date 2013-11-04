<?php

namespace DP\Admin\GameBundle\Controller;

use DP\Core\GameBundle\Entity\Plugin;
use DP\Admin\GameBundle\Form\PluginType;
use DP\Admin\AdminBundle\Controller\CRUDController;

class PluginController extends CRUDController
{
    protected function createEntity()
    {
        return new Plugin();
    }
    
    protected function getRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('DPGameBundle:Plugin');
    }
    
    protected function getBaseRoute()
    {
        return 'plugin';
    }
    
    protected function getTplDir()
    {
        return 'Plugin';
    }
    
    protected function getFormType()
    {
        return new PluginType();
    }
}
