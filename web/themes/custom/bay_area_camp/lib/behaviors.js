(function(Drupal, $) {
  Drupal.behaviors.foundation = {
    attach: function(context) {
      $(context).foundation();
    },
  };

  // Make article teasers look correct while logged in as a site editor.
  Drupal.behaviors.articleTeaser = {
    attach: function(context, settings) {
      $(context)
        .find('.article__listing')
        .each(function() {
          var contextEdit = $('.contextual-region.article__display--teaser');
          var row = $('.views-row');
          if (contextEdit.length) {
            row.css('padding', '0');
          }
        });
    },
  };
})(Drupal, jQuery);
