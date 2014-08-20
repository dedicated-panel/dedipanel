<?php

namespace DP\Core\CoreBundle\Behat;

use Behat\Behat\Hook\Scope\BeforeFeatureScope;

class ServerContext extends DefaultContext
{
    /**
     * @When /^I (?:click|press|follow) "([^"]*)" near "([^"]*)"$/
     */
    public function iClickNear($button, $value)
    {
        $selector = sprintf('.server-list .server-item:contains("%s")', $value);
        $item = $this->assertSession()->elementExists('css', $selector);

        $locator = sprintf('button:contains("%s")', $button);

        if ($item->has('css', $locator)) {
            $item->find('css', $locator)->press();
        } else {
            $item->clickLink($button);
        }
    }

    /**
     * @Then /^I should see "([^"]*)" near "([^"]*)"$/
     */
    public function iShouldSeeNear($text, $value)
    {
        $selector = sprintf('.server-list .server-item:contains("%s")', $value);

        $this->assertSession()->elementContains('css', $selector, $text);
    }

    /**
     * @Then /^I should not see "([^"]*)" near "([^"]*)"$/
     */
    public function iShouldNotSeeNear($text, $value)
    {
        $selector = sprintf('.server-list .server-item:contains("%s")', $value);

        $this->assertSession()->elementNotContains('css', $selector, $text);
    }
}
