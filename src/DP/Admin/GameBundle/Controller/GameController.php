<?php
namespace DP\Admin\GameBundle\Controller;

use DP\Core\GameBundle\Entity\Game;
use DP\Admin\GameBundle\Form\GameType;
use DP\Admin\AdminBundle\Controller\CRUDController;

/**
 * Game controller.
 *
 */
class GameController extends CRUDController
{
    protected function createEntity()
    {
        return new Game();
    }
    
    protected function getRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('DPGameBundle:Game');
    }
    
    protected function getBaseRoute()
    {
        return 'game';
    }
    
    protected function getTplDir()
    {
        return 'Game';
    }
    
    protected function getFormType()
    {
        return new GameType();
    }
}
