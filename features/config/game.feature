@game
Feature: Game settings
  In order to configure my panel
  As a panel admin
  I want to be able to edit game config

  Background:
    Given there are following users:
      | username | email       | password | role                     | enabled |
      | foo      | foo@bar.net | test1234 | ROLE_DP_ADMIN_GAME_ADMIN | yes     |
      | baz      | baz@bar.net | test1234 | ROLE_USER                | yes     |
    And there are following games:
      | name           | installName | bin      | type  | available |
      | Counter-Strike | cstrike     | hlds_run | steam | yes       |
      | Day Of Defeat  | dod         | hlds_run | steam | yes       |

  Scenario: Seeing index of all games
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Gestion des jeux"
     Then I should be on the game index page
      And I should see 2 games in the list

  Scenario: Accessing the game creation form
    Given I am logged in with foo account
      And I am on the game index page
     When I follow "Ajouter un jeu"
     Then I should be on the game creation page

  Scenario: Submitting empty form
    Given I am logged in with foo account
      And I am on the game creation page
     When I press "Créer"
     Then I should still be on the game creation page
      And I should see 4 validation errors

  Scenario: Creating game
    Given I am logged in with foo account
      And I am on the game creation page
     When I fill in dedipanel_game form with:
      | name        | Counter-Strike: Global Offensive |
      | installName | csgo                             |
      | launchName  | csgo                             |
      | bin         | srcds_run                        |
      | source      | yes                              |
      | type        | steam                            |
      | available   | yes                              |
      And I press "Créer"
     Then I should be on the page of game "Counter-Strike: Global Offensive"
      And I should see 1 alert success message
      And I should see "Le jeu a bien été créé."

  Scenario: Accessing the game editing form
    Given I am logged in with foo account
      And I am on the page of game "Counter-Strike"
     When I follow "Modifier"
     Then I should be editing game "Counter-Strike"

  Scenario: Accessing the game editing form from the list
    Given I am logged in with foo account
      And I am on the game index page
     When I click "Modifier" near "Counter-Strike"
     Then I should be editing game "Counter-Strike"

  Scenario: Updating the game
    Given I am logged in with foo account
      And I am editing game "Counter-Strike"
     When I fill in "Nom" with "Counter-Strike: Condition Zéro"
      And I press "Mettre à jour"
     Then I should be on the page of game "Counter-Strike: Condition Zéro"
      And I should see 1 alert success message
      And I should see "Le jeu a bien été mis à jour."

  Scenario: Deleting game
    Given I am logged in with foo account
      And I am on the page of game "Counter-Strike"
     When I press "Supprimer"
     Then I should be on the game index page
      And I should see 1 alert success message
      And I should see "Le jeu a bien été supprimé."
