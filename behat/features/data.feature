Feature:
  In order to be able to view for transparency portal statistic data
  As an anonymous user
  We need to be able to access the portal page

  @javascript
  Scenario: Visit Transparency Portal page, and check resources hover
    Given I am an anonymous user
    When  I visit the "http://www.unfpa.org/transparency-portal/unfpa-malawi"
    And   I click on Core tab
    Then  I should see "Malawi 2014 programme expenses (core)"

  @api @wip
  Scenario Outline: Visit Transparency Portal page, and check region selector
    Given I am an anonymous user
    When  I visit the "http://www.unfpa.org/transparency-portal/unfpa-malawi"
    Then  I should see "<paragraph>"

    Examples:
      | paragraph                                                                   |
      | Increased availability and use of integrated sexual and reproductive health |
      | Advanced gender equality, women’s and girls’ empowerment                    |
      | Strengthened national policies and international development agendas        |
      | Increased priority on adolescents, especially on very young adolescent      |
