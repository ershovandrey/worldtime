<?php

/**
 * @file
 * Worldtime theme functions.
 */

use Drupal\Core\Template\Attribute;

/**
 * Prepares variables for wtcwidget formatter templates.
 *
 * Default template: wtcwidget.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - items: array of Widgets.
 */
function template_preprocess_wtcwidget(array &$variables) {
  foreach ($variables['items'] as $index => &$item) {
    $attributes = [
      'class' => ['wtc-widget'],
      'id' => $index,
    ];
    $item = [
      'attributes' => new Attribute($attributes),
    ];
  }
}
