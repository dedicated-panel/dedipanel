<?php

namespace DP\Admin\GameBundle\Security;

use DP\Admin\AdminBundle\Security\ChildRoleBuilderInterface;

class GameRoleBuilder implements ChildRoleBuilderInterface
{
    public function getRole()
    {
        return 'ROLE_DP_ADMIN_GAME_ADMIN';
    }
}
