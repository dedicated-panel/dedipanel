@steam
Feature: Steam server management
  In order to manage steam server
  As a panel user
  I want to be able to act on servers

  Background:
    Given there are following groups:
      | name      | parent | roles                    |
      | Team 1    |        | ROLE_DP_GAME_STEAM_ADMIN |
      | SubTeam 1 | Team 1 | ROLE_DP_GAME_STEAM_ADMIN |
      | Team 2    |        | ROLE_DP_GAME_STEAM_ADMIN |
      | Team 3    |        | ROLE_DP_GAME_STEAM_ADMIN |
    And there are following users:
      | username | email       | password | group     | role             | enabled |
      | foo      | foo@foo.net | test1234 |           | ROLE_SUPER_ADMIN | yes     |
      | baz      | baz@baz.net | test1234 | Team 1    | ROLE_ADMIN       | yes     |
      | biz      | biz@biz.net | test1234 | Team 1    |                  | yes     |
      | bar      | bar@bar.net | test1234 | Team 3    |                  | yes     |
      | boz      | boz@boz.net | test1234 | SubTeam 1 |                  | yes     |
    And there are following machines:
      | privateIp | username | key     | group     |
      | 127.0.0.1 | testing1 | id_rsa1 | Team 1    |
      | 127.0.0.1 | testing2 | id_rsa2 | SubTeam 1 |
      | 127.0.0.1 | testing3 | id_rsa3 | Team 2    |
      | 127.0.0.1 | bugged   |         | SubTeam 1 |
    And there are following games:
      | name           | installName | bin      | type  | available |
      | Counter-Strike | cstrike     | hlds_run | steam | yes       |
    And there are following steam servers:
      | name  | machine  | port  | rconPassword | game           | installDir | maxplayers | installed |
      | Test1 | testing1 | 27025 | test1        | Counter-Strike | test1      | 2          | yes       |
      | Test2 | testing2 | 27025 | test2        | Counter-Strike | testcs     | 2          | yes       |
      | Test3 | testing3 | 27025 | test3        | Counter-Strike | test3      | 2          | yes       |

  Scenario: Seeing index of all steam servers when super admin
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Serveurs Steam"
     Then I should be on the steam index page
      And I should see 3 steam servers in the list

  Scenario: Seeing index of all steam servers when admin
    Given I am logged in with baz account
      And I am on the homepage
     When I follow "Serveurs Steam"
     Then I should be on the steam index page
      And I should see 2 steam servers in the list

  Scenario: Accessing the steam server details page from the list as admin
    Given I am logged in with baz account
      And I am on the steam index page
     When I follow "Test1"
     Then I should be on the page of steam server "Test1"

  Scenario: Accessing the steam creation form as super admin
    Given I am logged in with foo account
      And I am on the steam index page
     When I follow "Ajouter un serveur Steam"
     Then I should be on the steam creation page
      And I should see 4 "machine" options in "dedipanel_steam" form

  Scenario: Accessing the steam creation form as team admin
    Given I am logged in with baz account
      And I am on the steam index page
     When I follow "Ajouter un serveur Steam"
     Then I should be on the steam creation page
      And I should see 3 "machine" options in "dedipanel_steam" form

  Scenario: Accessing the steam creation form as normal user
    Given I am logged in with biz account
      And I am on the steam index page
     When I follow "Ajouter un serveur Steam"
     Then I should be on the steam creation page
      And I should see 1 "machine" options in "dedipanel_steam" form

  Scenario: Accessirng the steam creation form as subteam user
    Given I am logged in with boz account
      And I am on the steam index page
     When I follow "Ajouter un serveur Steam"
     Then I should be on the steam creation page
      And I should see 2 "machine" options in "dedipanel_steam" form

  Scenario: Submitting empty form
    Given I am logged in with bar account
      And I am on the steam creation page
     When I press "Créer"
     Then I should still be on the steam creation page
      And I should see 6 validation errors

  Scenario: Adding a steam server
    Given I am logged in with boz account
      And I am on the steam creation page
     When I fill in dedipanel_steam form with:
      | machine      | testing2       |
      | name         | Test CS        |
      | port         | 27025          |
      | game         | Counter-Strike |
      | dir          | testcs         |
      | rconPassword | testcs         |
      | maxplayers   | 2              |
      And I press "Créer"
     Then I should be on the steam index page
      And I should see 1 success message
      And I should see "Le serveur steam a bien été ajouté."

  Scenario: Adding an existing steam server
    Given I am logged in with boz account
      And I am on the steam creation page
     When I fill in dedipanel_steam form with:
      | machine          | testing2       |
      | name             | Test CS        |
      | port             | 27025          |
      | game             | Counter-Strike |
      | dir              | testcs         |
      | rconPassword     | testcs         |
      | maxplayers       | 2              |
      | alreadyInstalled | yes            |
      And I press "Créer"
     Then I should be on the steam index page
      And I should see 1 success message
      And I should see "Le serveur steam a bien été ajouté."

  Scenario: Adding a steam server on a bugged machine
    Given I am logged in with boz account
      And I am on the steam creation page
     When I fill in dedipanel_steam form with:
      | machine          | bugged          |
      | name             | Test CS         |
      | port             | 27025           |
      | game             | Counter-Strike  |
      | dir              | testcs          |
      | rconPassword     | testcs          |
      | maxplayers       | 2               |
      And I press "Créer"
     Then I should still be on the steam creation page
      And I should see 1 error message
      And I should see "La machine sélectionnée est actuellement indisponible."

  Scenario: Adding a steam server on an existing directory
    Given I am logged in with boz account
      And I am on the steam creation page
     When I fill in dedipanel_steam form with:
      | machine          | testing2        |
      | name             | Test CS         |
      | port             | 27025           |
      | game             | Counter-Strike  |
      | dir              | testcs          |
      | rconPassword     | testcs          |
      | maxplayers       | 2               |
      And I press "Créer"
     Then I should still be on the steam creation page
      And I should see 1 error message
      And I should see "Le dossier d'installation existe déjà sur le serveur."

  Scenario: Accessing the steam editing form as admin
    Given I am logged in with baz account
      And I am on the page of steam "Test2"
     When I follow "Modifier"
     Then I should be editing steam "Test2"

  Scenario: Accessing the steam editing form from the list as admin
    Given I am logged in with baz account
      And I am on the steam index page
     When I click "Modifier" near "Test2"
     Then I should be editing steam "Test2"

  Scenario: Updating a steam server as admin
    Given I am logged in with baz account
      And I am editing steam "Test2"
     When I fill in "Nom" with "Test21"
      And I press "Mettre à jour"
     Then I should be on the page of steam with name "Test21"

  Scenario: Deleting a steam server as admin
    Given I am logged in with baz account
      And I am viewing steam "Test2"
     When I press "Supprimer"
     Then I should be on the steam index page
      And I should see 1 success message
      And I should see "Le serveur steam a bien été supprimé."

#  Scenario: Completly deleting a steam server as admin
#    Given I am logged in with baz account
#      And I am viewing steam "Test1"
#     When I press "Supprimer totalement"
#     Then I should be on the steam index page
#      And I should see 1 success message
#      And I should see "Le serveur steam a bien été supprimé en totalité."
