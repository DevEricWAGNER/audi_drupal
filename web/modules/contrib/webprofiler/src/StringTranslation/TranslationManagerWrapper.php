<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\StringTranslation;

use Drupal\Core\StringTranslation\TranslationManager;

/**
 * Wrap the string_translation service to collect translation data.
 */
class TranslationManagerWrapper extends TranslationManager {

  /**
   * List of translated strings.
   *
   * @var string[]
   */
  private array $translated = [];

  /**
   * List of untranslated strings.
   *
   * @var string[]
   */
  private array $untranslated = [];

  /**
   * {@inheritdoc}
   */
  protected function doTranslate($string, array $options = []): string {
    // If a NULL langcode has been provided, unset it.
    if (!isset($options['langcode']) && array_key_exists('langcode', $options)) {
      unset($options['langcode']);
    }

    // Merge in options defaults.
    $options = $options + [
      'langcode' => $this->defaultLangcode,
      'context' => '',
    ];
    $translation = $this->getStringTranslation($options['langcode'], $string, $options['context']);

    if ($translation) {
      $this->translated[$string] = $translation;
    }
    else {
      $this->untranslated[$string] = $string;
    }

    return $translation === FALSE ? $string : $translation;
  }

  /**
   * Return the list of translated strings.
   *
   * @return string[]
   *   The list of translated strings.
   */
  public function getTranslated(): array {
    return $this->translated;
  }

  /**
   * Return the list of untranslated strings.
   *
   * @return string[]
   *   The list of untranslated strings.
   */
  public function getUntranslated(): array {
    return $this->untranslated;
  }

}
