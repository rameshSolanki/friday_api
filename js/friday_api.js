(function ($, Drupal, drupalSettings) {
  let records = null;

  Drupal.behaviors.friday_api = {
    attach: function(context, settings) {
      records = settings.records;
	  //alert (records);
    }
  }
})(jQuery, Drupal, drupalSettings);
