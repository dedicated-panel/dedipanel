@minecraft
Feature: Minecraft server management
  In order to manage minecraft server
  As a panel user
  I want to be able to act on servers

  Background:
    Given there are following groups:
      | name      | parent | roles                        |
      | Team 1    |        | ROLE_DP_GAME_MINECRAFT_ADMIN |
      | SubTeam 1 | Team 1 | ROLE_DP_GAME_MINECRAFT_ADMIN |
      | Team 2    |        | ROLE_DP_GAME_MINECRAFT_ADMIN |
    And there are following users:
      | username | email       | password | group     | role             | enabled |
      | foo      | foo@foo.net | test1234 |           | ROLE_SUPER_ADMIN | yes     |
      | baz      | baz@baz.net | test1234 | Team 1    | ROLE_ADMIN       | yes     |
      | bar      | bar@bar.net | test1234 | Team 2    |                  | yes     |
      | boz      | boz@boz.net | test1234 | SubTeam 1 |                  | yes     |
    And there are following machines:
      | privateIp | username | key     | group     |
      | 127.0.0.1 | testing1 | id_rsa1 | Team 1    |
      | 127.0.0.1 | testing2 | id_rsa2 | SubTeam 1 |
      | 127.0.0.1 | testing3 | id_rsa3 | Team 2    |
    And there are following games:
      | name      | installName | launchName | bin                  | type      | available |
      | Minecraft | minecraft   | minecraft  | minecraft_server.jar | minecraft | yes       |
      | Bukkit    | bukkit      | minecraft  | craftbukkit.jar      | minecraft | yes       |
    And there are following minecraft servers:
      | name  | machine  | port  | rconPort | rconPassword | game      | installDir | maxplayers | minHeap | maxHeap | installed |
      | Test1 | testing1 | 25565 | 25575    | test1        | Minecraft | test1      | 2          | 128     | 256     | yes       |
      | Test2 | testing2 | 25565 | 25575    | test2        | Minecraft | test2      | 2          | 128     | 256     | yes       |
      | Test3 | testing3 | 25565 | 25575    | test3        | Minecraft | test3      | 2          | 128     | 256     | yes       |

  Scenario: Seeing index of all minecraft servers when super admin
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Serveurs Minecraft"
     Then I should be on the minecraft index page
      And I should see 3 minecraft servers in the list

  Scenario: Seeing index of all minecraft servers when admin
    Given I am logged in with baz account
      And I am on the homepage
     When I follow "Serveurs Minecraft"
     Then I should be on the minecraft index page
      And I should see 2 minecraft servers in the list

  Scenario: Accessing the minecraft server details page from the list as admin
    Given I am logged in with baz account
      And I am on the minecraft index page
     When I follow "Test1"
     Then I should be on the page of minecraft server "Test1"

  Scenario: Accessing the minecraft server creation
    Given I am logged in with boz account
      And I am on the minecraft index page
     When I follow "Ajouter un serveur Minecraft"
     Then I should be on the minecraft creation page

  Scenario: Submitting empty form
    Given I am logged in with boz account
      And I am on the minecraft creation page
     When I press "Créer"
     Then I should still be on the minecraft creation page
      And I should see 8 validation errors

  Scenario: Adding a minecraft server
    Given I am logged in with boz account
      And I am on the minecraft creation page
     When I fill in dedipanel_minecraft_add form with:
      | machine      | testing3  |
      | name         | Test4     |
      | port         | 25565     |
      | queryPort    | 25565     |
      | rconPort     | 25575     |
      | rconPassword | test4     |
      | game         | minecraft |
      | dir          | test4     |
      | maxplayers   | 2         |
      | minHeap      | 128       |
      | maxHeap      | 256       |
      And I press "Créer"
     Then I should be on the page of minecraft server "Test4"
      And I should see 1 success message
      And I should see "Le serveur minecraft a bien été ajoutée."
