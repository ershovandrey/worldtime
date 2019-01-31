<?php

namespace Drupal\worldtime\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'WorldTimeClockWidgetBlock' block.
 *
 * @Block(
 *  id = "world_time_clock_widget_block",
 *  admin_label = @Translation("World Time Clock Widget block"),
 * )
 */
class WorldTimeClockWidgetBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $settings = [
      'title' => 'Houston, TX, USA',
      'offset' => -6,
      'skin' => 2
    ];

    return [
      '#markup' => '<div class="wtc-widget" id="worldtimeclockwidget">Test</div>',
      '#attached' => [
        'library' => 'worldtime/worldtime',
        'drupalSettings' => [
          'wtcwidget' => $settings,
        ],
      ],
    ];
  }

}
