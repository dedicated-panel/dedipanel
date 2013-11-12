<?php

namespace DP\GameServer\SteamServerBundle\Security;

use DP\GameServer\GameServerBundle\Security\GameChildrenRoleBuilder;

class SteamRoleBuilder extends GameChildrenRoleBuilder
{
    public function getBaseRole()
    {
        return 'ROLE_DP_STEAM';
    }
    
    protected function getRoleHierarchy()
    {
        return parent::getRoleHierarchy() + array( 
            'HLTV'   => array('SHOW', 'HLTV'),  
        );
    }
}
