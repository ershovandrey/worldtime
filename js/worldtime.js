(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.initWorldTimeClockWidget = {
    attach: function (context, settings) {
      if (!$.isFunction($.fn.jClocksGMT) || typeof settings.wtcwidget === 'undefined') {
        return;
      }

      $('.wtc-widget', context).each(function (index) {
        if (typeof settings.wtcwidget[index] !== 'undefined') {
          $(this).once('init-wtc-widget').jClocksGMT(settings.wtcwidget[index]);
        }
      });
    }
  };

})(jQuery, Drupal);
