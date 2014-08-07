@groups_admin
Feature: Groups management
  In order to manage groups
  As a panel owner
  I want to be able to act on groups

  Background:
    Given there are following groups:
      | name   | roles                    | parent |
      | Team   | ROLE_DP_GAME_STEAM_ADMIN |        |
      | Team 2 |                          | Team   |
      | Team 4 |                          | Team 3 |
    Given there are following users:
      | username | email       | password | group  | role             | enabled |
      | foo      | foo@foo.net | test1234 |        | ROLE_SUPER_ADMIN | yes     |
      | baz      | baz@baz.net | test1234 | Team   | ROLE_ADMIN       | yes     |
      | bar      | bar@bar.net | test1234 | Team 4 |                  | yes     |

  Scenario: Seeing index of all groups when super admin
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Gestion des groupes"
     Then I should be on the group index page
      And I should see 4 groups in the list
      And I should see 4 buttons "Voir"
      And I should see 4 buttons "Modifier"

  Scenario: Seeing index of all groups when admin
    Given I am logged in with baz account
      And I am on the homepage
     When I follow "Gestion des groupes"
     Then I should be on the group index page
      And I should see 2 groups in the list
      And I should see 2 button "Voir"
      And I should see 1 button "Modifier"

  Scenario: Seeing group data when super admin
    Given I am logged in with foo account
      And I am on the group index page
     When I follow "Voir" near "Team"
     Then I should be viewing group "Team"
      And I should see 1 button "Modifier"
      And I should see 1 button "Supprimer"

  Scenario: Seeing own group data when admin
    Given I am logged in with baz account
      And I am on the group index page
     When I follow "Voir" near "Team"
     Then I should be viewing group "Team"
      And I should not see button "Modifier"
      And I should not see button "Supprimer"

  Scenario: Seeing subgroup data when admin
    Given I am logged in with baz account
      And I am on the group index page
     When I follow "Voir" near "Team 2"
     Then I should be viewing group "Team 2"
      And I should see 1 button "Modifier"
      And I should see 1 button "Supprimer"

  Scenario: Accessing the group creation form when super admin
    Given I am logged in with foo account
      And I am on the group index page
     When I follow "Ajouter un groupe"
     Then I should be on the group creation page
      And I should see 5 "parent" options in "dedipanel_group" form
      And I should see 4 "roles" checkboxes in "dedipanel_group" form

  Scenario: Accessing the group creation form when admin
    Given I am logged in with baz account
      And I am on the group index page
     When I follow "Ajouter un groupe"
     Then I should be on the group creation page
      And I should see 2 "parent" options in "dedipanel_group" form
      And I should see 1 "roles" checkbox in "dedipanel_group" form

  Scenario: Submitting empty form
    Given I am logged in with foo account
      And I am on the group creation page
     When I press "Créer"
     Then I should still be on the group creation page
      And I should see 1 validation error

  Scenario: Creating group when super admin
      Given I am logged in with foo account
        And I am on the group creation page
       When I fill in dedipanel_group form with:
         | name     | Team 5 |
        And I press "Créer"
       Then I should be on the page of group "Team 5"
        And I should see 1 success message
        And I should see "Le groupe a bien été créé."

  Scenario: Creating subgroup of assigned group
    Given I am logged in with baz account
      And I am on the group creation page
     When I fill in dedipanel_group form with:
        | name     | Team 5  |
        | parent   | Team    |
      And I press "Créer"
     Then I should be on the page of group "Team 5"
      And I should see 1 success message
      And I should see "Le groupe a bien été créé."

  Scenario: Accessing the group editing form
    Given I am logged in with foo account
      And I am on the page of group "Team 2"
     When I follow "Modifier"
     Then I should be editing group "Team 2"

  Scenario: Accessing the group editing form from the list
    Given I am logged in with foo account
      And I am on the group index page
     When I click "Modifier" near "Team 2"
     Then I should be editing group "Team 2"

  Scenario: Admin accessing assigned group editing form
    Given I am logged in with baz account
     When I am editing group "Team"
     Then I should be on 403 page

  Scenario: Updating the group
    Given I am logged in with foo account
      And I am editing group "Team 2"
     When I fill in "Nom" with "Test"
      And I press "Mettre à jour"
     Then I should be on the page of group "Test"
      And I should see 1 success message
      And I should see "Le groupe a bien été mis à jour."

  Scenario: Deleting subgroup when superadmin
    Given I am logged in with foo account
      And I am on the page of group "Team 2"
     When I press "Supprimer"
     Then I should be on the group index page
      And I should see 1 success message
      And I should see "Le groupe a bien été supprimé."

  Scenario: Deleting group in use when super admin
    Given I am logged in with foo account
      And I am on the page of group "Team 3"
     When I press "Supprimer"
     Then I should still be on the page of group "Team 3"
      And I should see 1 error message
      And I should see "Des utilisateurs sont encore assignés à ce groupe (ou à un de ses sous-groupes). Veuillez les assigner à un autre groupe avant de supprimer celui-ci."

  Scenario: Deleting subgroup when admin
    Given I am logged in with baz account
      And I am on the page of group "Team 2"
     When I press "Supprimer"
     Then I should be on the group index page
      And I should see 1 success message
      And I should see "Le groupe a bien été supprimé."
