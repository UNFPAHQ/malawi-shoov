Feature:
  In order to be able to view the topics articles
  As an anonymous user
  We need to be able to have access to the topics page

  @api
  Scenario: Check that we get a default set of articles that appear on the page.
    Given I am an anonymous user
    When  I visit the "topics/sexual-reproductive-health-0" page
    Then  I should see text:
      | Sexual and Reproductive Health  |
      | Related News                    |
      | Related Publications            |
