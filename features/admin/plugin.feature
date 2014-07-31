@plugins_admin
Feature: Plugin settings
  In order to configure my panel
  As a panel admin
  I want to be able to edit plugin config

  Background:
    Given there are following users:
      | username | email       | password | role                       | enabled |
      | foo      | foo@bar.net | test1234 | ROLE_DP_ADMIN_PLUGIN_ADMIN | yes     |
      | baz      | baz@bar.net | test1234 | ROLE_USER                  | yes     |
    And there are following games:
      | name           | installName | bin      | type  | available |
      | Counter-Strike | cstrike     | hlds_run | steam | yes       |
      | Day Of Defeat  | dod         | hlds_run | steam | yes       |
    And there are following plugins:
      | name       | version | scriptName | downloadUrl                                |
      | Metamod    | 1.21-am | metamod    | www.dedicated-panel.net/metamod-1.21-am.tar.gz |
      | AMX Mod X  | 1.8.2   | amxmodx    | www.amxmodx.org/dl.php?file_id=690&mirror_id=2 |

  Scenario: Seeing index of all plugins
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Gestion des plugins"
     Then I should be on the plugin index page
      And I should see 2 plugins in the list

  Scenario: Accessing the plugin creation form
    Given I am logged in with foo account
      And I am on the plugin index page
     When I follow "Ajouter un plugin"
     Then I should be on the plugin creation page

  Scenario: Submitting empty form
    Given I am logged in with foo account
      And I am on the plugin creation page
     When I press "Créer"
     Then I should still be on the plugin creation page
      And I should see 4 validation errors

  Scenario: Creating plugin
    Given I am logged in with foo account
      And I am on the plugin creation page
     When I fill in dedipanel_plugin form with:
      | name        | Sourcemod                                                    |
      | version     | 1.5.0                                                        |
      | downloadUrl | sourcemod.gameconnect.net/files/sourcemod-1.5.0-linux.tar.gz |
      | scriptName  | sourcemod                                                    |
      And I press "Créer"
     Then I should be on the page of plugin "Sourcemod"
      And I should see 1 alert success message
      And I should see "Le plugin a bien été créé."

  Scenario: Accessing the plugin editing form
    Given I am logged in with foo account
      And I am on the page of plugin "Metamod"
     When I follow "Modifier"
     Then I should be editing plugin "Metamod"

  Scenario: Accessing the plugin editing form from the list
    Given I am logged in with foo account
      And I am on the plugin index page
     When I click "Modifier" near "Metamod"
     Then I should be editing plugin "Metamod"

  Scenario: Updating the plugin
    Given I am logged in with foo account
      And I am editing plugin "Metamod"
     When I fill in "Nom" with "Metamod:Source"
      And I press "Mettre à jour"
     Then I should be on the page of plugin "Metamod:Source"
      And I should see 1 alert success message
      And I should see "Le plugin a bien été mis à jour."

  Scenario: Associating a game to the plugin
    Given I am logged in with foo account
      And I am editing plugin "Metamod"
     When I select "Counter-Strike" from "Jeux associés"
      And I press "Mettre à jour"
     Then I should be on the page of plugin "Metamod"
      And I should see 1 alert success message
      And I should see "Le plugin a bien été mis à jour."
      And I should see 1 associated game

  Scenario: Associating multiple games to plugin
    Given I am logged in with foo account
      And I am editing plugin "Metamod"
     When I select "Counter-Strike" from "Jeux associés"
      And I additionally select "Day Of Defeat" from "Jeux associés"
      And I press "Mettre à jour"
     Then I should be on the page of plugin "Metamod"
      And I should see 1 alert success message
      And I should see "Le plugin a bien été mis à jour."
      And I should see 2 associated games

  Scenario: Deleting plugin
    Given I am logged in with foo account
      And I am on the page of plugin "Metamod"
     When I press "Supprimer"
     Then I should be on the plugin index page
      And I should see 1 alert success message
      And I should see "Le plugin a bien été supprimé."
