<?php

namespace DP\Core\GameBundle\Entity\Factory;

use DP\Admin\AdminBundle\Entity\Factory\FactoryInterface;
use DP\Core\GameBundle\Entity\Game;

class GameFactory implements FactoryInterface
{
    public function createEntity()
    {
        return new Game();
    }
}
