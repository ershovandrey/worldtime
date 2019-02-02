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
    if (!worldtime_library_installed()) {
      $this->messenger()->addMessage($this->t('The jClocksGMT library needs to be <a href="@url">downloaded</a> and extracted into the /libraries/jclocksgmt folder in your Drupal installation directory.', ['@url' => 'https://github.com/mcmastermind/jClocksGMT/archive/master.zip']), 'error');
      return $form;
    }

    $config = $this->getConfiguration();
    if (!$form_state->get('locations')) {
      $locations = isset($config['locations']) ? count($config['locations']) - 1 : 0;
      $form_state->set('locations', $locations);
    }

    $now = \Drupal::time()->getRequestTime();
    $time_options = [
      'HH:mm' => \Drupal::service('date.formatter')->format($now, 'custom', 'H:i'),
      'hh:mm A' => \Drupal::service('date.formatter')->format($now, 'custom', 'h:i A'),
    ];

    $date_options = [
      'DD.MM.YYYY' => \Drupal::service('date.formatter')->format($now, 'custom', 'd.m.Y'),
      'MM.DD.YYYY' => \Drupal::service('date.formatter')->format($now, 'custom', 'm.d.Y'),
      'YYYY-MM-DD' => \Drupal::service('date.formatter')->format($now, 'custom', 'Y-m-d'),
      'YYYY/MM/DD' => \Drupal::service('date.formatter')->format($now, 'custom', 'Y/m/d'),
    ];

    $skins = [];
    foreach (range(1, 5) as $id) {
      $label = $this->t('Skin @id', ['@id' => $id]);
      $image_variables = [
        '#theme' => 'image',
        '#uri' => base_path() . 'libraries/jclocksgmt/images/jcgmt-' . $id . '-clock_face.png',
        '#alt' => $label,
        '#title' => $label,
        '#width' => 100,
        '#height' => 100,
      ];
      $skins[$id] = \Drupal::service('renderer')->render($image_variables);
    }

    $form['locations'] = [
      '#tree' => TRUE,
      '#type' => 'container',
      '#attributes' => [
        'id' => 'locations-container',
      ],
    ];

    $settings = \Drupal::config('worldtime.settings');

    $locations = $form_state->get('locations');
    for ($i = 0; $i <= $locations; $i++) {
      $title = $settings->get('defaults.title');
      $timezone = $settings->get('defaults.timezone');
      $dst = $settings->get('defaults.dst');
      $digital = $settings->get('defaults.digital');
      $timeformat = $settings->get('defaults.timeformat');
      $date = $settings->get('defaults.date');
      $dateformat = $settings->get('defaults.dateformat');
      $analog = $settings->get('defaults.analog');
      $skin = $settings->get('defaults.skin');

      if (isset($config['locations']) && isset($config['locations'][$i])) {
        $title = $config['locations'][$i]['title'];
        $timezone = $config['locations'][$i]['timezone'];
        $dst = $config['locations'][$i]['dst'];
        $digital = $config['locations'][$i]['digital'];
        $timeformat = $config['locations'][$i]['timeformat'];
        $date = $config['locations'][$i]['date'];
        $dateformat = $config['locations'][$i]['dateformat'];
        $analog = $config['locations'][$i]['analog'];
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

      $form['locations'][$i]['dst'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable automatic Daylight Savings Time (DST) conversion'),
        '#default_value' => $dst,
      ];

      $form['locations'][$i]['digital'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show Digital clock'),
        '#default_value' => $digital,
      ];
      $form['locations'][$i]['timeformat'] = [
        '#type' => 'select',
        '#title' => t('Time format'),
        '#default_value' => $timeformat,
        '#options' => $time_options,
        '#states' => [
          'visible' => [
            ':input[name="settings[locations][' . $i . '][digital]"]' => [
              'checked' => TRUE,
            ],
          ],
        ],
      ];

      $form['locations'][$i]['date'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show current date'),
        '#default_value' => $date,
      ];
      $form['locations'][$i]['dateformat'] = [
        '#type' => 'select',
        '#title' => t('Time format'),
        '#default_value' => $dateformat,
        '#options' => $date_options,
        '#states' => [
          'visible' => [
            ':input[name="settings[locations][' . $i . '][date]"]' => [
              'checked' => TRUE,
            ],
          ],
        ],
      ];

      $form['locations'][$i]['analog'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show Analog clock'),
        '#default_value' => $analog,
      ];
      $form['locations'][$i]['skin'] = [
        '#type' => 'radios',
        '#title' => $this->t('Clock Skin'),
        '#default_value' => $skin,
        '#options' => $skins,
        '#attributes' => [
          'class' => [
            'container-inline',
          ],
        ],
        '#states' => [
          'visible' => [
            ':input[name="settings[locations][' . $i . '][analog]"]' => [
              'checked' => TRUE,
            ],
          ],
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
  public function blockValidate($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    unset($values['locations']['actions']);
    foreach ($values['locations'] as $i => $location) {
      if (!$location['digital'] && !$location['analog']) {
        $form_state->setErrorByName('locations][' . $i . '][title', $this->t('You must select either digital or analog clock.'));
      }
    }
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

    // Store block id separately as workaround for multiple blocks on same page.
    $block_id = $form['id']['#value'];
    $this->configuration['block_id'] = $block_id;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $settings = $items = [];
    foreach ($this->configuration['locations'] as $id => $location) {
      $block_id = 'block-' . $this->configuration['block_id'];
      $widget_id = $block_id . '-wtc-widget-' . $id;
      $settings[$block_id][$widget_id] = $location;
      $settings[$block_id][$widget_id]['imgpath'] = base_path() . 'libraries/jclocksgmt/';
      $settings[$block_id][$widget_id]['offset'] = worldtime_get_timezone_offset($location['timezone']);
      $items[$widget_id] = [];
    }

    return [
      '#theme' => 'wtcwidget',
      '#items' => $items,
      '#attached' => [
        'library' => 'worldtime/worldtime',
        'drupalSettings' => [
          'wtcwidget' => $settings,
        ],
      ],
    ];
  }

}
