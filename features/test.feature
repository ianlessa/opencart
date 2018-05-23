# features/test.feature
  Feature: Test
    In order to test Function Test Integration
    As a developer
    I need to be able to login on Opencart

  @javascript
  Scenario: Login into Opencart
    Given I am on "/index.php?route=account/login"
    When I fill in "email" with "test@test.com"
    And I fill in "password" with "test12"
    And I press "Login"
    Then I wait for text "Edit your account information" to appear, for 10 seconds