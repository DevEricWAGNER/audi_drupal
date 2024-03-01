<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\sdc\Plugin\Component;
use Drupal\webprofiler\Theme\ThemeNegotiatorWrapper;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Twig\Markup;
use Twig\Profiler\Dumper\HtmlDumper;
use Twig\Profiler\Profile;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Collects theme data.
 */
class ThemeDataCollector extends DataCollector implements HasPanelInterface, LateDataCollectorInterface {

  use StringTranslationTrait, DataCollectorTrait, PanelTrait;

  /**
   * Used to store twig computed data between method calls.
   *
   * @var array|null
   */
  private ?array $computed = NULL;

  /**
   * The twig profile.
   *
   * @var \Twig\Profiler\Profile|null
   */
  private ?Profile $profile = NULL;

  /**
   * ThemeDataCollector constructor.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   * @param \Drupal\Core\Theme\ThemeNegotiatorInterface $themeNegotiator
   *   The theme negotiator.
   * @param \Drupal\Core\Template\TwigEnvironment $twig
   *   The Twig service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Twig\Profiler\Profile $profile
   *   The twig profile.
   */
  public function __construct(
    private readonly ThemeManagerInterface $themeManager,
    private readonly ThemeNegotiatorInterface $themeNegotiator,
    private readonly TwigEnvironment $twig,
    public ModuleHandlerInterface $moduleHandler,
    Profile $profile,
  ) {
    $this->profile = $profile;
    $this->data['components'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'theme';
  }

  /**
   * Reset the collected data.
   */
  public function reset(): void {
    $this->data = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Throwable $exception = NULL): void {
    $activeTheme = $this->themeManager->getActiveTheme();

    $this->data['activeTheme'] = [
      'name' => $activeTheme->getName(),
      'path' => $activeTheme->getPath(),
      'engine' => $activeTheme->getEngine(),
      'owner' => $activeTheme->getOwner(),
      'baseThemes' => $activeTheme->getBaseThemeExtensions(),
      'extension' => $activeTheme->getExtension(),
      'librariesOverride' => $activeTheme->getLibrariesOverride(),
      'librariesExtend' => $activeTheme->getLibrariesExtend(),
      'libraries' => $activeTheme->getLibraries(),
      'regions' => $activeTheme->getRegions(),
    ];

    $this->data['twig_extensions'] = [
      'filters' => array_map(function (TwigFilter $filter) {
        return [
          'name' => $filter->getName(),
          'callable' => $this->getCallableContext($filter->getCallable()),
        ];
      }, $this->twig->getFilters()),
      'functions' => array_map(function (TwigFunction $function) {
        return [
          'name' => $function->getName(),
          'callable' => $this->getCallableContext($function->getCallable()),
        ];
      }, $this->twig->getFunctions()),
      'globals' => $this->twig->getGlobals(),
    ];

    if ($this->themeNegotiator instanceof ThemeNegotiatorWrapper) {
      $this->data['negotiator'] = [
        'class' => $this->getMethodData($this->themeNegotiator->getNegotiator(), 'determineActiveTheme'),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect(): void {
    $this->data['twig'] = serialize($this->profile);
  }

  /**
   * Add an SDC component to the data collector.
   *
   * @param \Drupal\sdc\Plugin\Component $component
   *   The SDC component to add.
   */
  public function addComponent(Component $component): void {
    if (!isset($this->data['components'][$component->getPluginId()])) {
      $converter = new CommonMarkConverter();

      $this->data['components'][$component->getPluginId()] = [
        'name' => $component->metadata->name,
        'status' => $component->metadata->status,
        'path' => $component->metadata->path,
        'documentation' => $converter->convert($component->metadata->documentation),
        'group' => $component->metadata->group,
        'thumbnail' => $component->metadata->getThumbnailPath(),
        'plugin_id' => $component->getPluginId(),
        'provider' => $component->getPluginDefinition()['provider'],
        'library' => $component->library,
        'props' => $component->getPluginDefinition()['props'] ?? [],
        'slots' => $component->getPluginDefinition()['slots'] ?? [],
        'count' => 1,
      ];
    }
    else {
      $this->data['components'][$component->getPluginId()]['count']++;
    }
  }

  /**
   * Return the active theme.
   *
   * @return array
   *   The active theme.
   */
  public function getActiveTheme(): array {
    return $this->data['activeTheme'];
  }

  /**
   * Return the theme negotiator.
   *
   * @return array
   *   The theme negotiator.
   */
  public function getThemeNegotiator(): array {
    return $this->data['negotiator'];
  }

  /**
   * Return the time spent by the twig rendering process, in seconds.
   *
   * @return float
   *   The time spent by the twig rendering process, in seconds.
   */
  public function getTime(): float {
    return $this->getProfile()->getDuration() * 1000;
  }

  /**
   * Return the number of twig templates rendered.
   *
   * @return int
   *   The number of twig templates rendered.
   */
  public function getTemplateCount(): int {
    return $this->getComputedData('template_count');
  }

  /**
   * Return the number of twig blocks rendered.
   *
   * @return int
   *   The number of twig blocks rendered.
   */
  public function getBlockCount(): int {
    return $this->getComputedData('block_count');
  }

  /**
   * Return the number of twig macros rendered.
   *
   * @return int
   *   The number of twig macros rendered.
   */
  public function getMacroCount(): int {
    return $this->getComputedData('macro_count');
  }

  /**
   * Return the number of twig filters.
   *
   * @return int
   *   The number of twig filters.
   */
  public function getTwigFiltersCount(): int {
    return count($this->data['twig_extensions']['filters']);
  }

  /**
   * Return the number of twig functions.
   *
   * @return int
   *   The number of twig functions.
   */
  public function getTwigFunctionsCount(): int {
    return count($this->data['twig_extensions']['functions']);
  }

  /**
   * Return TRUE if the SDC module is enabled.
   *
   * @return bool
   *   TRUE if the SDC module is enabled.
   */
  public function hasComponentsModule(): bool {
    return $this->moduleHandler->moduleExists('sdc');
  }

  /**
   * Return the number of SDC components.
   *
   * @return int
   *   The number of SDC components.
   */
  public function getComponentsCount(): int {
    return count($this->data['components']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): array {
    $filters = $this->data['twig_extensions']['filters'];
    ksort($filters);

    $functions = $this->data['twig_extensions']['functions'];
    ksort($functions);

    $tabs = [];

    $tabs[] = [
      'label' => 'Theme data',
      'content' => [
        '#type' => 'inline_template',
        '#template' => '{{ data|raw }}',
        '#context' => [
          'data' => $this->dumpData($this->cloneVar($this->data['activeTheme'])),
        ],
      ],
    ];

    $tabs[] = [
      'label' => 'Twig filters',
      'content' => [
        '#theme' => 'webprofiler_dashboard_section',
        '#data' => [
          '#type' => 'table',
          '#header' => [
            $this->t('Name'),
            $this->t('Callable'),
          ],
          '#rows' => $filters,
          '#attributes' => [
            'class' => [
              'webprofiler__table',
            ],
          ],
          '#sticky' => TRUE,
        ],
      ],
    ];

    $tabs[] = [
      'label' => 'Twig functions',
      'content' => [
        '#theme' => 'webprofiler_dashboard_section',
        '#data' => [
          '#type' => 'table',
          '#header' => [
            $this->t('Name'),
            $this->t('Callable'),
          ],
          '#rows' => $functions,
          '#attributes' => [
            'class' => [
              'webprofiler__table',
            ],
          ],
          '#sticky' => TRUE,
        ],
      ],
    ];

    $tabs[] = [
      'label' => 'Twig globals',
      'content' => [
        '#theme' => 'webprofiler_dashboard_section',
        '#data' => [
          '#type' => 'table',
          '#header' => [
            $this->t('Name'),
            $this->t('Callable'),
          ],
          '#rows' => $this->data['twig_extensions']['globals'],
          '#attributes' => [
            'class' => [
              'webprofiler__table',
            ],
          ],
          '#sticky' => TRUE,
        ],
      ],
    ];

    $tabs[] = [
      'label' => 'Rendering Call Graph',
      'content' => [
        '#theme' => 'webprofiler_dashboard_section',
        '#data' => [
          '#type' => 'inline_template',
          '#template' => '<div id="twig-dump">{{ data|raw }}</div>',
          '#context' => [
            'data' => (string) $this->getHtmlCallGraph(),
          ],
        ],
      ],
    ];

    if ($this->moduleHandler->moduleExists('sdc')) {
      $tabs[] = [
        'label' => 'Components',
        'content' => $this->renderComponents($this->data['components']),
      ];
    }

    return [
      '#theme' => 'webprofiler_dashboard_tabs',
      '#tabs' => $tabs,
    ];
  }

  /**
   * Render the twig call graph.
   *
   * @return \Twig\Markup
   *   The twig call graph.
   */
  private function getHtmlCallGraph(): Markup {
    $dumper = new HtmlDumper();

    return new Markup($dumper->dump($this->getProfile()), 'UTF-8');
  }

  /**
   * Render the SDC components.
   *
   * @param array $components
   *   The SDC components.
   *
   * @return array
   *   The render array of the SDC components.
   */
  private function renderComponents(array $components): array {
    return [
      '#theme' => 'webprofiler_dashboard_components',
      '#components' => $components,
    ];
  }

  /**
   * Return the twig profile, deserialized from data, if needed.
   *
   * @return \Twig\Profiler\Profile
   *   The twig profile, deserialized from data, if needed.
   */
  private function getProfile(): Profile {
    return $this->profile ??= unserialize(
      $this->data['twig'],
      ['allowed_classes' => ['\Twig\Profiler\Profile', Profile::class]],
    );
  }

  /**
   * Return a specific computed data.
   *
   * @param string $index
   *   The index of the data to return.
   *
   * @return mixed
   *   The computed data.
   */
  private function getComputedData(string $index): mixed {
    $this->computed ??= $this->computeData($this->getProfile());

    return $this->computed[$index];
  }

  /**
   * Compute the data from the twig profile.
   *
   * @param \Twig\Profiler\Profile $profile
   *   The twig profile.
   *
   * @return array
   *   The computed data.
   */
  private function computeData(Profile $profile): array {
    $data = [
      'template_count' => 0,
      'block_count' => 0,
      'macro_count' => 0,
    ];

    $templates = [];
    foreach ($profile as $p) {
      $d = $this->computeData($p);

      $data['template_count'] += ($p->isTemplate() ? 1 : 0) + $d['template_count'];
      $data['block_count'] += ($p->isBlock() ? 1 : 0) + $d['block_count'];
      $data['macro_count'] += ($p->isMacro() ? 1 : 0) + $d['macro_count'];

      if ($p->isTemplate()) {
        if (!isset($templates[$p->getTemplate()])) {
          $templates[$p->getTemplate()] = 1;
        }
        else {
          $templates[$p->getTemplate()]++;
        }
      }

      foreach ($d['templates'] as $template => $count) {
        if (!isset($templates[$template])) {
          $templates[$template] = $count;
        }
        else {
          $templates[$template] += $count;
        }
      }
    }
    $data['templates'] = $templates;

    return $data;
  }

}
