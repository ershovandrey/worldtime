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
      0 => [
        'title' => 'Helsinki, Finland',
        'offset' => +2,
        'timeformat' => 'HH:mm',
        'skin' => 1,
        'imgpath' => base_path() . 'libraries/jclocksgmt/',
      ],
      1 => [
        'title' => 'New York, NY, USA',
        'offset' => -5,
        'skin' => 5,
        'imgpath' => base_path() . 'libraries/jclocksgmt/',
      ],
      2 => [
        'title' => 'London, England',
        'offset' => 0,
        'timeformat' => 'HH:mm',
        'skin' => 4,
        'imgpath' => base_path() . 'libraries/jclocksgmt/',
      ],
    ];

    return [
      '#theme' => 'wtcwidget',
      '#items' => array_keys($settings),
      '#attached' => [
        'library' => 'worldtime/worldtime',
        'drupalSettings' => [
          'wtcwidget' => $settings,
        ],
      ],
    ];
  }

}
