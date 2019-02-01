<?php

namespace Drupal\worldtime\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'WorldTimeClockWidgetBlock' block.
 *
 * @Block(
 *  id = "world_time_clock_widget_block",
 *  admin_label = @Translation("World Time Clock Widget block"),
 * )
 */
class WorldTimeClockWidgetBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $system_date = \Drupal::config('system.date');
    $default_timezone = $system_date->get('timezone.default') ?: date_default_timezone_get();
    $config = $this->getConfiguration();
    if (!isset($config['locations'])) {
      $config['locations'] = [
        [
          'title' => $default_timezone,
          'timezone' => $default_timezone,
          'timeformat' => WORLDTIME_DEFAULT_TIMEFORMAT,
          'skin' => WORLDTIME_DEFAULT_SKIN,
        ],
      ];
    }

    $form['locations'] = [
      '#tree' => TRUE,
    ];
    $form['locations'][0]['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Location'),
      '#description' => $this->t('Location of a place clock widget'),
      '#default_value' => $config['locations'][0]['title'],
      '#size' => 19,
      '#maxlength' => 19,
    ];

    $form['locations'][0]['timezone'] = [
      '#type' => 'select',
      '#title' => t('Time zone'),
      '#default_value' => $config['locations'][0]['timezone'],
      '#options' => system_time_zones(NULL, TRUE),
    ];

    $form['locations'][0]['timeformat'] = [
      '#type' => 'select',
      '#title' => t('Time format'),
      '#default_value' => $config['locations'][0]['timeformat'],
      '#options' => [
        'HH:mm' => $this->t('24 Hour'),
        'hh:mm' => $this->t('12 Hour'),
      ],
    ];

    $form['locations'][0]['skin'] = [
      '#type' => 'radios',
      '#title' => $this->t('Clock Skin'),
      '#default_value' => $config['locations'][0]['skin'],
      '#options' => [
        1 => $this->t('Skin 1'),
        2 => $this->t('Skin 2'),
        3 => $this->t('Skin 3'),
        4 => $this->t('Skin 4'),
        5 => $this->t('Skin 5'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['locations'] = [];
    foreach ($values['locations'] as $i => $location) {
      $this->configuration['locations'][$i] = $location;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $settings = [];
    foreach ($this->configuration['locations'] as $id => $location) {
      $settings[$id] = $location;
      $settings[$id]['imgpath'] = base_path() . 'libraries/jclocksgmt/';
      $settings[$id]['offset'] = worldtime_get_timezone_offset($location['timezone']);
    }

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
