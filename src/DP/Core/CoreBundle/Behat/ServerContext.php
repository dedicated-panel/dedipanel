<?php

namespace DP\Core\CoreBundle\Behat;

use Behat\Behat\Hook\Scope\BeforeFeatureScope;

class ServerContext extends DefaultContext
{
    /**
     * @Then /^I should be on the page of ([^""(w)]*) server "([^""]*)"$/
     * @Then /^I should still be on the page of ([^""(w)]*) server "([^""]*)"$/
     */
    public function iShouldBeOnTheResourcePageByName($type, $name)
    {
        $this->iShouldBeOnTheResourcePage($type, 'name', $name);
    }

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

    /**
     * @Then /^I should be on the ftp page of ([^""]*) "([^""]*)"$/
     */
    public function iShouldBeOnTheFtpPage($type, $name)
    {
        $type = str_replace(' ', '_', $type);
        $resource = $this->findOneBy($type, array('name' => $name));

        $this->assertSession()->addressEquals($this->generatePageUrl(sprintf('%s_ftp_show', $type), array('id' => $resource->getId())));
        $this->assertStatusCodeEquals(200);
    }
}
