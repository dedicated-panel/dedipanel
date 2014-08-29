<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\VoipServer\VoipServerBundle\Exception;

class OfflineServerException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
