<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Exception;

/**
 * @author Albin Kerouanton 
 */
class InstallAlreadyStartedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Installation is already started.");
    }
}
