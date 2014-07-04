<?php

namespace DP\Core\DistributionBundle\Behat;

use DP\Core\CoreBundle\Behat\WebContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;

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
        }
    }

    /**
     * @Given /^The database should be empty$/
     */
    public function databaseShouldBeEmpty()
    {
        if ($this->countTables() != 0) {
            throw new \RuntimeException('The database need to be flush.');
        }
    }

    /**
     * @Given /^The (?P<table>.+) table should be empty$/
     */
    public function tableShouldBeEmpty($table)
    {
        return $this
            ->getService('doctrine.orm.entity_manager')
            ->getConnection()
            ->executeQuery('SELECT COUNT(*) FROM ' . $table)
            ->fetchColumn() == 0;
    }

    /**
     * @Given /^The (?P<table>.+) table should (?P<neg>not) be empty$/
     */
    public function tableShouldNotBeEmpty($table, $neg = '')
    {
        return $this
            ->getService('doctrine.orm.entity_manager')
            ->getConnection()
            ->executeQuery('SELECT COUNT(*) FROM ' . $table)
            ->fetchColumn() != 0;
    }

    private function countTables()
    {
        return count($this
            ->getService('doctrine.orm.entity_manager')
            ->getConnection()
            ->executeQuery('SHOW TABLES')
            ->fetchAll());
    }
}
