<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\GameServer\SteamServerBundle\SteamQuery\Exception;

/**
 * @author Albin Kerouanton 
 */
class UnexpectedServerTypeException extends \Exception
{
    public function __construct($hltvExpected = false)
    {
        $msg = 'Unexpected server type. ';
        
        if ($hltvExpected) {
            $msg .= 'HLTV expected but steam server detected.';
        }
        else {
            $msg .= 'Steam server expected but HLTV detected.';
        }
        
        parent::__construct($msg);
    }
}
