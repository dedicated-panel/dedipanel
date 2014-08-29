<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Exception;

/**
 * @author Albin Kerouanton 
 */
class IPBannedException extends \Exception
{
    private $duration;

    public function __construct($message = 'IP banned from the server.', $duration = null)
    {
        parent::__construct($message);

        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }
}
