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

use Sylius\Bundle\ResourceBundle\Behat\DefaultContext as SyliusDefaultContext;
use Behat\Gherkin\Node\TableNode;

class DefaultContext extends SyliusDefaultContext
{
    /**
     * @var array
     */
    protected $actions = array(
        'viewing'  => 'show',
        'creation' => 'create',
        'editing'  => 'update',
        'building' => 'build',
        'testing'  => 'connection_test',
    );

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

    /**
     * @param string $baseName
     */
    protected function getRepository($resource, $baseName = null)
    {
        $service = 'dedipanel.';

        if (!empty($baseName)) {
            $service .= $baseName . '.';
        }

        return $this->getService($service . 'repository.'.$resource);
    }

    protected function findOneBy($type, array $criteria, $repoPrefix = '')
    {
        $resource = $this
            ->getRepository($type, $repoPrefix)
            ->findOneBy($criteria);

        if (null === $resource) {
            throw new \InvalidArgumentException(
                sprintf('%s for criteria "%s" was not found.', str_replace('_', ' ', ucfirst($type)), serialize($criteria))
            );
        }

        return $resource;
    }

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
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @Then /^access should be forbidden$/
     */
    public function accessShouldBeForbidden()
    {
        $this->assertStatusCodeEquals(403);
        $this->iShouldSeeAlertMessage(1, 'error');
        $this->assertSession()->pageTextContains("Vous n'avez pas accès à cette page.");
    }

    /**
     * @Then /^I should be unauthorized on the (.+) (page)$/
     */
    public function iShouldBeUnauthorizedOnThePage($page)
    {
        $this->assertSession()->addressEquals($this->generatePageUrl($page));
        $this->assertStatusCodeEquals(403);
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
     * @Then /^I should see (\d+) validation errors?$/
     */
    public function iShouldSeeFieldsOnError($amount)
    {
        $this->iShouldSeeAlertMessage($amount, 'error');
    }

    /**
     * @Then /^I should see (\d+) (?:alert )?((error|success) )?message$/
     */
    public function iShouldSeeAlertMessage($amount, $type = '')
    {
        $class = '.alert' . (!empty($type) ? '-' . $type : '');

        $this->assertSession()->elementsCount('css', $class . ' > ul > li', $amount);
    }

    /**
     * @Given /^I am logged in with (.*) account$/
     */
    public function iAmLoggedInWithAccount($username)
    {
        $password = 'test1234';

        if (isset($this->users[$username])) {
            $password = $this->users[$username];
        }

        $this->getSession()->visit($this->generatePageUrl('fos_user_security_login'));

        $this->fillField("Nom d'utilisateur", $username);
        $this->fillField('Mot de passe', $password);
        $this->pressButton('Connexion');
    }

    /**
     * @When /^I fill in (.+) form with:$/
     */
    public function whenIFillInFormWith($base, TableNode $table)
    {
        $page = $this->getSession()->getPage();

        foreach ($table->getTable() AS $item) {
            list($name,$value) = $item;
            $field     = $this->findField($base, $name);
            $fieldName = $field->getAttribute('name');

            if ($name == 'machine' || $name == 'game') {
                $key = ($name == 'machine' ? 'username' : 'name');
                $entity = $this->findOneBy($name, array($key => $value));
                $value = $entity->getId();
            }

            if ($field->getTagName() == 'select') {
                $this->selectOption($fieldName, $value);
            }
            elseif ($field->getAttribute('type') == 'checkbox') {
                $this->fillCheckbox($fieldName, $value);
            }
            elseif ($field->getAttribute('type') == 'radio') {
                $this->fillRadio($fieldName, $value);
            }
            else {
                $this->fillField($fieldName, $value);
            }
        }
    }

    public function fillCheckbox($fieldName, $value)
    {
        $page = $this->getSession()->getPage();

        if ($value == 'yes') {
            $page->checkField($fieldName);
        }
        elseif ($value == 'no') {
            $page->uncheckField($fieldName);
        }
        else {
            throw new \RuntimeException(sprintf('Unsupported value "%s" for the checkbox field "%s"', $value, $fieldName));
        }
    }

    public function fillRadio($fieldName, $value)
    {
        if ($value == 'yes') {
            $value = 1;
        }
        elseif ($value == 'no') {
            $value = 0;
        }

        $this->fillField($fieldName, $value);
    }

    /**
     * @Then /^I should see [\w\s]+ with [\w\s]+ "([^""]*)" in (that|the) list$/
     */
    public function iShouldSeeResourceWithValueInThatList($value)
    {
        $this->assertSession()->elementTextContains('css', 'table', $value);
    }

    /**
     * @Then /^I should not see [\w\s]+ with [\w\s]+ "([^""]*)" in (that|the) list$/
     */
    public function iShouldNotSeeResourceWithValueInThatList($value)
    {
        $this->assertSession()->elementTextNotContains('css', 'table', $value);
    }

    /**
     * @Then /^I should see (\d+) ([^"" ]*) in (that|the) list$/
     */
    public function iShouldSeeThatMuchResourcesInTheList($amount, $type)
    {
        if (1 === count($this->getSession()->getPage()->findAll('css', 'table'))) {
            $this->assertSession()->elementsCount('css', 'table tbody > tr', $amount);
        } else {
            $this->assertSession()->elementsCount('css', sprintf('table#%s tbody > tr', str_replace(' ', '-', $type)), $amount);
        }
    }

    /**
     * @Then /^I should be on the page of ([^""]*) with ([^""]*) "([^""]*)"$/
     * @Then /^I should still be on the page of ([^""]*) with ([^""]*) "([^""]*)"$/
     */
    public function iShouldBeOnTheResourcePage($type, $property, $value)
    {
        $type = str_replace(' ', '_', $type);
        $resource = $this->findOneBy($type, array($property => $value));

        $this->assertSession()->addressEquals($this->generatePageUrl(sprintf('%s_show', $type), array('id' => $resource->getId())));
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @Given /^I am on the page of ([^""]*) with ([^""]*) "([^""]*)"$/
     * @Given /^I go to the page of ([^""]*) with ([^""]*) "([^""]*)"$/
     */
    public function iAmOnTheResourcePage($type, $property, $value)
    {
        $type = str_replace(' ', '_', $type);

        $resource = $this->findOneBy($type, array($property => $value));

        $this->getSession()->visit($this->generatePageUrl(sprintf('%s_show', $type), array('id' => $resource->getId())));
    }

    /**
     * @Given /^I am on the page of (?!teamspeak)([^""(w)]*) "([^""]*)"$/
     * @Given /^I go to the page of (?!teamspeak)([^""(w)]*) "([^""]*)"$/
     */
    public function iAmOnTheResourcePageByName($type, $name)
    {
        $this->iAmOnTheResourcePage($type, 'name', $name);
    }

    /**
     * @Given /^I am (building|viewing|editing) ([^""]*) with ([^""]*) "([^""]*)"$/
     */
    public function iAmDoingSomethingWithResource($action, $type, $property, $value)
    {
        $type = str_replace(' ', '_', $type);

        $action = str_replace(array_keys($this->actions), array_values($this->actions), $action);
        $resource = $this->findOneBy($type, array($property => $value));

        $this->getSession()->visit($this->generatePageUrl(sprintf('%s_%s', $type, $action), array('id' => $resource->getId())));
    }

    /**
     * @Given /^I am (building|viewing|editing) (?!teamspeak)([^""(w)]*) "([^""]*)"$/
     */
    public function iAmDoingSomethingWithResourceByName($action, $type, $name)
    {
        $this->iAmDoingSomethingWithResource($action, $type, 'name', $name);
    }

    /**
     * @Then /^I should be (building|viewing|editing|testing) ([^"]*) with ([^"]*) "([^""]*)"$/
     */
    public function iShouldBeDoingSomethingWithResource($action, $type, $property, $value)
    {
        $type = str_replace(' ', '_', $type);

        $action = str_replace(array_keys($this->actions), array_values($this->actions), $action);
        $resource = $this->findOneBy($type, array($property => $value));

        $this->assertSession()->addressEquals($this->generatePageUrl(sprintf('dedipanel_%s_%s', $type, $action), array('id' => $resource->getId())));
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @Then /^I should be (building|viewing|editing) (?!teamspeak)([^""(w)]*) "([^""]*)"$/
     */
    public function iShouldBeDoingSomethingWithResourceByName($action, $type, $name)
    {
        $this->iShouldBeDoingSomethingWithResource($action, $type, 'name', $name);
    }

    /**
     * Assert that given code equals the current one.
     *
     * @param integer $code
     */
    protected function assertStatusCodeEquals($code)
    {
        $this->assertSession()->statusCodeEquals($code);
    }

    /**
     * @Then /^I should see (\d+) associated games?$/
     */
    public function iShouldSeeAssociatedGames($amount)
    {
        $this->assertSession()->elementsCount('css', 'ul.associated-games > li', $amount);
    }

    /**
     * @Then /^I should see (\d+) buttons? "([^"]+)"$/
     */
    public function iShouldSeeButton($count, $value)
    {
        $locator = sprintf('a:contains("%s"), button:contains("%s")', $value, $value);

        $this->assertSession()->elementsCount('css', $locator, $count);
    }

    /**
     * @Then /^I should not see button "([^"]+)"$/
     */
    public function iShouldNotSeeButton($value)
    {
        $locator = sprintf('a:contains("%s"), button:contains("%s")', $value, $value);

        $this->assertSession()->elementsCount('css', $locator, 0);
    }

    /**
     * @Then /^I should be on 403 page$/
     * @Then /^I should be on 403 page with "([^"]+)"$/
     */
    public function iShouldBeOn403($message = "Vous n'avez pas accès à cette page.")
    {
        $this->assertStatusCodeEquals(403);

        $this->iShouldSeeAlertMessage(1, 'error');
        $this->assertSession()->pageTextContains($message);
    }

    /**
     * @Then /^I should see (\d+) "([^"]+)" checkbox(?:es)? in "([^"]+)" form$/
     */
    public function iShouldSeeCheckboxes($count, $type, $form)
    {
        $locator = sprintf('//input[@type="checkbox"][@name="%s[%s][]"]', $form, $type);

        $this->assertSession()->elementscount('xpath', $locator, $count);
    }

    /**
     * @Then /^I should see (\d+) "([^"]+)" options in "([^"]+)" form$/
     */
    public function iShouldSeeOptionsInSelect($count, $type, $form)
    {
        $xpath   = sprintf('%s[%s]', $form, $type);
        $locator = sprintf('//select[@name="%s" or @name="%s[]"]/option', $xpath, $xpath);

        $this->assertSession()->elementsCount('xpath', $locator, $count);
    }

    public function findField($form, $fieldName)
    {
        $page = $this->getSession()->getPage();
        $fieldName = sprintf('%s[%s]', $form, $fieldName);

        if ((null === $field = $page->findField($fieldName)) && (null === $field = $page->findField($fieldName . '[]'))) {
            throw new \RuntimeException(sprintf('Form field with id|name|label|value "%s" or "%s[]" not found.', $fieldName, $fieldName));
        }

        return $field;
    }

    /**
     * @Then /^I should see (\d+) ([^" ]*) servers in (that|the) list$/
     */
    public function iShouldSeeThatMuchServersInTheList($amount, $type)
    {
        if (1 === count($this->getSession()->getPage()->findAll('css', '.server-list'))) {
            $this->assertSession()->elementsCount('css', '.server-list > .server-item', $amount);
        } else {
            $this->assertSession()->elementsCount('css', sprintf('#%s.server-list > .server-item', $type), $amount);
        }
    }
}
