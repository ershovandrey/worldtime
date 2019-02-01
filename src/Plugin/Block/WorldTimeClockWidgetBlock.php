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
      'title' => 'Helsinki, Finland',
      'offset' => +2,
      'skin' => 1,
      'imgpath' => base_path() . 'libraries/jclocksgmt/',
    ];

    return [
      '#markup' => '<div class="wtc-widget" id="worldtimeclockwidget"></div>',
      '#attached' => [
        'library' => 'worldtime/worldtime',
        'drupalSettings' => [
          'wtcwidget' => $settings,
        ],
      ],
    ];
  }

}
