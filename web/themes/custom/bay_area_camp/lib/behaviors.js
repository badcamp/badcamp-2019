(function(Drupal, $) {

  Drupal.behaviors.foundation = {
    attach:function(context) {
      $(context).foundation();
    }
  };

  Drupal.behaviors.badAreaTabs = {
    attach: function (context, settings) {
      $('.tabs-trigger').click(function(e){
        e.preventDefault();
        $('.tabs-list').parent().toggleClass('open');
        $('.fa', this).toggleClass('fa-cog');
        $('.fa', this).toggleClass('fa-close');
      });
    }
  };

}(Drupal, jQuery));