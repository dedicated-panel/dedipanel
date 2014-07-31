@configs
Feature: Core settings
  In order to configure my panel
  As a panel admin
  I want to be able to edit core configuration

  Background:
    Given there are following users:
      | username | email       | password | role             | enabled |
      | foo      | foo@bar.net | test1234 | ROLE_SUPER_ADMIN | yes     |
      | baz      | baz@bar.net | test1234 | ROLE_ADMIN       | yes     |

  Scenario: Accessing the settings form
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Configuration"
     Then I should be on the core config page

  Scenario: Accessing the settings form without access rights
    Given I am logged in with baz account
      And I am on the homepage
      And I should not see "Configuration"
     When I go to the core config page
     Then I should be unauthorized on the core config page
      And I should see 1 alert error message
      And I should see "Vous n'avez pas accès à cette page."

  Scenario: Saving the configuration
    Given I am logged in with foo account
      And I am on the core config page
     When I select "Non" from "Debug mode"
      And I press "Mettre à jour"
     Then I should be on the core config page
      And I should see 1 alert success message
      And I should see "Mise à jour du fichier de configuration réussie."
