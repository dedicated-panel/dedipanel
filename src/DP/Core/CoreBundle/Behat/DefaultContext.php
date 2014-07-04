<?php

namespace DP\Core\CoreBundle\Behat;

use Sylius\Bundle\ResourceBundle\Behat\DefaultContext as BaseDefaultContext;

class DefaultContext extends BaseDefaultContext
{
    /** {@inheritdoc} */
    protected function generatePageUrl($page, array $parameters = array())
    {
        if (is_object($page)) {
            return $this->locatePath($this->generateUrl($page, $parameters));
        }

        $route  = str_replace(' ', '_', trim($page));
        $routes = $this->getContainer()->get('router')->getRouteCollection();

        if (null === $routes->get($route)) {
            $route = 'dedipanel_'.$route;
        }

        $route = str_replace(array_keys($this->actions), array_values($this->actions), $route);
        $route = str_replace(' ', '_', $route);

        return $this->locatePath($this->generateUrl($route, $parameters));
    }
}