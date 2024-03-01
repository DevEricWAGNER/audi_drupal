<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Mail;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\webprofiler\DataCollector\MailDataCollector;

/**
 * Wrap the plugin.manager.mail service to collect sent mails.
 */
class MailManagerWrapper extends MailManager {

  /**
   * MailManagerWrapper constructor.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
    TranslationInterface $string_translation,
    RendererInterface $renderer,
    private readonly MailManagerInterface $mailManager,
    private readonly MailDataCollector $mailDataCollector
  ) {
    parent::__construct($namespaces, $cache_backend, $module_handler, $config_factory, $logger_factory, $string_translation, $renderer);

    $this->alterInfo('mail_backend_info');
    $this->setCacheBackend($cache_backend, 'mail_backend_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function mail($module, $key, $to, $langcode, $params = [], $reply = NULL, $send = TRUE) {
    $message = $this->mailManager->mail($module, $key, $to, $langcode, $params, $reply, $send);

    $instance = $this->mailManager->getInstance(
      ['module' => $module, 'key' => $key],
    );
    $this->mailDataCollector->addMessage($message, $instance);

    return $message;
  }

}
