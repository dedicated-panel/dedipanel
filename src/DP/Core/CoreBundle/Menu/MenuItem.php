<?php

/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Menu;

use Knp\Menu\MenuItem as BaseMenuItem;

class MenuItem extends BaseMenuItem
{
    private $pattern;
    
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        
        return $this;
    }
    
    public function getPattern()
    {
        return $this->pattern;
    }
    
    /**
     * Get whether or not this menu item is "current"
     *
     * @return bool
     */
    public function isCurrent()
    {
        if (null === $this->isCurrent) {
            $pattern = $this->getPattern();
            
            $this->isCurrent = false;
            
            // Impossible à déterminer s'il n'y a pas de pattern de fourni
            if (!empty($pattern)) {
                $pattern = '#^' . $pattern . '#';
                
                if (preg_match($pattern, $this->getCurrentUri()) == 1) {
                    $this->isCurrent = true;
                }
            }
            
        }

        return $this->isCurrent;
    }
}
