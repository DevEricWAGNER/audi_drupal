<?php

declare(strict_types = 1);

namespace Drupal\webprofiler\Render;

use Drupal\big_pipe\Render\BigPipe;
use Drupal\big_pipe\Render\BigPipeMarkup;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Render\HtmlResponse;

/**
 * Extends the Drupal core big_pipe service to trace placeholder expansion.
 */
class TraceableBigPipe extends BigPipe {

  /**
   * {@inheritdoc}
   */
  protected function sendPlaceholders(array $placeholders, array $placeholder_order, AttachedAssetsInterface $cumulative_assets) {
    // Return early if there are no BigPipe placeholders to send.
    if (empty($placeholders)) {
      return;
    }

    // Send the start signal.
    $this->sendChunk("\n" . static::START_SIGNAL . "\n");

    // A BigPipe response consists of an HTML response plus multiple embedded
    // AJAX responses. To process the attachments of those AJAX responses, we
    // need a fake request that is identical to the main request, but with
    // one change: it must have the right Accept header, otherwise the work-
    // around for a bug in IE9 will cause not JSON, but <textarea>-wrapped JSON
    // to be returned.
    // @see \Drupal\Core\EventSubscriber\AjaxResponseSubscriber::onResponse()
    $fake_request = $this->requestStack->getMainRequest()->duplicate();
    $fake_request->headers->set('Accept', 'application/vnd.drupal-ajax');

    foreach ($placeholder_order as $placeholder_id) {
      if (!isset($placeholders[$placeholder_id])) {
        continue;
      }

      // Render the placeholder.
      $placeholder_render_array = $placeholders[$placeholder_id];
      try {
        $elements = $this->renderPlaceholder($placeholder_id, $placeholder_render_array);
      }
      catch (\Exception $e) {
        if ($this->configFactory->get('system.logging')
          ->get('error_level') === ERROR_REPORTING_DISPLAY_VERBOSE) {
          throw $e;
        }
        else {
          trigger_error($e, E_USER_ERROR);
          continue;
        }
      }

      // Create a new AjaxResponse.
      $ajax_response = new AjaxResponse();
      // JavaScript's querySelector automatically decodes HTML entities in
      // attributes, so we must decode the entities of the current BigPipe
      // placeholder ID (which has HTML entities encoded since we use it to find
      // the placeholders).
      $big_pipe_js_placeholder_id = Html::decodeEntities($placeholder_id);
      $ajax_response->addCommand(new ReplaceCommand(sprintf('[data-big-pipe-placeholder-id="%s"]', $big_pipe_js_placeholder_id), $elements['#markup']));
      $ajax_response->setAttachments($elements['#attached']);
      $ajax_response->headers->set('X-Drupal-BigPipe-Placeholder', $placeholder_id);

      // Push a fake request with the asset libraries loaded so far and dispatch
      // KernelEvents::RESPONSE event. This results in the attachments for the
      // AJAX response being processed by AjaxResponseAttachmentsProcessor and
      // hence:
      // - the necessary AJAX commands to load the necessary missing asset
      //   libraries and updated AJAX page state are added to the AJAX response
      // - the attachments associated with the response are finalized, which
      //   allows us to track the total set of asset libraries sent in the
      //   initial HTML response plus all embedded AJAX responses sent so far.
      $fake_request->request->set('ajax_page_state', ['libraries' => implode(',', $cumulative_assets->getAlreadyLoadedLibraries())] + $cumulative_assets->getSettings()['ajaxPageState']);
      try {
        $ajax_response = $this->filterEmbeddedResponse($fake_request, $ajax_response);
      }
      catch (\Exception $e) {
        if ($this->configFactory->get('system.logging')
          ->get('error_level') === ERROR_REPORTING_DISPLAY_VERBOSE) {
          throw $e;
        }
        else {
          trigger_error($e, E_USER_ERROR);
          continue;
        }
      }

      // Send this embedded AJAX response.
      $json = $ajax_response->getContent();
      $output = <<<EOF
    <script type="application/vnd.drupal-ajax" data-big-pipe-replacement-for-placeholder-with-id="$placeholder_id">
    $json
    </script>
EOF;
      $this->sendChunk($output);

      // Another placeholder was rendered and sent, track the set of asset
      // libraries sent so far. Any new settings are already sent; we don't need
      // to track those.
      if (isset($ajax_response->getAttachments()['drupalSettings']['ajaxPageState']['libraries'])) {
        $cumulative_assets->setAlreadyLoadedLibraries(explode(',', $ajax_response->getAttachments()['drupalSettings']['ajaxPageState']['libraries']));
      }
    }

    // Send the stop signal.
    $this->sendChunk("\n" . static::STOP_SIGNAL . "\n");
  }

  /**
   * {@inheritdoc}
   */
  protected function sendNoJsPlaceholders($html, $no_js_placeholders, AttachedAssetsInterface $cumulative_assets) {
    // Split the HTML on every no-JS placeholder string.
    $placeholder_strings = array_keys($no_js_placeholders);
    $fragments = static::splitHtmlOnPlaceholders($html, $placeholder_strings);

    // Determine how many occurrences there are of each no-JS placeholder.
    $placeholder_occurrences = array_count_values(array_intersect($fragments, $placeholder_strings));

    // Set up a variable to store the content of placeholders that have multiple
    // occurrences.
    $multi_occurrence_placeholders_content = [];

    foreach ($fragments as $fragment) {
      // If the fragment isn't one of the no-JS placeholders, it is the HTML in
      // between placeholders and it must be printed & flushed immediately. The
      // rest of the logic in the loop handles the placeholders.
      if (!isset($no_js_placeholders[$fragment])) {
        $this->sendChunk($fragment);
        continue;
      }

      // If there are multiple occurrences of this particular placeholder, and
      // this is the second occurrence, we can skip all calculations and just
      // send the same content.
      if ($placeholder_occurrences[$fragment] > 1 && isset($multi_occurrence_placeholders_content[$fragment])) {
        $this->sendChunk($multi_occurrence_placeholders_content[$fragment]);
        continue;
      }

      $placeholder = $fragment;
      assert(isset($no_js_placeholders[$placeholder]));
      $token = Crypt::randomBytesBase64(55);

      // Render the placeholder, but include the cumulative settings assets, so
      // we can calculate the overall settings for the entire page.
      $placeholder_plus_cumulative_settings = [
        'placeholder' => $no_js_placeholders[$placeholder],
        'cumulative_settings_' . $token => [
          '#attached' => [
            'drupalSettings' => $cumulative_assets->getSettings(),
          ],
        ],
      ];
      try {
        $elements = $this->renderPlaceholder($placeholder, $placeholder_plus_cumulative_settings);
      }
      catch (\Exception $e) {
        if ($this->configFactory->get('system.logging')
          ->get('error_level') === ERROR_REPORTING_DISPLAY_VERBOSE) {
          throw $e;
        }
        else {
          trigger_error($e, E_USER_ERROR);
          continue;
        }
      }

      // Create a new HtmlResponse. Ensure the CSS and (non-bottom) JS is sent
      // before the HTML they're associated with. In other words: ensure the
      // critical assets for this placeholder's markup are loaded first.
      // @see \Drupal\Core\Render\HtmlResponseSubscriber
      // @see template_preprocess_html()
      $css_placeholder = '<nojs-bigpipe-placeholder-styles-placeholder token="' . $token . '">';
      $js_placeholder = '<nojs-bigpipe-placeholder-scripts-placeholder token="' . $token . '">';
      $elements['#markup'] = BigPipeMarkup::create($css_placeholder . $js_placeholder . (string) $elements['#markup']);
      $elements['#attached']['html_response_attachment_placeholders']['styles'] = $css_placeholder;
      $elements['#attached']['html_response_attachment_placeholders']['scripts'] = $js_placeholder;

      $html_response = new HtmlResponse();
      $html_response->setContent($elements);
      $html_response->getCacheableMetadata()->setCacheMaxAge(0);
      $html_response->headers->set('X-Drupal-BigPipe-Placeholder', $placeholder);

      // Push a fake request with the asset libraries loaded so far and dispatch
      // KernelEvents::RESPONSE event. This results in the attachments for the
      // HTML response being processed by HtmlResponseAttachmentsProcessor and
      // hence:
      // - the HTML to load the CSS can be rendered.
      // - the HTML to load the JS (at the top) can be rendered.
      $fake_request = $this->requestStack->getMainRequest()->duplicate();
      $fake_request->request->set('ajax_page_state', ['libraries' => implode(',', $cumulative_assets->getAlreadyLoadedLibraries())]);
      try {
        $html_response = $this->filterEmbeddedResponse($fake_request, $html_response);
      }
      catch (\Exception $e) {
        if ($this->configFactory->get('system.logging')
          ->get('error_level') === ERROR_REPORTING_DISPLAY_VERBOSE) {
          throw $e;
        }
        else {
          trigger_error($e, E_USER_ERROR);
          continue;
        }
      }

      // Send this embedded HTML response.
      $this->sendChunk($html_response);

      // Another placeholder was rendered and sent, track the set of asset
      // libraries sent so far. Any new settings also need to be tracked, so
      // they can be sent in ::sendPreBody().
      $cumulative_assets->setAlreadyLoadedLibraries(array_merge($cumulative_assets->getAlreadyLoadedLibraries(), $html_response->getAttachments()['library']));
      $cumulative_assets->setSettings($html_response->getAttachments()['drupalSettings']);

      // If there are multiple occurrences of this particular placeholder, track
      // the content that was sent, so we can skip all calculations for the next
      // occurrence.
      if ($placeholder_occurrences[$fragment] > 1) {
        $multi_occurrence_placeholders_content[$fragment] = $html_response->getContent();
      }
    }
  }

  /**
   * Splits an HTML string into fragments.
   *
   * Creates an array of HTML fragments, separated by placeholders. The result
   * includes the placeholders themselves. The original order is respected.
   *
   * @param string $html_string
   *   The HTML to split.
   * @param string[] $html_placeholders
   *   The HTML placeholders to split on.
   *
   * @return string[]
   *   The resulting HTML fragments.
   */
  private static function splitHtmlOnPlaceholders($html_string, array $html_placeholders): array {
    $prepare_for_preg_split = function ($placeholder_string) {
      return '(' . preg_quote($placeholder_string, '/') . ')';
    };
    $preg_placeholder_strings = array_map($prepare_for_preg_split, $html_placeholders);
    $pattern = '/' . implode('|', $preg_placeholder_strings) . '/';
    if (strlen($pattern) < 31000) {
      // Only small (<31K characters) patterns can be handled by preg_split().
      $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
      $result = preg_split($pattern, $html_string, 0, $flags);
    }
    else {
      // For large amounts of placeholders we use a simpler but slower approach.
      foreach ($html_placeholders as $placeholder) {
        $html_string = str_replace($placeholder, "\x1F" . $placeholder . "\x1F", $html_string);
      }
      $result = array_filter(explode("\x1F", $html_string));
    }
    return $result;
  }

}
