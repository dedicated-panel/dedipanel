@teamspeak_instance
Feature: Teamspeak instances management
  In order to manage teamspeak instances
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
      | privateIp | username | key     | groups            |
      | 127.0.0.1 | testing1 | id_rsa1 | Team 1            |
      | 127.0.0.1 | testing2 | id_rsa2 | SubTeam 1         |
      | 127.0.0.1 | testing3 | id_rsa3 | Team 2            |
      | 127.0.0.1 | testing4 | id_rsa2 | SubTeam 1         |
      | 127.0.0.1 | bugged   |         | SubTeam 1         |
    And there are following teamspeak servers:
      | machine  | queryPassword | installDir | installed |
      | testing4 | test-ts       | test-ts    | yes       |
    And there are following teamspeak instances:
      | instanceId | name  | server             | port | slots |
      | 1          | Test1 | testing4@127.0.0.1 | 9987 | 2     |
      | 2          | Test2 | testing4@127.0.0.1 | 9988 | 2     |

  Scenario: Seeing index of all teamspeak instances
    Given I am logged in with foo account
      And I am on the teamspeak index page
     When I follow "testing4@127.0.0.1"
     Then I should be on the teamspeak "testing4@127.0.0.1" instance index
      And I should see 2 teamspeak instances in the list

  Scenario: Submitting empty form
    Given I am logged in with baz account
      And I am on the teamspeak instance creation page for "testing4@127.0.0.1"
     When I press "Créer"
     Then I should still be on the teamspeak instance creation page for "testing4@127.0.0.1"
      And I should see 2 validation errors

  Scenario: Adding a teamspeak server
    Given I am logged in with boz account
      And I am on the teamspeak instance creation page for "testing4@127.0.0.1"
     When I fill in dedipanel_teamspeak_instance form with:
      | name       | Test3 |
      | port       | 9989  |
      | maxClients | 2     |
      And I press "Créer"
     Then I should be on the page of teamspeak instance "Test3"
      And I should see 1 success message
      And I should see "L'instance teamspeak a bien été ajoutée."

  Scenario: Accessing the teamspeak instance editing form as admin
    Given I am logged in with baz account
      And I am on the page of teamspeak instance "Test1"
     When I follow "Modifier"
     Then I should be editing teamspeak instance "Test1"

  Scenario: Updating a teamspeak instance as admin
    Given I am logged in with baz account
      And I am editing teamspeak instance "Test1"
     When I fill in "Slots" with "4"
      And I press "Mettre à jour"
     Then I should be on the page of teamspeak instance "Test1"
      And I should see 1 success message
      And I should see "L'instance teamspeak a bien été mise à jour."

  Scenario: Trying to increase slots to 2000
    Given I am logged in with baz account
      And I am editing teamspeak instance "Test1"
     When I fill in "Slots" with "2000"
      And I press "Mettre à jour"
     Then I should still be editing teamspeak instance "Test1"
      And I should see 1 error message
      And I should see "Le nombre maximum de slots est atteint."

  Scenario: Deleting a teamspeak instance as admin
    Given I am logged in with baz account
      And I am viewing teamspeak instance "Test1"
     When I press "Supprimer"
     Then I should be on the teamspeak "testing4@127.0.0.1" instance index
      And I should see 1 success message
      And I should see "L'instance teamspeak a bien été supprimée."
