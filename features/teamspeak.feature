@teamspeak
Feature: Teamspeak server management
  In order to manage teamspeak server
  As a panel user
  I want to be able to act on servers

  Background:
    Given there are following groups:
      | name      | parent | roles                        |
      | Team 1    |        | ROLE_DP_VOIP_TEAMSPEAK_ADMIN |
      | SubTeam 1 | Team 1 | ROLE_DP_VOIP_TEAMSPEAK_ADMIN |
      | Team 2    |        | ROLE_DP_VOIP_TEAMSPEAK_ADMIN |
      | Team 3    |        | ROLE_DP_VOIP_TEAMSPEAK_ADMIN |
    And there are following users:
      | username | email       | password | group     | role             | enabled |
      | foo      | foo@foo.net | test1234 |           | ROLE_SUPER_ADMIN | yes     |
      | baz      | baz@baz.net | test1234 | Team 1    | ROLE_ADMIN       | yes     |
      | biz      | biz@biz.net | test1234 | Team 1    |                  | yes     |
      | bar      | bar@bar.net | test1234 | Team 3    |                  | yes     |
      | boz      | boz@boz.net | test1234 | SubTeam 1 |                  | yes     |
    And there are following machines:
      | privateIp | username | key     | group     | is64Bit |
      | 127.0.0.1 | testing1 | id_rsa1 | Team 1    | yes     |
      | 127.0.0.1 | testing2 | id_rsa2 | SubTeam 1 | yes     |
      | 127.0.0.1 | testing3 | id_rsa3 | Team 2    | yes     |
      | 127.0.0.1 | testing4 | id_rsa2 | SubTeam 1 | yes     |
      | 127.0.0.1 | bugged   |         | SubTeam 1 | yes     |
    And there are following teamspeak servers:
      | machine  | queryPassword | installDir | installed |
      | testing1 | test1         | test1      | yes       |
      | testing2 | test2         | test2      | yes       |
      | testing3 | test3         | test3      | yes       |

  Scenario: Seeing index of all teamspeak servers when super admin
    Given I am logged in with foo account
      And I am on the homepage
     When I follow "Serveurs Teamspeak"
     Then I should be on the teamspeak index page
      And I should see 3 teamspeak servers in the list

  Scenario: Seeing index of all teamspeak servers when admin
    Given I am logged in with baz account
      And I am on the homepage
     When I follow "Serveurs Teamspeak"
     Then I should be on the teamspeak index page
      And I should see 2 teamspeak servers in the list

  Scenario: Accessing the teamspeak instance index page from the list as admin
    Given I am logged in with baz account
      And I am on the teamspeak index page
     When I follow "testing1@127.0.0.1"
     Then I should be on the teamspeak "testing1@127.0.0.1" instance index

  Scenario: Accessing the teamspeak creation form as super admin
    Given I am logged in with foo account
      And I am on the teamspeak index page
     When I follow "Ajouter"
     Then I should be on the teamspeak creation page
      And I should see 5 "machine" options in "dedipanel_teamspeak" form

  Scenario: Accessing the teamspeak creation form as team admin
    Given I am logged in with baz account
      And I am on the teamspeak index page
     When I follow "Ajouter"
     Then I should be on the teamspeak creation page
      And I should see 4 "machine" options in "dedipanel_teamspeak" form

  Scenario: Accessing the teamspeak creation form as normal user
    Given I am logged in with biz account
      And I am on the teamspeak index page
     When I follow "Ajouter"
     Then I should be on the teamspeak creation page
      And I should see 1 "machine" options in "dedipanel_teamspeak" form

  Scenario: Accessing the teamspeak creation form as subteam user
    Given I am logged in with boz account
      And I am on the teamspeak index page
     When I follow "Ajouter"
     Then I should be on the teamspeak creation page
      And I should see 3 "machine" options in "dedipanel_teamspeak" form

  Scenario: Submit empty form
    Given I am logged in with bar account
      And I am on the teamspeak creation page
     When I press "Créer"
     Then I should still be on the teamspeak creation page
      And I should see 2 validation errors

  Scenario: Adding a teamspeak server
    Given I am logged in with boz account
      And I am on the teamspeak creation page
     When I fill in dedipanel_teamspeak form with:
      | machine        | testing4 |
      | query_password | test-ts  |
      | dir            | test-ts  |
      And I press "Créer"
     Then I should be on the teamspeak "testing4@127.0.0.1" instance index
      And I should see 2 success message
      And I should see "Le serveur teamspeak a bien été ajouté."
      And I should see "L'installation de votre serveur est terminé."

  Scenario: Adding an existing teamspeak server
    Given I am logged in with boz account
      And I am on the teamspeak creation page
     When I fill in dedipanel_teamspeak form with:
      | machine           | testing4 |
      | query_password    | test-ts  |
      | dir               | test-ts  |
      | alreadyInstalled  | yes      |
      And I press "Créer"
     Then I should be on the teamspeak "testing4@127.0.0.1" instance index
      And I should see 2 success message
      And I should see "Le serveur teamspeak a bien été ajouté."
      And I should see "L'installation de votre serveur est terminé."

  Scenario: Adding a teamspeak server on a bugged machine
    Given I am logged in with boz account
      And I am on the teamspeak creation page
     When I fill in dedipanel_teamspeak form with:
      | machine        | bugged  |
      | query_password | test-ts |
      | dir            | test-ts |
      And I press "Créer"
     Then I should still be on the teamspeak creation page
      And I should see 1 error message
      And I should see "La machine sélectionnée est actuellement indisponible."

  Scenario: Adding a teamspeak server on an existing directory
    Given I am logged in with boz account
      And I am on the teamspeak creation page
     When I fill in dedipanel_teamspeak form with:
      | machine           | testing4 |
      | query_password    | test-ts  |
      | dir               | test-ts  |
      And I press "Créer"
     Then I should still be on the teamspeak creation page
      And I should see 1 error message
      And I should see "Le dossier d'installation existe déjà sur le serveur."

  Scenario: Accessing the teamspeak editing form as admin
    Given I am logged in with baz account
      And I am on the teamspeak "testing1@127.0.0.1" instance index
     When I follow "Modifier le serveur"
     Then I should be editing teamspeak "testing1@127.0.0.1"

  Scenario: Deleting a teamspeak server as admin
    Given I am logged in with baz account
      And I am editing teamspeak "testing1@127.0.0.1"
     When I press "Supprimer"
     Then I should be on the teamspeak index page
      And I should see 1 success message
      And I should see "Le serveur teamspeak a bien été supprimé."

#  Scenario: Completly deleting a teamspeak server as admin
#    Given I am logged in with baz account
#      And I am editing teamspeak "testing1@127.0.0.1"
#     When I press "Supprimer totalement"
#     Then I should be on the teamspeak index page
#      And I should see 1 success message
#      And I should see "Le serveur teamspeak a bien été supprimé en totalité."
