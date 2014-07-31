@groups_admin
Feature: Groups management
  In order to manage groups
  As a panel user
  I want to be able to act on groups

  Background:
    Given there are following groups:
      | name   | roles                                  |
      | Admin  | ROLE_ADMIN                             |
      | Team 1 | ROLE_DP_GAME_ADMIN, ROLE_DP_VOIP_ADMIN |
    Given there are following users:
      | username | email       | password | groups | enabled |
      | foo      | foo@bar.net | test1234 | Admin  | yes     |
      | baz      | baz@bar.net | test1234 |        | yes     |

  Scenario: Seeing index of all groups
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Gestion des groupes"
     Then I should be on the group index page
      And I should see 2 groups in the list
