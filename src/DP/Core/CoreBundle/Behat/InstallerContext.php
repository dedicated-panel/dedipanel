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

use DP\Core\CoreBundle\Behat\DefaultContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;

class InstallerContext extends DefaultContext
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

    /**
     * @Given /^I am on the installer final step$/
     * @When /^I go to the installer final step$/
     */
    public function iAmOnTheInstallerFinalStep()
    {
        $this->getSession()->visit($this->generatePageUrl('installer_final_step', array('type' => 'install')));
    }

    /**
     * @Then /^I should be on the installer final step$/
     */
    public function iShouldBeOnTheInstallerFinalStep()
    {
        $this->assertSession()->addressEquals($this->generatePageUrl('installer_final_step', array('type' => 'install')));
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Given /^I am on the installer check step$/
     */
    public function iAmOnInstallerCheckPage()
    {
        $this->getSession()->visit($this->generatePageUrl('installer_check', array('type' => 'install')));
    }

    /**
     * @Then /^I should be on the installer check step$/
     */
    public function iShouldBeOnInstallerCheckPage()
    {
        $this->assertSession()->addressEquals($this->generatePageUrl('installer_check', array('type' => 'install')));
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Given /^I should not see bad requirements$/
     */
    public function iShouldNotSeeBadRequirements()
    {
        $this->assertSession()->elementsCount('css', 'ul#requirements li.bad', 0);
    }

    /**
     * @Given /^The database need to be empty$/
     */
    public function databaseNeedToBeEmpty()
    {
        if ($this->countTables() != 0) {
            $em = $this->getService('doctrine.orm.entity_manager');

            $schemaTool = new SchemaTool($em);
            $metadatas  = $em->getMetadataFactory()->getAllMetadata();
            $schemaTool->dropSchema($metadatas);

            if ($this->countTables() != 0) {
                throw new \RuntimeException('Old tables are still in database.');
            }
        }
    }

    /**
     * @Then /^The database should be populated$/
     */
    public function databaseShouldBePopulated()
    {
        if (0 === $this->countTables()) {
            throw new Exception('The database should not be empty.');
        }
    }

    private function countTables()
    {
        return count($this
            ->getService('doctrine.orm.entity_manager')
            ->getConnection()
            ->executeQuery('SHOW TABLES')
            ->fetchAll());
    }

    protected function doPurgeDatabase()
    {
        // We don't wan't to call the default doPurgeDatabase()
    }
}
