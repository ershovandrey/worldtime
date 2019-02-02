(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.initWorldTimeClockWidget = {
    attach: function (context, settings) {
      if (!$.isFunction($.fn.jClocksGMT) || typeof settings.wtcwidget === 'undefined') {
        return;
      }

      $('.wtc-widget', context).once('wtc-widget').each(function () {
        var parent_id = $(this).closest('.block-worldtime').prop('id');
        var widget_id = $(this).prop('id');
        if (typeof settings.wtcwidget[parent_id][widget_id] !== 'undefined') {
          $(this).jClocksGMT(settings.wtcwidget[parent_id][widget_id]);
        }
      });
    }
  };

})(jQuery, Drupal);
