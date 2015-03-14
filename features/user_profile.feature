@user
Feature: User profile edition
  In order to manage my personal information
  As a logged user
  I want to be able to edit my name, email and password

  Background:
    Given there are following users:
      | username | email       | password | group | enabled |
      | foo      | foo@bar.net | test1234 | Team  | yes     |
      And I am logged in with foo account
      And I am on the fos user profile edit page

  Scenario: Viewing my personal information page
    Given I am on the homepage
     When I follow "Profil"
     Then I should be on the fos user profile show page
      And I should see "Nom d'utilisateur: foo"
      And I should see "Adresse e-mail: foo@bar.net"

  Scenario: Editing my information with blank fields
     When I leave "Nom d'utilisateur" empty
      And I leave "Adresse e-mail" empty
      And I press "Mettre à jour"
     Then I should still be on the fos user profile edit page
      And I should see "Veuillez renseigner le nom d'utilisateur."
      And I should see "Veuillez renseigner l'adresse email de l'utilisateur."

  Scenario: Editing my information with an invalid email
     When I fill in "Adresse e-mail" with "wrongemail"
      And I fill in "Mot de passe actuel" with "test1234"
      And I press "Mettre à jour"
     Then I should still be on the fos user profile edit page
      And I should see "Veuillez indiquer une adresse email valide."

  Scenario: Editing my information with an invalid current password
     When I fill in "Mot de passe actuel" with "wrongpassword"
      And I press "Mettre à jour"
     Then I should still be on the fos user profile edit page
      And I should see "Cette valeur doit être le mot de passe actuel de l'utilisateur."

  Scenario: Successfully editing my profile
     When I fill in "Adresse e-mail" with "foo@example.com"
      And I fill in "Mot de passe actuel" with "test1234"
      And I press "Mettre à jour"
     Then I should be on the fos user profile show page
      And I should see "Vos identifiants ont bien été mis à jour."
