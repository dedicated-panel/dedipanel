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
      | Team 3    |        | ROLE_DP_GAME_MINECRAFT_ADMIN |
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
      | name      | installName | launchName | bin                  | type      | available |
      | Minecraft | minecraft   | minecraft  | minecraft_server.jar | minecraft | yes       |
      | Bukkit    | bukkit      | minecraft  | craftbukkit.jar      | minecraft | yes       |
    And there are following minecraft servers:
      | name  | machine  | port  | queryPort | rconPort | rconPassword | game      | installDir | maxplayers | minHeap | maxHeap | installed |
      | Test1 | testing1 | 25565 | 25565     | 25575    | test1        | Minecraft | test1      | 2          | 128     | 256     | yes       |
      | Test2 | testing2 | 25565 | 25565     | 25575    | test2        | Minecraft | test4      | 2          | 128     | 256     | yes       |
      | Test3 | testing3 | 25565 | 25565     | 25575    | test3        | Minecraft | test3      | 2          | 128     | 256     | yes       |

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

  Scenario: Accessing the minecraft creation form as super admin
    Given I am logged in with foo account
      And I am on the minecraft index page
     When I follow "Ajouter un serveur Minecraft"
     Then I should be on the minecraft creation page
      And I should see 4 "machine" options in "dedipanel_minecraft" form

  Scenario: Accessing the minecraft creation form as team admin
    Given I am logged in with baz account
      And I am on the minecraft index page
     When I follow "Ajouter un serveur Minecraft"
     Then I should be on the minecraft creation page
      And I should see 3 "machine" options in "dedipanel_minecraft" form

  Scenario: Accessing the minecraft creation form as normal user
    Given I am logged in with biz account
      And I am on the minecraft index page
     When I follow "Ajouter un serveur Minecraft"
     Then I should be on the minecraft creation page
      And I should see 1 "machine" options in "dedipanel_minecraft" form

  Scenario: Accessing the minecraft creation form as subteam user
    Given I am logged in with boz account
      And I am on the minecraft index page
     When I follow "Ajouter un serveur Minecraft"
     Then I should be on the minecraft creation page
      And I should see 2 "machine" options in "dedipanel_minecraft" form

  Scenario: Submitting empty form
    Given I am logged in with bar account
      And I am on the minecraft creation page
     When I press "Créer"
     Then I should still be on the minecraft creation page
      And I should see 10 validation errors

  Scenario: Adding a minecraft server
    Given I am logged in with boz account
      And I am on the minecraft creation page
     When I fill in dedipanel_minecraft form with:
      | machine      | testing2  |
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
     Then I should be on the minecraft index page
      And I should see 2 success message
      And I should see "Le serveur minecraft a bien été ajouté."
      And I should see "L'installation de votre serveur est terminé."

  Scenario: Adding an existing minecraft server
    Given I am logged in with boz account
      And I am on the minecraft creation page
     When I fill in dedipanel_minecraft form with:
      | machine          | testing2  |
      | name             | Test4     |
      | port             | 25565     |
      | queryPort        | 25565     |
      | rconPort         | 25575     |
      | rconPassword     | test4     |
      | game             | minecraft |
      | dir              | test4     |
      | maxplayers       | 2         |
      | minHeap          | 128       |
      | maxHeap          | 256       |
      | alreadyInstalled | yes       |
      And I press "Créer"
     Then I should be on the minecraft index page
      And I should see 2 success message
      And I should see "Le serveur minecraft a bien été ajouté."
      And I should see "L'installation de votre serveur est terminé."

  Scenario: Adding a minecraft server on a bugged machine
    Given I am logged in with boz account
      And I am on the minecraft creation page
     When I fill in dedipanel_minecraft form with:
      | machine          | bugged    |
      | name             | Test4     |
      | port             | 25565     |
      | queryPort        | 25565     |
      | rconPort         | 25575     |
      | rconPassword     | test4     |
      | game             | minecraft |
      | dir              | test4     |
      | maxplayers       | 2         |
      | minHeap          | 128       |
      | maxHeap          | 256       |
      | alreadyInstalled | no        |
      And I press "Créer"
     Then I should still be on the minecraft creation page
      And I should see 1 error message
      And I should see "La machine sélectionnée est actuellement indisponible."

  Scenario: Adding a minecraft server on an existing directory
    Given I am logged in with boz account
      And I am on the minecraft creation page
     When I fill in dedipanel_minecraft form with:
      | machine          | testing2  |
      | name             | Test4     |
      | port             | 25565     |
      | queryPort        | 25565     |
      | rconPort         | 25575     |
      | rconPassword     | test4     |
      | game             | minecraft |
      | dir              | test4     |
      | maxplayers       | 2         |
      | minHeap          | 128       |
      | maxHeap          | 256       |
      | alreadyInstalled | no        |
      And I press "Créer"
     Then I should still be on the minecraft creation page
      And I should see 1 error message
      And I should see "Le dossier d'installation existe déjà sur le serveur."

  Scenario: Accessing the minecraft editing form as admin
    Given I am logged in with baz account
      And I am on the page of minecraft "Test2"
     When I follow "Modifier"
     Then I should be editing minecraft "Test2"

  Scenario: Accessing the minecraft editing form from the list as admin
    Given I am logged in with baz account
      And I am on the minecraft index page
     When I click "Modifier" near "Test2"
     Then I should be editing minecraft "Test2"

  Scenario: Updating a minecraft server as admin
    Given I am logged in with baz account
      And I am editing minecraft "Test2"
     When I fill in "Nom" with "Test21"
      And I press "Mettre à jour"
     Then I should be on the page of minecraft with name "Test21"

  Scenario: Deleting a minecraft server as admin
    Given I am logged in with baz account
      And I am viewing minecraft "Test2"
     When I press "Supprimer"
     Then I should be on the minecraft index page
      And I should see 1 success message
      And I should see "Le serveur minecraft a bien été supprimé."

  Scenario: Completely deleting a minecraft server as admin
    Given I am logged in with baz account
      And I am viewing minecraft "Test1"
     When I press "Supprimer totalement"
     Then I should be on the minecraft index page
      And I should see 1 success message
      And I should see "Le serveur minecraft a bien été supprimé en totalité."

  Scenario: Starting a minecraft server
    Given I am logged in with boz account
      And I am on the minecraft index page
     When I press "Démarrer" near "Test2"
     Then I should be on the minecraft index page
      And I should see 1 success message
      And I should see "Démarrage du serveur en cours."

  Scenario: Seeing index of all files on a minecraft server
    Given I am logged in with boz account
      And I am on the minecraft index page
     When I follow "Zone FTP" near "Test2"
     Then I should be on the ftp page of minecraft "Test2"
      And I should see 3 files in the list
