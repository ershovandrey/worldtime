<?php

namespace Drupal\worldtime\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ClockSkinForm.
 */
class ClockSkinForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $clock_skin = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $clock_skin->label(),
      '#description' => $this->t("Label for the Clock skin."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $clock_skin->id(),
      '#machine_name' => [
        'exists' => '\Drupal\worldtime\Entity\ClockSkin::load',
      ],
      '#disabled' => !$clock_skin->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $clock_skin = $this->entity;
    $status = $clock_skin->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Clock skin.', [
          '%label' => $clock_skin->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Clock skin.', [
          '%label' => $clock_skin->label(),
        ]));
    }
    $form_state->setRedirectUrl($clock_skin->toUrl('collection'));
  }

}
