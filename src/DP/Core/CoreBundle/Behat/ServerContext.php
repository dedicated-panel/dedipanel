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

class ServerContext extends DefaultContext
{
    /**
     * @Given /^I am on the (teamspeak) instance creation page for "([^""]*)"$/
     * @When /^I go to the (teamspeak) instance creation page for "([^""]*)"$/
     */
    public function iAmOnTheVoipPage($type, $value)
    {
        $parts   = explode('@', $value);
        $machine = $this->findOneBy('machine', array('username' => $parts[0]));
        $server  = $this->findOneBy($type, array('machine' => $machine->getId()));

        $this->getSession()->visit($this->generatePageUrl($type . ' instance creation', array('serverId' => $server->getId())));
    }

    /**
     * @Given /^I should be on the (teamspeak) instance creation page for "([^""]*)"$/
     * @Given /^I should still be on the (teamspeak) instance creation page for "([^""]*)"$/
     */
    public function iShouldBeOnTheVoipPage($type, $value)
    {
        $parts   = explode('@', $value);
        $machine = $this->findOneBy('machine', array('username' => $parts[0]));
        $server  = $this->findOneBy($type, array('machine' => $machine->getId()));

        $this->assertSession()->addressEquals($this->generatePageUrl($type . ' instance creation', array('serverId' => $server->getId())));
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @Given /^I am on the page of (teamspeak) instance "([^""]*)"$/
     * @Given /^I go to the page of (teamspeak) instance "([^""]*)"$/
     */
    public function iAmOnTheInstancePageByName($type, $name)
    {
        $resource = $this->findOneBy('instance', array('name' => $name), $type);

        $this->getSession()->visit($this->generatePageUrl(sprintf('%s_instance_show', $type), array('id' => $resource->getId(), 'serverId' => $resource->getServer()->getId())));
    }

    /**
     * @Given /^I am (building|viewing|editing) (teamspeak) instance "([^""]*)"$/
     */
    public function iAmDoingSomethingWithVoipResourceByName($action, $type, $name)
    {
        $action = str_replace(array_keys($this->actions), array_values($this->actions), $action);
        $resource = $this->findOneBy('instance', array('name' => $name), $type);

        $this->getSession()->visit($this->generatePageUrl(sprintf('%s_instance_%s', $type, $action), array('id' => $resource->getId(), 'serverId' => $resource->getServer()->getId())));
    }

    /**
     * @Then /^I should be (building|viewing|editing) (teamspeak) instance "([^""]*)"$/
     * @Then /^I should still be (building|viewing|editing) (teamspeak) instance "([^""]*)"$/
     */
    public function iShouldBeDoingSomethingWithVoipResourceByName($action, $type, $name)
    {
        $action = str_replace(array_keys($this->actions), array_values($this->actions), $action);
        $resource = $this->findOneBy('instance', array('name' => $name), $type);

        $this->assertSession()->addressEquals($this->generatePageUrl(sprintf('%s_instance_%s', $type, $action), array('id' => $resource->getId(), 'serverId' => $resource->getServer()->getId())));
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @Then /^I should be on the page of ([^""(w)]*) server "([^""]*)"$/
     * @Then /^I should still be on the page of ([^""(w)]*) server "([^""]*)"$/
     */
    public function iShouldBeOnTheServerPageByName($type, $name)
    {
        $this->iShouldBeOnTheResourcePage($type, 'name', $name);
    }

    /**
     * @Then /^I should be on the page of (teamspeak) instance "([^""]*)"$/
     * @Then /^I should still be on the page of (teamspeak) instance "([^""]*)"$/
     */
    public function iShouldBeOnTheInstancePageByName($type, $name)
    {
        $type = str_replace(' ', '_', $type);
        $resource = $this->findOneBy('instance', array('name' => $name), $type);

        $this->assertSession()->addressEquals($this->generatePageUrl(sprintf('%s_instance_show', $type), array('id' => $resource->getId(), 'serverId' => $resource->getServer()->getId())));
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @Given /^I am on the (teamspeak) "([^""]*)" instance index$/
     * @TODO: Refactorer comme iAmOnTheVoipPage()
     */
    public function iAmOnTheInstanceIndex($type, $value)
    {
        $parts   = explode('@', $value);
        $machine = $this->findOneBy('machine', array('username' => $parts[0]));
        $server  = $this->findOneBy($type, array('machine' => $machine->getId()));

        $this->getSession()->visit($this->generatePageUrl(sprintf('dedipanel_%s_instance_index', $type), array('serverId' => $server->getId())));
    }

    /**
     * @Then /^I should be on the (teamspeak) "([^"]*)" instance index$/
     */
    public function iShouldBeOnTheInstanceIndexPage($type, $value)
    {
        $parts   = explode('@', $value);
        $machine = $this->findOneBy('machine', array('username' => $parts[0]));
        $server  = $this->findOneBy($type, array('machine' => $machine->getId()));

        $this->assertSession()->addressEquals($this->generatePageUrl(sprintf('dedipanel_%s_instance_index', $type), array('serverId' => $server->getId())));
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @Given /^I am (building|viewing|editing) (teamspeak) "([^""]*)"$/
     */
    public function iAmDoingSomethingWithVoipServer($action, $type, $value)
    {
        $type = str_replace(' ', '_', $type);

        $action = str_replace(array_keys($this->actions), array_values($this->actions), $action);
        $parts   = explode('@', $value);
        $machine = $this->findOneBy('machine', array('username' => $parts[0]));
        $server  = $this->findOneBy($type, array('machine' => $machine->getId()));

        $this->getSession()->visit($this->generatePageUrl(sprintf('dedipanel_%s_%s', $type, $action), array('id' => $server->getId())));
    }

    /**
     * @Then /^I should be (building|viewing|editing|testing) (teamspeak) "([^""]*)"$/
     */
    public function iShouldBeDoingSomethingWithVoipServer($action, $type, $value)
    {
        $type = str_replace(' ', '_', $type);

        $action  = str_replace(array_keys($this->actions), array_values($this->actions), $action);
        $parts   = explode('@', $value);
        $machine = $this->findOneBy('machine', array('username' => $parts[0]));
        $server  = $this->findOneBy($type, array('machine' => $machine->getId()));

        $this->assertSession()->addressEquals($this->generatePageUrl(sprintf('dedipanel_%s_%s', $type, $action), array('id' => $server->getId())));
        $this->assertStatusCodeEquals(200);
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
     * @Then /^I should be on the ftp page of ([^""]*) "([^""]*)"$/
     */
    public function iShouldBeOnTheFtpPage($type, $name)
    {
        $type = str_replace(' ', '_', $type);
        $resource = $this->findOneBy($type, array('name' => $name));

        $this->assertSession()->addressEquals($this->generatePageUrl(sprintf('%s_ftp_show', $type), array('id' => $resource->getId())));
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @Then /^I should see (\d+) ([^" ]*) instances in (that|the) list$/
     */
    public function iShouldSeeThatMuchInstancesInTheList($amount, $type)
    {
        if (1 === count($this->getSession()->getPage()->findAll('css', '.instance-list'))) {
            $this->assertSession()->elementsCount('css', '.instance-list > .instance-item', $amount);
        } else {
            $this->assertSession()->elementsCount('css', sprintf('#%s.instance-list > .instance-item', $type), $amount);
        }
    }
}
