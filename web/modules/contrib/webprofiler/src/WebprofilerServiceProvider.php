<?php

declare(strict_types = 1);

namespace Drupal\webprofiler;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Site\Settings;
use Drupal\webprofiler\Compiler\ProfilerPass;
use Drupal\webprofiler\Compiler\ServicePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines a service profiler for the WebProfiler module.
 */
class WebprofilerServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    // Add a compiler pass to discover all data collector services.
    $container->addCompilerPass(new ProfilerPass());

    $container->addCompilerPass(new ServicePass(), PassConfig::TYPE_AFTER_REMOVING);

    $modules = $container->getParameter('container.modules');

    // Add BlockDataCollector only if Block module is enabled.
    if (isset($modules['block'])) {
      $container->register('webprofiler.blocks',
        'Drupal\webprofiler\DataCollector\BlocksDataCollector')
        ->addArgument(new Reference('entity_type.manager'))
        ->addTag('data_collector', [
          'template' => '@webprofiler/Collector/blocks.html.twig',
          'id' => 'blocks',
          'label' => 'Blocks',
          'priority' => 500,
        ]);
    }

    // Add ViewsDataCollector only if Views module is enabled.
    if (isset($modules['views'])) {
      $container->register('webprofiler.views', 'Drupal\webprofiler\DataCollector\ViewsDataCollector')
        ->addArgument(new Reference('views.executable'))
        ->addArgument(new Reference('entity_type.manager'))
        ->addTag('data_collector', [
          'template' => '@webprofiler/Collector/views.html.twig',
          'id' => 'views',
          'label' => 'Views',
          'priority' => 450,
        ]);
    }

    if (isset($modules['monolog'])) {
      $container->register('webprofiler.logs', 'Drupal\webprofiler\DataCollector\LogsDataCollector')
        ->addArgument(new Reference('logger.channel.debug'))
        ->addTag('data_collector', [
          'template' => '@webprofiler/Collector/logs.html.twig',
          'id' => 'logs',
          'label' => 'Logs',
          'priority' => 25,
        ]);
    }

    // Allow exception page handler to be disabled.
    $webprofiler_error_page_disabled = (bool) Settings::get('webprofiler_error_page_disabled', FALSE);
    if (!$webprofiler_error_page_disabled) {
      $container->register('webprofiler.error_handler', 'Symfony\Component\HttpKernel\EventListener\ErrorListener')
        ->addArgument('\Drupal\webprofiler\Controller\ErrorController')
        ->addArgument(new Reference('logger.channel.debug'))
        ->addArgument(TRUE)
        ->addTag('event_subscriber');
    }

    if (isset($modules['sdc'])) {
      $container->register('webprofiler.twig.component_extension', 'Drupal\webprofiler\Twig\Extension\ComponentExtension')
        ->addArgument(new Reference('plugin.manager.sdc'))
        ->addTag('twig.extension', [
          'priority' => 100,
        ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    $modules = $container->getParameter('container.modules');

    // Alter the views.executable service only if Views module is enabled.
    if (isset($modules['views'])) {
      $container->getDefinition('views.executable')
        ->setClass('Drupal\webprofiler\Views\ViewExecutableFactoryWrapper');
    }

    // Alter the big_pipe service only if BigPipe module is enabled.
    if (isset($modules['big_pipe'])) {
      $container->getDefinition('big_pipe')
        ->setClass('Drupal\webprofiler\Render\TraceableBigPipe');
    }

    // Replace the regular access_manager service with a traceable one.
    $container->getDefinition('access_manager')
      ->setClass('Drupal\webprofiler\Access\AccessManagerWrapper')
      ->addMethodCall('setDataCollector',
        [new Reference('webprofiler.request')]);

    // Replace the regular config.factory service with a traceable one.
    $container->getDefinition('config.factory')
      ->setClass('Drupal\webprofiler\Config\ConfigFactoryWrapper')
      ->addMethodCall('setDataCollector', [new Reference('webprofiler.config')]);

    // Replace the regular form_builder service with a traceable one.
    $container->getDefinition('form_builder')
      ->setClass('Drupal\webprofiler\Form\FormBuilderWrapper');

    // Replace the regular theme.negotiator service with a traceable one.
    $container->getDefinition('theme.negotiator')
      ->setClass('Drupal\webprofiler\Theme\ThemeNegotiatorWrapper');

    // Replace the regular string_translation service with a traceable one.
    $container->getDefinition('string_translation')
      ->setClass('Drupal\webprofiler\StringTranslation\TranslationManagerWrapper');

    // Replace the regular html_response.attachments_processor service with a
    // traceable one.
    $container->getDefinition('html_response.attachments_processor')
      ->setClass('Drupal\webprofiler\Render\HtmlResponseAttachmentsProcessor')
      ->addMethodCall('setDataCollector',
        [new Reference('webprofiler.assets')]);
  }

}
