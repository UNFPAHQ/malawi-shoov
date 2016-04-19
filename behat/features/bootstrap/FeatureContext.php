<?php

use Drupal\DrupalExtension\Context\MinkContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Client;
use Behat\Behat\Tester\Exception\PendingException;

class FeatureContext extends MinkContext implements SnippetAcceptingContext {

  /**
   * @Given I am an anonymous user
   */
  public function iAmAnAnonymousUser() {
    // Just let this pass-through.
  }

  /**
   * @When /^I visit the homepage$/
   */
  public function iVisitTheHomepage() {
    $this->getSession()->visit($this->locatePath('/'));
  }

  /**
   * @Then I should have access to the page
   */
  public function iShouldHaveAccessToThePage() {
    $this->assertSession()->statusCodeEquals('200');
  }

  /**
   * Get the anchor element by it's text and it's relative parent element.
   *
   * @param $section
   *  The anchor element relative parent element.
   * @param $link_text
   *  The anchor element text.
   * @return mixed|null
   * @throws Exception
   */
  private function getLinkElement($section, $link_text) {
    $page = $this->getSession()->getPage();

    switch ($section) {
      case 'main menu':
        $link = $page->find('xpath', '//section[@id="block-menu-menu-malawi-menu"]//ul[@class="menu"]//li[contains(@class, "level-1")]/a[contains(., "' . $link_text .'")]');
        break;

      case 'sub menu':
        $link = $page->find('xpath', '//section[@id="block-system-main-menu"]//ul[@class="menu"]//li[contains(@class, "level-2")]/a[contains(., "' . $link_text .'")]');
        break;

      case 'events':
        $link = $page->find('xpath', '//div[contains(@class, "view-vw-events")]//a[contains(., "' . $link_text .'")]');
        break;

      case 'carousel':
        $link = $page->find('xpath', '//div[contains(@class, "carousel")]//a[contains(., "' . $link_text .'")]');
        break;

      case 'news':
        $link = $page->find('xpath', '//div[contains(@class, "view-vw-news")]//a[contains(., "' . $link_text .'")]');
        break;

      case 'videos':
        $link = $page->find('xpath', '//div[contains(@class, "view-vw-video")]//a[contains(., "' . $link_text .'")]');
        break;

      case 'publications':
        $link = $page->find('xpath', '//div[contains(@class, "pane-vw-publications")]//a[contains(., "' . $link_text .'")]');
        break;

      case 'sidebar':
        $link = $page->find('xpath', '//div[@id="sub-page-template"]//ul[@class="menu"]//li[contains(@class, "level-2")]/a[contains(., "' . $link_text .'")]');
        break;

      case 'stay connected':
        $link = $page->find('xpath', '//div[contains(@class, "stay_connected")]//a[contains(., "' . $link_text .'")]');
        break;

      case 'footer':
        $link = $page->find('xpath', '//div[@id="footer_links"]//ul[@class="menu"]//li[contains(@class, "level-1")]/a[contains(., "' . $link_text .'")]');
        break;

      case 'footer social links':
        $link = $page->find('xpath', '//div[@id="footer_social"]//a[contains(., "' . $link_text .'")]');
        break;

      default:
        $link = FALSE;
    }

    // In case we have no link.
    if (!$link) {
      throw new \Exception("The link: " . $link_text . " was not found on section: " . $section);
    }
    return $link;
  }

  /**
   * @Then I should see the :arg1 with the :arg2 and have access to the link destination
   */
  public function iShouldSeeTheWithTheAndHaveAccessToTheLinkDestination($section, $link_text) {

    $link = $this->getLinkElement($section, $link_text);

    // Check if we have access to the page (link url).
    $link->click();
    $url = $this->getSession()->getCurrentUrl();
    $code = $this->getSession()->getStatusCode();
    // In case the link url doesn't return a status code of '200'.
    if ($code != '200')  {
      throw new \Exception("The page code is " . $code . " it expects it to be '200' (from url: " . $url . " at section: " . $section);
    }
  }

  /**
   * @When I visit the :arg1 page
   */
  public function iVisitThePage($page) {
    $this->getSession()->visit($this->locatePath('/' . $page));
  }

  /**
   * @When I click on :arg1 tab
   */
  public function iClickOnButtonTab($button) {
    $page_url = $this->getSession()->getCurrentUrl();
    $page = $this->getSession()->getPage();

    // Click the Donut tab.
    if (!$chart_tabs = $page->find('css', 'li[tabfor="' . $button . '"]')) {
      throw new \Exception("Could not find the " . $button . " button in `donut_chart_tab` button at ". $page_url);
    }
    $chart_tabs->click();
  }

  /**
   * @Then /^I wait for css element "([^"]*)" to "([^"]*)"$/
   */
  private function iWaitForCssElement($element, $appear) {
    $xpath = $this->getSession()->getSelectorsHandler()->selectorToXpath('css', $element);
    $this->waitForXpathNode($xpath, $appear == 'appear');
  }

  /**
   * Helper function; Execute a function until it return TRUE or timeouts.
   *
   * @param $fn
   *   A callable to invoke.
   * @param int $timeout
   *   The timeout period. Defaults to 10 seconds.
   *
   * @throws Exception
   */
  private function waitFor($fn, $timeout = 10000) {
    $start = microtime(true);
    $end = $start + $timeout / 1000.0;
    while (microtime(true) < $end) {
      if ($fn($this)) {
        return;
      }
    }
    throw new \Exception('waitFor timed out.');
  }

  /**
   * Wait for an element by its XPath to appear or disappear.
   *
   * @param string $xpath
   *   The XPath string.
   * @param bool $appear
   *   Determine if element should appear. Defaults to TRUE.
   *
   * @throws Exception
   */
  private function waitForXpathNode($xpath, $appear = TRUE) {
    $this->waitFor(function ($context) use ($xpath, $appear) {
      try {
        $nodes = $context->getSession()->getDriver()->find($xpath);
        if (count($nodes) > 0) {
          $visible = $nodes[0]->isVisible();
          return $appear ? $visible : !$visible;
        }
        return !$appear;
      } catch (WebDriver\Exception $e) {
        if ($e->getCode() == WebDriver\Exception::NO_SUCH_ELEMENT) {
          return !$appear;
        }
        throw $e;
      }
    });
  }

  /**
   * @When I click on page :arg1
   */
  function iClickOnPageNumber($page_number) {
    $page_url = $this->getSession()->getCurrentUrl();
    $page = $this->getSession()->getPage();

    // Get the pagination button
    if(!$button = $page->findLink("Go to page " . $page_number)) {
      throw new Exception("The pagination button " .  $page_number . " was not found on the page " . $page_url);
    }
    $button->click();
  }

  /**
   * @Then I should not see :arg1 active
   */
  function iShouldNotSeePageNumberActive($page_number) {
    $page_url = $this->getSession()->getCurrentUrl();
    $page = $this->getSession()->getPage();

    // Get the pagination button.
    if($button = $page->findLink("Go to page " . $page_number)) {
      throw new Exception("The pagination button " . $page_number . " is active at " . $page_url);
    }
  }

  /**
   * @Given /^I set the filters:$/
   */
  public function iSetTheFilters(TableNode $table) {
    $page = $this->getSession()->getPage();

    // Iterate over each filter and set it's field value accordingly.
    foreach ($table->getRows() as $filters => $filter_data) {

      // Get the filter data: (name, element selector ,value).
      list($filter_name, $filter, $filter_value) = $filter_data;

      // In case the target element is not found.
      $element = $page->find('css', $filter);
      if (!$element) {
        throw new \Exception("The " . $filter_name . " filter field with id: " . $filter . " was not found");
      }
      $this->setElementValue($element, $filter_value);
    }
  }

  /**
   * Set an element value according to its type e.g. input || select etc.
   *
   * @param $element
   *  The target  html element to set it's value.
   * @param $value
   *  The value to be assigned to the element.
   * @throws Exception
   * @return bool
   *  In case of a success returns TRUE else throws an Exception.
   */
  private function setElementValue($element, $value) {

    // Get the element tag name.
    $tag_name = $element->getTagName();

    // Flag to identify if an element was set with a value.
    switch ($tag_name) {
      case 'input':
        if ($element->getAttribute('type') === 'checkbox') {
          $element->click();
        } else {
          // The default input type is text.
          $element->setValue($value);
        }
        $element_is_set = TRUE;
        break;

      case 'select':
        $element->selectOption($value);
        $element_is_set = TRUE;
        break;

      case 'div':
        $element->click();
        $element_is_set = TRUE;
        break;

      default:
        $element_is_set = FALSE;
    }

    if (!$element_is_set) {
      throw new \Exception("The element: " . $element->getXpath() . " was not set with the value: " .$value);
    }

    return $element_is_set;
  }

  /**
   * @Then /^I should see text:$/
   */
  public function iShouldSeeText(TableNode $table) {
    // Iterate over each title and check if it's in the page.
    foreach ($table->getRows() as $titles) {
      foreach ($titles as $title) {
        if (strpos($this->getSession()
            ->getPage()
            ->getText(), $title) === FALSE
        ) {
          throw new \Exception("Can't find the text " . $title . " on the page: " . $this->getSession()->getCurrentUrl());
        }
      }
    }
  }

  /**
   * @When I click on :arg1 link in :arg2
   */
  public function iClickOnLinkIn($link, $section) {
    $link = $this->getLinkElement($section, $link);
    $link->click();
  }

  /**
   * @When I :arg1 on :arg2 from :arg3 chart
   */
  public function iDoAnActionOnColumnFromChartName($action, $chartColumn, $chartName) {
    $page = $this->getSession()->getPage();
    sleep(10);
    // Check the svg region to hover/click on.
    switch($chartColumn) {
      case "non-core resources":
        $item = $page->find('xpath', '//div[@id="chart_div"]//*[local-name() = "svg"]//*[local-name()="rect" and @fill="#e0decd" and @width="47"]');
        break;

      case "UNFPA":
        $item = $page->find('xpath', '//div[@id="implemented-all"]//*[local-name() = "svg"]//*[local-name()="rect" and @fill="#f7931d" and @width="48"]');
        break;

      case "Latin America":
        $item = $page->find('xpath', '//div[@id="map_inner"]//*[@class="sm_state_BR"]');
        break;

      case "Asia":
        $item = $page->find('xpath', '//div[@id="map_inner"]//*[@class="sm_state_PK"]');
        break;
    }

    // Check if the svg item was found on the page.
    if (!$item) {
      throw new \Exception("The " .$chartColumn . " was not found in " . $chartName);
    }

    // Check if it needs to click on or hover on the svg item.
    switch($action) {
      case "hover":
        $item->mouseOver();
        break;

      case "click":
        $item->click();
        break;
    }
  }

}
