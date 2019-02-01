(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.initWorldTimeClockWidget = {
    attach: function (context, settings) {
      if (!$.isFunction($.fn.jClocksGMT) || typeof settings.wtcwidget === 'undefined') {
        return;
      }

      $('.wtc-widget', context).once('wtc-widget').each(function (index) {
        if (typeof settings.wtcwidget[index] !== 'undefined') {
          $(this).jClocksGMT(settings.wtcwidget[index]);
        }
      });
    }
  };

})(jQuery, Drupal);
