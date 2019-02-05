<?php

namespace Drupal\worldtime\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Clock skin entities.
 */
interface ClockSkinInterface extends ConfigEntityInterface {

  /**
   * Get unique identifier of Clock Skin.
   *
   * @return string
   *   Skin unique identifier.
   */
  public function getId();

  /**
   * Get label of Clock Skin.
   *
   * @return string
   *   Skin label.
   */
  public function getLabel();

  /**
   * Determines if this clock skin is locked.
   *
   * @return bool
   *   TRUE if the clock skin is locked, FALSE otherwise.
   */
  public function isLocked();

}
