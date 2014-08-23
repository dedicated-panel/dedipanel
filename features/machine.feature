@machines
Feature: Machines management
  In order to manage machine
  As a panel user
  I want to be able to act on machines

  Background:
    Given there are following users:
      | username | email       | password | group | role             | enabled |
      | foo      | foo@bar.net | test1234 |       | ROLE_SUPER_ADMIN | yes     |
      | baz      | baz@bar.net | test1234 | Team1 | ROLE_ADMIN       | yes     |
      | bar      | bar@bar.net | test1234 | Team1 |                  | yes     |
    Given there are following machines:
      | privateIp | username | key     | group |
      | 127.0.0.1 | testing1 | id_rsa1 | Team1 |
      | 127.0.0.1 | testing2 | id_rsa2 | Team1 |
      | 127.0.0.1 | testing3 | id_rsa3 | Team2 |

  Scenario: Seeing index of all machines when super admin
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Gestion des machines"
     Then I should be on the machine index page
      And I should see 3 machine in the list

  Scenario: Seeing index of all machines when admin
    Given I am logged in with baz account
      And I am on the homepage
     When I follow "Gestion des machines"
     Then I should be on the machine index page
      And I should see 2 machine in the list

  Scenario: Accessing the machine details page from the list as admin
    Given I am logged in with baz account
      And I am on the machine index page
     When I click "Voir" near "testing1"
     Then I should be on the page of machine with username "testing1"

  Scenario: Accessing the machine creation form when super admin
    Given I am logged in with foo account
      And I am on the machine index page
     When I follow "Ajouter une machine"
     Then I should be on the machine creation page
      And I should see 2 "groups" options in "dedipanel_machine" form

  Scenario: Accessing the machine creation form when admin
    Given I am logged in with baz account
      And I am on the machine index page
     When I follow "Ajouter une machine"
     Then I should be on the machine creation page
      And I should see 1 "groups" options in "dedipanel_machine" form

  Scenario: Submitting empty form
    Given I am logged in with foo account
      And I am on the machine creation page
     When I press "Créer"
     Then I should still be on the machine creation page
      And I should see 3 validation errors

  Scenario: Adding a machine as super admin, without assigning a group
    Given I am logged in with foo account
      And I am on the machine creation page
     When I fill in dedipanel_machine form with:
      | ip       | 127.0.0.1 |
      | port     | 22        |
      | username | testing4  |
      | password | testing4  |
      And I press "Créer"
     Then I should be on the page of machine with username "testing4"
      And I should see 1 success message
      And I should see "La machine a bien été ajoutée."

  Scenario: Adding a machine as super admin
    Given I am logged in with foo account
      And I am on the machine creation page
     When I fill in dedipanel_machine form with:
      | ip       | 127.0.0.1 |
      | port     | 22        |
      | username | testing4  |
      | password | testing4  |
      | groups   | Team2     |
      And I press "Créer"
     Then I should be on the page of machine with username "testing4"
      And I should see 1 success message
      And I should see "La machine a bien été ajoutée."

  Scenario: Adding a machine as admin
    Given I am logged in with baz account
      And I am on the machine creation page
     When I fill in dedipanel_machine form with:
      | ip       | 127.0.0.1 |
      | port     | 22        |
      | username | testing4  |
      | password | testing4  |
      | groups   | Team1     |
      And I press "Créer"
     Then I should be on the page of machine with username "testing4"
      And I should see 1 success message
      And I should see "La machine a bien été ajoutée."

  Scenario: Accessing the machine editing form as admin
    Given I am logged in with baz account
      And I am on the page of machine with username "testing1"
     When I follow "Modifier"
     Then I should be editing machine with username "testing1"

  Scenario: Accessing the machine editing form from the list as admin
    Given I am logged in with baz account
      And I am on the machine index page
     When I click "Modifier" near "testing1"
     Then I should be editing machine with username "testing1"

  Scenario: Updating a machine as admin
    Given I am logged in with baz account
      And I am editing machine with username "testing1"
     When I fill in "Utilisateur" with "testing5"
      And I fill in "Mot de pass" with "testing5"
      And I press "Mettre à jour"
     Then I should be on the page of machine with username "testing5"
      And I should see 1 success message
      And I should see "La machine a bien été mis à jour."

  Scenario: Deleting a machine as admin
    Given I am logged in with baz account
      And I am on the page of machine with username "testing1"
     When I press "Supprimer"
     Then I should be on the machine index page
      And I should see 1 success message
      And I should see "La machine a bien été supprimé."

  Scenario: Testing machine connection as admin
    Given I am logged in with baz account
      And I am on the machine index page
     When I click "Tester" near "testing2"
     Then I should be testing machine with username "testing2"
      And I should see 1 success message
      And I should see "Test de connexion réussi."
