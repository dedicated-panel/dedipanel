@admin
Feature: Users management
  In order to manage users
  As a panel admin
  I want to be able to act on users

  Background:
    Given there are following users:
      | username | email       | password | group | role             | enabled |
      | foo      | foo@foo.net | test1234 |       | ROLE_SUPER_ADMIN | yes     |
      | baz      | baz@baz.net | test1234 | Team  | ROLE_ADMIN       | yes     |
      | boz      | boz@boz.net | test1234 | Team  | ROLE_ADMIN       | yes     |
      | bar      | bar@bar.net | test1234 | Team  |                  | yes     |

  Scenario: Seeing index of all users when super admin
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Gestion des utilisateurs"
     Then I should be on the user index page
      And I should see 4 users in the list

  Scenario: Seeing index of all users when admin
    Given I am logged in with baz account
      And I am on the homepage
     When I follow "Gestion des utilisateurs"
     Then I should be on the user index page
      And I should see 3 users in the list

  Scenario: Accessing the user details page from the list
    Given I am logged in with foo account
      And I am on the user index page
     When I click "Voir" near "baz"
     Then I should be on the page of user with username "baz"

  Scenario: Accessing the user creation form
    Given I am logged in with foo account
      And I am on the user index page
     When I follow "Ajouter un utilisateur"
     Then I should be on the user creation page

  Scenario: Submitting empty form
    Given I am logged in with foo account
      And I am on the user creation page
     When I press "Créer"
     Then I should still be on the user creation page
      And I should see 4 validation errors

  Scenario: Creating user without group
    Given I am logged in with foo account
      And I am on the user creation page
     When I fill in dedipanel_user form with:
      | username              | aze                     |
      | email                 | aze@dedicated-panel.net |
      | plainPassword][first  | test1234                |
      | plainPassword][second | test1234                |
      | enabled               | yes                     |
     When I press "Créer"
     Then I should still be on the user creation page
      And I should see 1 validation errors

  Scenario: Creating user with group
    Given I am logged in with foo account
      And I am on the user creation page
     When I fill in dedipanel_user form with:
      | username              | aze                     |
      | email                 | aze@dedicated-panel.net |
      | plainPassword][first  | test1234                |
      | plainPassword][second | test1234                |
      | group                 | Team                    |
      | enabled               | yes                     |
      And I press "Créer"
     Then I should be on the page of user with username "aze"
      And I should see 1 alert success message
      And I should see "L'utilisateur a bien été créé."

  Scenario: Creating super admin with group
    Given I am logged in with foo account
      And I am on the user creation page
     When I fill in dedipanel_user form with:
      | username              | aze                     |
      | email                 | aze@dedicated-panel.net |
      | plainPassword][first  | test1234                |
      | plainPassword][second | test1234                |
      | enabled               | yes                     |
      | group                 | Team                    |
      | superAdmin            | yes                     |
     When I press "Créer"
     Then I should still be on the user creation page
      And I should see 1 validation errors

  Scenario: Creating super admin without group
    Given I am logged in with foo account
      And I am on the user creation page
     When I fill in dedipanel_user form with:
      | username              | aze                     |
      | email                 | aze@dedicated-panel.net |
      | plainPassword][first  | test1234                |
      | plainPassword][second | test1234                |
      | enabled               | yes                     |
      | superAdmin            | yes                     |
      And I press "Créer"
     Then I should be on the page of user with username "aze"
      And I should see 1 alert success message
      And I should see "L'utilisateur a bien été créé."

  Scenario: Accessing the user editing form
    Given I am logged in with foo account
      And I am on the page of user with username "baz"
     When I follow "Modifier"
     Then I should be editing user with username "baz"

  Scenario: Accessing the user editing form from the list
    Given I am logged in with foo account
      And I am on the user index page
     When I click "Modifier" near "baz"
     Then I should be editing user with username "baz"

  Scenario: Trying to access editing form for himself
    Given I am logged in with baz account
     When I am editing user with username "baz"
     Then access should be forbidden

  Scenario: Updating the user
    Given I am logged in with baz account
      And I am editing user with username "boz"
     When I fill in "Nom" with "biz"
      And I press "Mettre à jour"
     Then I should be on the page of user with username "biz"
      And I should see 1 alert success message
      And I should see "L'utilisateur a bien été mis à jour."

  Scenario: Promote an admin
    Given I am logged in with baz account
      And I am editing user with username "bar"
     When I fill in dedipanel_user form with:
      | admin | yes |
      And I press "Mettre à jour"
     Then I should be on the page of user with username "bar"
      And I should see 1 alert success message
      And I should see "L'utilisateur a bien été mis à jour."
      And I should see "Oui" near "Admin ?"

  Scenario: Demote an admin
    Given I am logged in with baz account
      And I am editing user with username "bar"
     When I fill in dedipanel_user form with:
      | admin | no |
      And I press "Mettre à jour"
     Then I should be on the page of user with username "bar"
      And I should see 1 alert success message
      And I should see "L'utilisateur a bien été mis à jour."
      And I should see "Non" near "Admin ?"

  Scenario: Deleting user
    Given I am logged in with foo account
      And I am on the page of user with username "baz"
     When I press "Supprimer"
     Then I should be on the user index page
      And I should see 1 alert success message
      And I should see "L'utilisateur a bien été supprimé."
