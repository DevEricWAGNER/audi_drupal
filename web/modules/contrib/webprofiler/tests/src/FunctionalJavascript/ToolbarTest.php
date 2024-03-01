<?php

declare(strict_types = 1);

namespace Drupal\Tests\webprofiler\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the toolbar.
 *
 * @group webprofiler
 */
class ToolbarTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = ['devel', 'tracer', 'webprofiler'];

  /**
   * Theme to enable.
   *
   * @var string
   */
  protected $defaultTheme = 'olivero';

  /**
   * Test that the toolbar is visible.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testToolbarVisible(): void {
    $account = $this->drupalCreateUser(['view webprofiler toolbar']);
    $this->drupalLogin($account);

    $this->drupalGet('<front>');
    $this->assertSession()->elementExists('css', '.sf-toolbar');

    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert_session */
    $assert_session = $this->assertSession();

    static::assertNotEmpty($assert_session->waitForElement('css', '.sf-toolbar-status'));
    static::assertNotEmpty($assert_session->waitForElement('css', '.sf-toolbar-status-green'));

    $status = $this->getSession()->getPage()->find('css', '.sf-toolbar-status')->getText();
    static::assertEquals('200', $status);
  }

  /**
   * Test that the toolbar is visible.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testToolbarPageNotFound(): void {
    $account = $this->drupalCreateUser(['view webprofiler toolbar']);
    $this->drupalLogin($account);

    $this->drupalGet('page-not-found');
    $this->assertSession()->elementExists('css', '.sf-toolbar');

    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert_session */
    $assert_session = $this->assertSession();

    static::assertNotEmpty($assert_session->waitForElement('css', '.sf-toolbar-status'));
    static::assertNotEmpty($assert_session->waitForElement('css', '.sf-toolbar-status-red'));

    $status = $this->getSession()->getPage()->find('css', '.sf-toolbar-status')->getText();
    static::assertEquals('404', $status);
  }

}
