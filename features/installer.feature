@installer
Feature: Panel installer
  In order to use the panel
  I want to be able to install it

  Scenario: Choosing the installation step
    Given I am on the installer page
     When I select "Installation" from "Choisissez le type de configuration"
      And I press "Prochaine étape"
     Then I should be on the installer check page

  Scenario: Starting the installation process
    Given I should be on the installer check page
     When I follow "Prochaine étape"
     Then I should be on the installer step 1

  Scenario: Configuring database with empty fields
    Given I am on the installer step 1
      And I leave "Hôte" empty
      And I leave "Utilisateur" empty
      And I leave "Nom de la BDD" empty
     When I press "Prochaine étape"
     Then I should see 3 validation errors

  Scenario: Configuring database
    Given I am on the installer step 1
      And I fill in "localhost" for "Hôte"
      And I fill in "dedipanel_test" for "Nom de la BDD"
      And I fill in "root" for "Utilisateur"
     When I press "Prochaine étape"
     Then I should be on the installer step 2
