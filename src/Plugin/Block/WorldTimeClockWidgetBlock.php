<?php

namespace Drupal\worldtime\Plugin\Block;

use Drupal\Component\Datetime\Time;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'WorldTimeClockWidgetBlock' block.
 *
 * @Block(
 *  id = "world_time_clock_widget_block",
 *  admin_label = @Translation("World Time Clock Widget block"),
 * )
 */
class WorldTimeClockWidgetBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Config Service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Date Formatter Service.
   *
   * @var Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Renderer Service.
   *
   * @var Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Time component.
   *
   * @var Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container object.
   * @param array $configuration
   *   Plugin configuration array.
   * @param string $plugin_id
   *   Plugin identifier.
   * @param mixed $plugin_definition
   *   Plugin definition object.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin identifier.
   * @param mixed $plugin_definition
   *   Plugin definition object.
   * @param Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   * @param Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   Date formatter service.
   * @param Drupal\Core\Render\RendererInterface $renderer
   *   Renderer service.
   * @param Drupal\Component\Datetime\Time $time
   *   Time component.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, DateFormatterInterface $dateFormatter, RendererInterface $renderer, Time $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->dateFormatter = $dateFormatter;
    $this->renderer = $renderer;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // If jClocksGMT library is not installed - show the warning to the user.
    if (!worldtime_library_installed()) {
      $this->messenger()->addMessage($this->t('The jClocksGMT library needs to be <a href="@url">downloaded</a> and extracted into the /libraries/jclocksgmt folder in your Drupal installation directory.', ['@url' => 'https://github.com/mcmastermind/jClocksGMT/archive/master.zip']), 'error');
      return $form;
    }

    $config = $this->getConfiguration();

    // Maintain the current number of added locations.
    $locations = $form_state->get('locations');
    if (!$locations) {
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

    if (count($config['locations']) > ($locations + 1)) {
      // Remove last location if it was deleted by user.
      array_pop($this->configuration['locations']);
      $config = $this->getConfiguration();
    }

    $time_options = $this->getTimeOptions();
    $date_options = $this->getDateOptions();
    $timezone_options = system_time_zones(TRUE, TRUE);
    $skins = $this->getSkins();

    // Get default settings for location.
    $settings = $this->configFactory->get('worldtime.settings');
    $defaults = $settings->get('defaults');

    for ($i = 0; $i <= $locations; $i++) {
      if (!isset($config['locations'][$i])) {
        // If location is not exists yet - fill it with defaults.
        $config['locations'][$i] = $defaults;
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
        '#default_value' => $config['locations'][$i]['title'],
        '#size' => 19,
        '#maxlength' => 19,
        '#required' => TRUE,
      ];

      $form['locations'][$i]['timezone'] = [
        '#type' => 'select',
        '#title' => $this->t('Time zone'),
        '#default_value' => $config['locations'][$i]['timezone'],
        '#options' => $timezone_options,
        '#required' => TRUE,
      ];

      $form['locations'][$i]['dst'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable automatic Daylight Savings Time (DST) conversion'),
        '#default_value' => $config['locations'][$i]['dst'],
      ];

      $form['locations'][$i]['digital'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show Digital clock'),
        '#default_value' => $config['locations'][$i]['digital'],
      ];
      $form['locations'][$i]['timeformat'] = [
        '#type' => 'select',
        '#title' => $this->t('Time format'),
        '#default_value' => $config['locations'][$i]['timeformat'],
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
        '#default_value' => $config['locations'][$i]['date'],
      ];
      $form['locations'][$i]['dateformat'] = [
        '#type' => 'select',
        '#title' => $this->t('Time format'),
        '#default_value' => $config['locations'][$i]['dateformat'],
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
        '#default_value' => $config['locations'][$i]['analog'],
      ];
      $form['locations'][$i]['skin'] = [
        '#type' => 'radios',
        '#title' => $this->t('Clock Skin'),
        '#default_value' => $config['locations'][$i]['skin'],
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
      // If there is more than 1 location - show the delete button.
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
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the location form container with updated structure.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['settings']['locations'];
  }

  /**
   * Submit handler for the "add-item" button.
   *
   * Increments the locations counter and causes a rebuild.
   */
  public function addItem(array &$form, FormStateInterface $form_state) {
    $form_state->set('locations', $form_state->get('locations') + 1);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove-item" button.
   *
   * Decrements the locations counter and causes a form rebuild.
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
        // User must select either digital or analog clock or both.
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
      $block_id = 'block-' . str_replace('_', '-', $this->configuration['block_id']);
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

  /**
   * Get list of time formats.
   *
   * @return array
   *   List of time formats.
   */
  private function getTimeOptions() {
    $formats = [
      'HH:mm' => 'H:i',
      'hh:mm A' => 'h:i A',
    ];
    return $this->convertDateTimeFormats($formats);
  }

  /**
   * Get list of date formats.
   *
   * @return array
   *   List of dates formats.
   */
  private function getDateOptions() {
    $formats = [
      'DD.MM.YYYY' => 'd.m.Y',
      'MM.DD.YYYY' => 'm.d.Y',
      'YYYY-MM-DD' => 'Y-m-d',
      'YYYY/MM/DD' => 'Y/m/d',
    ];
    return $this->convertDateTimeFormats($formats);
  }

  /**
   * Convert DateTime format.
   *
   * @param array $formats
   *   List of input and output formats.
   *
   * @return array
   *   List of formatted datetimes.
   */
  private function convertDateTimeFormats(array $formats) {
    $return = [];
    $now = $this->time->getRequestTime();
    foreach ($formats as $input => $output) {
      $return[$input] = $this->dateFormatter->format($now, 'custom', $output);
    }
    return $return;
  }

  /**
   * Get list of skins.
   *
   * @return array
   *   List of skins.
   */
  private function getSkins() {
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
      $skins[$id] = $this->renderer->render($image_variables);
    }
    return $skins;
  }

}
