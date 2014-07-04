<?php

namespace DP\Core\DistributionBundle\Behat;

use DP\Core\CoreBundle\Behat\WebContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class InstallerContext extends WebContext
{
    /**
     * @Given /^I am on the installer step (?P<step>\d)?$/
     * @When /^I go to the installer step (?P<step>\d)?$/
     */
    public function iAmOnTheInstallerStep($step)
    {
        $this->getSession()->visit($this->generatePageUrl('installer_step', array('step' => $step-1, 'type' => 'install')));
    }

    /**
     * @Then /^I should be on the installer step (?P<step>\d)$/
     */
    public function iShouldBeOnTheInstallerStep($step)
    {
        $this->assertSession()->addressEquals($this->generatePageUrl('installer_step', array('step' => $step-1, 'type' => 'install')));
        $this->assertSession()->statusCodeEquals(200);
    }

    public function purgeDatabase(BeforeScenarioScope $scope)
    {
        // We don't want to purge the database as the installer will
        // create it
    }
}
