<?php

namespace DP\GameServer\MinecraftServerBundle\Security;

use DP\GameServer\GameServerBundle\Security\GameChildrenRoleBuilder;

class MinecraftRoleBuilder extends GameChildrenRoleBuilder
{
    public function getBaseRole()
    {
        return 'ROLE_DP_MINECRAFT';
    }
}
