<?php

namespace Drupal\worldtime\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Defines the Clock skin entity.
 *
 * @ConfigEntityType(
 *   id = "clock_skin",
 *   label = @Translation("Clock skin"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\worldtime\ClockSkinListBuilder",
 *     "form" = {
 *       "add" = "Drupal\worldtime\Form\ClockSkinForm",
 *       "edit" = "Drupal\worldtime\Form\ClockSkinForm",
 *       "delete" = "Drupal\worldtime\Form\ClockSkinDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\worldtime\ClockSkinHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\worldtime\ClockSkinAccessControlHandler",
 *   },
 *   config_prefix = "clock_skin",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   list_cache_tags = { "rendered" },
 *   links = {
 *     "canonical" = "/admin/config/user-interface/clock_skin/{clock_skin}",
 *     "add-form" = "/admin/config/user-interface/clock_skin/add",
 *     "edit-form" = "/admin/config/user-interface/clock_skin/{clock_skin}/edit",
 *     "delete-form" = "/admin/config/user-interface/clock_skin/{clock_skin}/delete",
 *     "collection" = "/admin/config/user-interface/clock_skin"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "locked"
 *   }
 * )
 */
class ClockSkin extends ConfigEntityBase implements ClockSkinInterface {

  /**
   * The Clock skin ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Clock skin label.
   *
   * @var string
   */
  protected $label;

  /**
   * The locked status of this date format.
   *
   * @var bool
   */
  protected $locked = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return (bool) $this->locked;
  }

  /**
   * {@inheritdoc}
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    if ($a->isLocked() == $b->isLocked()) {
      $a_label = $a->label();
      $b_label = $b->label();
      return strnatcasecmp($a_label, $b_label);
    }
    return $a->isLocked() ? -1 : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    return ['rendered'];
  }

}
