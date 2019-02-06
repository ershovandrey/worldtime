<?php

namespace Drupal\worldtime;

use Drupal\worldtime\Entity\ClockSkinInterface;

/**
 * Interface SkinRendererServiceInterface.
 */
interface SkinRendererServiceInterface {

  /**
   * Render Clock Skin as Image.
   *
   * @param \Drupal\worldtime\Entity\ClockSkinInterface $skin
   *   Clock skin configuration entity.
   *
   * @return \Drupal\Component\Render\MarkupInterface|null
   *   Rendered image or null.
   */
  public function renderSkin(ClockSkinInterface $skin);

}
