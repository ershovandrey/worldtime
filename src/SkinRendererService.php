<?php

namespace Drupal\worldtime;
use Drupal\Core\Render\RendererInterface;
use Drupal\worldtime\Entity\ClockSkinInterface;

/**
 * Class SkinRendererService.
 */
class SkinRendererService implements SkinRendererServiceInterface {

  /**
   * Drupal\Core\Render\RendererInterface definition.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * SkinRendererService constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function renderSkin(ClockSkinInterface $skin) {
    $uri = base_path() . 'libraries/jclocksgmt/images/jcgmt-' . $skin->getId() . '-clock_face.png';
    if (file_exists(DRUPAL_ROOT . $uri)) {
      $label = $skin->getLabel();
      $image_variables = [
        '#theme' => 'image',
        '#uri' => $uri,
        '#alt' => $label,
        '#title' => $label,
        '#width' => 100,
        '#height' => 100,
      ];
      return $this->renderer->render($image_variables);
    }
    return NULL;
  }

}
