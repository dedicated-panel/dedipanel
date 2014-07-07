@users
Feature: Sign in to the panel
  In order to manage my servers
  As a visitor
  I need to be able to log in to the panel

  Background:
    Given there are following users:
      | username | email       | password | enabled |
      | foo      | foo@bar.net | test1234 | yes     |

  Scenario: Log in with username and password
    Given I am on "/login"
     When I fill in the following:
        | Nom d'utilisateur | foo      |
        | Mot de passe      | test1234 |
      And I press "Connexion"
     Then I should be on the homepage
      And I should see "foo"

  Scenario: Log in with bad credentials
    Given I am on "/login"
    When I fill in the following:
      | Nom d'utilisateur | foo |
      | Mot de passe      | aze |
    And I press "Connexion"
   Then I should be on "/login"
    And I should see "Nom d'utilisateur et/ou mot de passe incorrect."

  Scenario: Log in without credentials
    Given I am on "/login"
    When I press "Connexion"
    Then I should be on "/login"
    And I should see "Nom d'utilisateur et/ou mot de passe incorrect."
