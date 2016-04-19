Feature:
  In order to be able to view the about-us
  As an anonymous user
  We need to be able to have access to the about-us

  @api
  Scenario Outline: Visit every sidebar link.
    Given I am an anonymous user
    When  I visit the "about-us-UNFPA-Malawi" page
    Then  I should see the "<section>" with the "<link>" and have access to the link destination

    Examples:
      | section       | link                            |
      | sidebar       | Implementing Parners            |
      | sidebar       | Message from the Representative |
