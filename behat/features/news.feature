Feature:
  In order to be able to view the publications page
  As an anonymous user
  We need to be able to have access to the publications page

  @api
  Scenario: Check that we get a default set of articles that appear on the page.
    Given I am an anonymous user
    When  I visit the "news" page
    Then  I should have access to the page

  @api
  Scenario: Check the articles filters.
    Given I am an anonymous user
    When  I visit the "news" page
    And   I set the filters:
      | title | #edit-title                          | HIV  |
      | year  | #edit-field-news-date-value-value-year | 2015 |
    And   I press "Go"
    Then  I should see "Malawi hosts the final SRH/HIV Linkages Steering Committee Meeting"
