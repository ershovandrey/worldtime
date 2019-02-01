(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.initWorldTimeClockWidget = {
    attach: function (context, settings) {
      if (!$.isFunction($.fn.jClocksGMT) || typeof settings.wtcwidget === 'undefined') {
        return;
      }

      $('.wtc-widget', context).once('init-wtc-widget').jClocksGMT(settings.wtcwidget);
    }
  };

})(jQuery, Drupal);
