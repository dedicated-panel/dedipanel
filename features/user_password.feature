@user
Feature: User account password change
  In order to enhance the security of my account
  As a logged user
  I want to be able to change password

  Background:
    Given there are following users:
      | username | email       | password | group | enabled |
      | foo      | foo@bar.net | test1234 | Team  | yes     |
      And I am logged in with foo account
      And I am on the fos user profile show page

  Scenario: Viewing my password change page
    Given I follow "Modifier mon mot de passe"
     Then I should be on the fos user change password page

  Scenario: Changing my password with a wrong current password
    Given I am on the fos user change password page
     When I fill in "Mot de passe actuel" with "wrongpassword"
      And I fill in "Nouveau mot de passe" with "newpassword"
      And I fill in "Vérification" with "newpassword"
      And I press "Modifier le mot de passe"
     Then I should still be on the fos user change password page
      And I should see "Cette valeur doit être le mot de passe actuel de l'utilisateur."

  Scenario: Changing my password with a wrong confirmation password
    Given I am on the fos user change password page
     When I fill in "Mot de passe actuel" with "test1234"
      And I fill in "Nouveau mot de passe" with "newpassword"
      And I fill in "Vérification" with "wrongnewpassword"
      And I press "Modifier le mot de passe"
     Then I should still be on the fos user change password page
      And I should see "Les deux mots de passe ne sont pas identiques"

  Scenario: Successfully changing my password
    Given I am on the fos user change password page
     When I fill in "Mot de passe actuel" with "test1234"
      And I fill in "Nouveau mot de passe" with "newpassword"
      And I fill in "Vérification" with "newpassword"
      And I press "Modifier le mot de passe"
     Then I should be on the fos user profile show page
      And I should see "Votre mot de passe a bien été mis à jour."
