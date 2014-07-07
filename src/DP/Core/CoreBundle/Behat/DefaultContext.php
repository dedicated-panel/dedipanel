<?php

namespace DP\Core\CoreBundle\Behat;

use Sylius\Bundle\ResourceBundle\Behat\DefaultContext as BaseDefaultContext;
use Behat\Gherkin\Node\TableNode;

class DefaultContext extends BaseDefaultContext
{
    protected $users = [];

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

    protected function getRepository($resource)
    {
        return $this->getService('dedipanel.repository.'.$resource);
    }

    public function thereIsUser($username, $email, $password, $role = null, $enabled = true, $groups = array(), $flush = true)
    {
        if (null === $user = $this->getRepository('user')->findOneBy(array('username' => $username))) {
            /* @var $user UserInterface */
            $user = $this->getRepository('user')->createNew();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setEnabled($enabled);
            $user->setPlainPassword($password);

            if (null !== $role) {
                $user->addRole($role);
            }

            $this->getEntityManager()->persist($user);

            foreach ($groups as $groupName) {
                if ($group = $this->findOneByName('group', $groupName)) {
                    $user->addGroup($group);
                }
            }

            if ($flush) {
                $this->getEntityManager()->flush();
            }

            $this->users[$username] = $password;
        }

        return $user;
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
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Then /^I should be unauthorized on the (.+) (page)$/
     */
    public function iShouldBeUnauthorizedOnThePage($page)
    {
        $this->assertSession()->addressEquals($this->generatePageUrl($page));
        $this->assertSession()->statusCodeEquals(403);
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
     * @Then /^I should see (\d+) alert (.+)? message$/
     */
    public function iShouldSeeAlertMessage($amount, $type = '')
    {
        $class = '.alert' . (!empty($type) ? '-' . $type : '');

        $this->assertSession()->elementsCount('css', $class . ' > ul > li', $amount);
    }

    /**
     * @Given /^there are following users:$/
     */
    public function thereAreFollowingUsers(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsUser(
                $data['username'],
                $data['email'],
                $data['password'],
                isset($data['role']) ? $data['role'] : 'ROLE_USER',
                isset($data['enabled']) ? $data['enabled'] : true,
                isset($data['groups']) && !empty($data['groups']) ? explode(',', $data['groups']) : array(),
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^I am logged in with (.*) account$/
     */
    public function iAmLoggedInWithAccount($username)
    {
        if (!isset($this->users[$username])) {
            throw new \RuntimeException('Given user ("' . $username . '") does not exists.');
        }

        $this->getSession()->visit($this->generatePageUrl('fos_user_security_login'));

        $this->fillField("Nom d'utilisateur", $username);
        $this->fillField('Mot de passe', $this->users[$username]);
        $this->pressButton('Connexion');
    }
}
