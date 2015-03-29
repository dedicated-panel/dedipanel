<?php

/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DP\Core\CoreBundle\Behat;

class AdminContext extends DefaultContext
{
    /**
     * @Then /^I should be on the page of ([^""(w)]*) "([^""]*)"$/
     * @Then /^I should still be on the page of ([^""(w)]*) "([^""]*)"$/
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
        $selector = sprintf('table tbody tr:contains("%s")', $value);
        $tr = $this->assertSession()->elementExists('css', $selector);

        $locator = sprintf('button:contains("%s")', $button);

        if ($tr->has('css', $locator)) {
            $tr->find('css', $locator)->press();
        } else {
            $tr->clickLink($button);
        }
    }

    /**
     * @Then /^I should see "([^"]*)" near "([^"]*)"$/
     */
    public function iShouldSeeNear($text, $value)
    {
        $selector = sprintf('*:contains("%s") + td', $value);

        $this->assertSession()->elementContains('css', $selector, $text);
    }

    /**
     * @Then /^I should see the dedipanel version "(?<version>[^"]+)"$/
     */
    public function iShouldSeeTheDedipanelVersion($version)
    {
        $this->assertSession()->elementContains('css', 'footer p', sprintf('DediPanel v%s', $version));
    }

    /**
     * @Then /^I should see "(?<value>[^"]+)" selected in "(?<select>[^"]+)"$/
     */
    public function iShouldSeeSelectedIn($select, $value)
    {
        $locator = sprintf('//select[@name="%s"]/option[@selected]', $select, $value);

        $this->assertSession()->elementsCount('xpath', $locator, 1);
    }
}
