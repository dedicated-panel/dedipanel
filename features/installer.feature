@installer
Feature: Panel installer
  In order to use the panel
  I want to be able to install it

  Scenario: Choosing installation type
    Given I am on the installer page
      And The database need to be empty
     When I select "Installation" from "Choisissez le type de configuration"
      And I press "Prochaine étape"
     Then I should be on the installer check step

  Scenario: Checking requirements
    Given I am on the installer check step
     Then I should not see bad requirements

  Scenario: Starting the installation process
    Given I am on the installer check step
     When I follow "Prochaine étape"
     Then I should be on the installer step 1

  Scenario: Configuring database with empty fields
    Given I am on the installer step 1
     When I leave "Hôte" empty
      And I leave "Utilisateur" empty
      And I leave "Nom de la BDD" empty
      And I press "Prochaine étape"
     Then I should see 3 validation errors

  Scenario: Configuring database
    Given I am on the installer step 1
     When I fill in "localhost" for "Hôte"
      And I fill in "dedipanel" for "Nom de la BDD"
      And I fill in "root" for "Utilisateur"
      And I press "Prochaine étape"
     Then I should be on the installer step 2

  Scenario: Preloading data
    Given I am on the installer step 2
     When I press "Prochaine étape"
     Then I should be on the installer step 3
      And The database should be populated

  Scenario: Creating admin user with empty fields
    Given I am on the installer step 3
     When I leave "Nom d'utilisateur" empty
      And I leave "Adresse email" empty
      And I leave "Mot de passe" empty
      And I leave "Confirmation du mot de passe" empty
      And I press "Prochaine étape"
     Then I should see 3 validation errors

  Scenario: Creating admin user with too short password
    Given I am on the installer step 3
     When I fill in "admin" for "Nom d'utilisateur"
      And I fill in "admin@dedicated-panel.net" for "Adresse email"
      And I fill in "12345" for "Mot de passe"
      And I fill in "12345" for "Confirmation du mot de passe"
      And I press "Prochaine étape"
    Then I should see 1 validation error

  Scenario: Creating admin user
    Given I am on the installer step 3
     When I fill in "admin" for "Nom d'utilisateur"
      And I fill in "admin@dedicated-panel.net" for "Adresse email"
      And I fill in "123456" for "Mot de passe"
      And I fill in "123456" for "Confirmation du mot de passe"
      And I press "Prochaine étape"
     Then I should be on the installer final step

  Scenario: Finishing installation process
    Given I am on the installer final step
     When I follow "étape à effectuer"
     Then I should be on "/login"
