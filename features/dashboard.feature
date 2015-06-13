@dashboard
Feature: Panel dashboard
    In order to have an overview of my server
    As a panel user
    I need to be able to see servers informations

    Background:
      Given there are following groups:
        | name   | roles                        | parent |
        | Team   | ROLE_SUPER_ADMIN             |        |
        | Team 2 | ROLE_DP_VOIP_TEAMSPEAK_ADMIN | Team   |
        | Team 3 | ROLE_DP_GAME_STEAM_ADMIN     | Team   |
        | Team 4 | ROLE_DP_GAME_MINECRAFT_ADMIN | Team   |
        | Team 5 | ROLE_DP_ADMIN_MACHINE_ADMIN  | Team   |
        | Team 6 | ROLE_USER                    | Team   |
      Given there are following users:
        | username       | email              | password | group  | role             | enabled |
        | admin_user     | admin@user.net     | test1234 |        | ROLE_SUPER_ADMIN | yes     |
        | ts_user        | ts@user.net        | test1234 | Team 2 |                  | yes     |
        | steam_user     | steam@user.net     | test1234 | Team 3 |                  | yes     |
        | minecraft_user | minecraft@user.net | test1234 | Team 4 |                  | yes     |
        | machine_user   | machine@user.net   | test1234 | Team 5 |                  | yes     |
        | user           | user@user.net      | test1234 | Team 6 |                  | yes     |
      And there are following machines:
        | privateIp | username | key     | group  | is64Bit |
        | 127.0.0.1 | testing1 | id_rsa1 | Team   | yes     |
        | 127.0.0.1 | testing2 | id_rsa2 | Team 3 | yes     |
        | 127.0.0.1 | testing3 | id_rsa3 | Team 2 | yes     |
        | 127.0.0.1 | testing4 | id_rsa2 | Team 3 | yes     |
        | 127.0.0.1 | bugged   |         | Team 3 | yes     |
      And there are following games:
        | name           | launchName | appId | appMod  | bin                  | type      | available |
        | Counter-Strike | hlds_run   | 90    | cstrike | steam                | steam     | yes       |
        | Minecraft      | minecraft  |       |         | minecraft_server.jar | minecraft | yes       |
        | Bukkit         | minecraft  |       |         | craftbukkit.jar      | minecraft | yes       |
      And there are following steam servers:
        | name  | machine  | port  | rconPassword | game           | installDir | maxplayers | installed |
        | Test1 | testing1 | 27025 | test1        | Counter-Strike | test1      | 2          | yes       |
        | Test2 | testing2 | 27025 | test2        | Counter-Strike | testcs     | 2          | yes       |
        | Test3 | testing3 | 27025 | test3        | Counter-Strike | test3      | 2          | yes       |
      And there are following minecraft servers:
        | name  | machine  | port  | queryPort | rconPort | rconPassword | game      | installDir | maxplayers | minHeap | maxHeap | installed |
        | Test1 | testing1 | 25565 | 25565     | 25575    | test1        | Minecraft | test1      | 2          | 128     | 256     | yes       |
        | Test2 | testing2 | 25565 | 25565     | 25575    | test2        | Minecraft | test4      | 2          | 128     | 256     | yes       |
        | Test3 | testing3 | 25565 | 25565     | 25575    | test3        | Minecraft | test3      | 2          | 128     | 256     | yes       |
      And there are following teamspeak servers:
        | machine  | queryPassword | installDir | installed |
        | testing1 | test1         | test1      | yes       |
        | testing2 | test2         | test2      | yes       |
        | testing3 | test3         | test3      | yes       |

    Scenario: Viewing the dashboard at website root
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see "Tableau de bord"

    Scenario: Viewing user list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 6 users in the list

    Scenario: Viewing user list as not granted user
        Given I am logged in with user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 0 users in the list

    Scenario: Accessing the user page from list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
         When I follow "Liste des utilisateurs"
         Then I should be on the user index page

    Scenario: Viewing machine list as super-admin
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 5 machines in the list

    Scenario: Viewing machine list as granted user
        Given I am logged in with machine_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 5 machines in the list

    Scenario: Viewing machine list as not granted user
        Given I am logged in with user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 0 machines in the list

    Scenario: Accessing the machine page from list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
         When I follow "Liste des machines"
         Then I should be on the machine index page

    Scenario: Viewing steam server list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 3 steam servers in the list

    Scenario: Viewing steam server list as granted user
        Given I am logged in with steam_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 3 steam servers in the list

    Scenario: Viewing steam server list as not granted user
        Given I am logged in with user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 0 steam servers in the list

    Scenario: Accessing the steam page from list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
         When I follow "Serveurs Steam"
         Then I should be on the steam index page

    Scenario: Viewing minecraft server list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 3 minecraft servers in the list

    Scenario: Viewing minecraft server list as granted user
        Given I am logged in with minecraft_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 3 minecraft servers in the list

    Scenario: Viewing minecraft server list as not granted user
        Given I am logged in with user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 0 minecraft servers in the list

    Scenario: Accessing the minecraft page from list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
         When I follow "Serveurs Minecraft"
         Then I should be on the minecraft index page

    Scenario: Viewing teamspeak server list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 3 teamspeak servers in the list

    Scenario: Viewing teamspeak server list as granted user
        Given I am logged in with ts_user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 3 teamspeak servers in the list

    Scenario: Viewing teamspeak server list as not granted user
        Given I am logged in with user account
          And I am on the homepage
         Then I should be on the _welcome page
          And I should see 0 teamspeak servers in the list

    Scenario: Accessing the teamspeak page from list
        Given I am logged in with admin_user account
          And I am on the homepage
         Then I should be on the _welcome page
         When I follow "Liste des serveurs Teamspeak"
         Then I should be on the teamspeak index page