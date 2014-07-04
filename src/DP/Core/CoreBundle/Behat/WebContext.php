<?php

namespace DP\Core\CoreBundle\Behat;

class WebContext extends DefaultContext
{
    /**
     * @Given /^I am on the (.+) (page)?$/
     * @When /^I go to the (.+) (page)?$/
     */
    public function iAmOnThePage($page)
    {
        $this->getSession()->visit($this->generatePageUrl($page));
    }

    /**
     * @Then /^I should be on the (.+) (page)$/
     * @Then /^I should be redirected to the (.+) (page)$/
     * @Then /^I should still be on the (.+) (page)$/
     */
    public function iShouldBeOnThePage($page)
    {
        $this->assertSession()->addressEquals($this->generatePageUrl($page));
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Given /^I leave "([^"]*)" empty$/
     * @Given /^I leave "([^"]*)" field blank/
     */
    public function iLeaveFieldEmpty($field)
    {
        $this->fillField($field, '');
    }

    /**
     * @Given /^I should see (\d+) validation errors$/
     */
    public function iShouldSeeFieldsOnError($amount)
    {
        $this->assertSession()->elementsCount('css', '.alert-error > ul > li', $amount);
    }
}
