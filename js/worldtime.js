(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.initWorldTimeClockWidget = {
    attach: function (context, settings) {
      if (!$.isFunction($.jClocksGMT) || typeof settings.wtcwidget === 'undefined') {
        return;
      }

      $('.wtc-widget', context).once('init-wtc-widget').jClocksGMT(settings.wtcwidget);
    }
  };

})(jQuery, Drupal);
