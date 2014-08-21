<?php

namespace DP\Core\CoreBundle\Behat;

class AdminContext extends DefaultContext
{
    /**
     * @When /^I (?:click|press|follow) "([^"]*)" near "([^"]*)"$/
     */
    public function iClickNear($button, $value)
    {
        $selector = sprintf('table tbody tr:contains("%s")', $value);
        $tr = $this->assertSession()->elementExists('css', $selector);

        $locator = sprintf('button:contains("%s")', $button);

        if ($tr->has('css', $locator)) {
            $tr->find('css', $locator)->press();
        } else {
            $tr->clickLink($button);
        }
    }
}
