<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\GameServerBundle\Query;

interface QueryInterface
{
    /*
     * @return boolean
     * @TODO: renommer en verifyServerType(), et appliquer une vérif sur le type minecraft/bukkit aux serveurs minecraft
     */
    function verifyStatus();
    
    /*
     * @return boolean
     */
    function isOnline();
    
    /*
     * @return boolean
     */
    function isBanned();
}