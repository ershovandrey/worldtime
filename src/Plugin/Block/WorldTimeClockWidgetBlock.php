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

    $config = $this->getConfiguration();
    if (!$form_state->get('locations')) {
      $locations = isset($config['locations']) ? count($config['locations']) - 1 : 0;
      $form_state->set('locations', $locations);
    }

    $form['locations'] = [
      '#tree' => TRUE,
      '#type' => 'container',
      '#attributes' => [
        'id' => 'locations-container',
      ],
    ];
    $locations = $form_state->get('locations');
    for ($i = 0; $i <= $locations; $i++) {
      $title = '';
      $timezone = '';
      $timeformat = WORLDTIME_DEFAULT_TIMEFORMAT;
      $skin = WORLDTIME_DEFAULT_SKIN;
      if (isset($config['locations']) && isset($config['locations'][$i])) {
        $title = $config['locations'][$i]['title'];
        $timezone = $config['locations'][$i]['timezone'];
        $timeformat = $config['locations'][$i]['timeformat'];
        $skin = $config['locations'][$i]['skin'];
      }
      $form['locations'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Location #@number', ['@number' => $i + 1]),
        '#collapsible' => FALSE,
        '#collapsed' => FALSE,
      ];
      $form['locations'][$i]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('City or Location'),
        '#default_value' => $title,
        '#size' => 19,
        '#maxlength' => 19,
        '#required' => TRUE,
      ];

      $form['locations'][$i]['timezone'] = [
        '#type' => 'select',
        '#title' => t('Time zone'),
        '#default_value' => $timezone,
        '#options' => system_time_zones(TRUE, TRUE),
        '#required' => TRUE,
      ];

      $form['locations'][$i]['timeformat'] = [
        '#type' => 'select',
        '#title' => t('Time format'),
        '#default_value' => $timeformat,
        '#options' => [
          'HH:mm' => $this->t('24 Hour'),
          'hh:mm A' => $this->t('12 Hour'),
        ],
      ];

      $form['locations'][$i]['skin'] = [
        '#type' => 'radios',
        '#title' => $this->t('Clock Skin'),
        '#default_value' => $skin,
        '#options' => [
          1 => $this->t('Skin 1'),
          2 => $this->t('Skin 2'),
          3 => $this->t('Skin 3'),
          4 => $this->t('Skin 4'),
          5 => $this->t('Skin 5'),
        ],
      ];

    }

    // Disable caching on this form.
    $form_state->setCached(FALSE);
    $form['locations']['actions'] = [
      '#type' => 'actions',
    ];

    $form['locations']['actions']['add_item'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add location #@number', ['@number' => $i + 1]),
      '#submit' => [[$this, 'addItem']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [$this, 'ajaxCallback'],
        'wrapper' => 'locations-container',
      ],
    ];
    if ($locations > 0) {
      $form['locations']['actions']['remove_item'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove location #@number', ['@number' => $i]),
        '#submit' => [[$this, 'removeItem']],
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => [$this, 'ajaxCallback'],
          'wrapper' => 'locations-container',
        ],
      ];
    }

    return $form;
  }

  /**
   * AJAX callback handler.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['settings']['locations'];
  }

  /**
   * AJAX submit handler for adding item.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function addItem(array &$form, FormStateInterface $form_state) {
    $form_state->set('locations', $form_state->get('locations') + 1);
    $form_state->setRebuild();
  }

  /**
   * AJAX submit handler for removing item.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function removeItem(array &$form, FormStateInterface $form_state) {
    if ($form_state->get('locations') > 0) {
      $form_state->set('locations', $form_state->get('locations') - 1);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['locations']['actions']);
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
