Feature:
  In order to be able to view the homepage
  As an anonymous user
  We need to be able to have access to the homepage

  @api
  Scenario Outline: Visit every link page from the homepage main sections.
    Given I am an anonymous user
    When  I visit the homepage
    Then  I should see the "<section>" with the "<link>" and have access to the link destination

  Examples:
    | section             | link              |
    | main menu           | Home              |
    | main menu           | What we do        |
    | main menu           | Topics            |
    | main menu           | News              |
    | main menu           | Resources         |
    | main menu           | Data              |
    | news                | More News         |
    | videos              | More Videos       |
    | events              | Browse all Events |
    | footer              | Transparency      |
    | footer              | Contact           |
    | footer              | Sitemap           |
    | footer              | Terms of Use      |
    | footer              | Vacancies         |
    | footer social links | Twitter           |
    | footer social links | Facebook          |

