<?php
namespace DP\Admin\GameBundle\Controller;

use DP\Admin\AdminBundle\Controller\CRUDController;

/**
 * Game controller.
 *
 */
class GameController extends CRUDController
{
    protected function getDescriptor()
    {
        return $this->get('dp.descriptor.game_admin');
    }
}
