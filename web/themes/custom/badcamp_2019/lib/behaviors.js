(function(Drupal, $) {

  Drupal.behaviors.foundation = {
    attach:function(context) {
      $(context).foundation();
    }
  };

  Drupal.behaviors.equalheight = {
      attach:function(context) {
          $(document).ready(function(){
          });
      }
  }

  Drupal.behaviors.menuToggle = {
      attach: function(context) {
          $('.responsive-menu-button-wrap').on('toggled.zf.responsiveToggle', function(e) {
              $('.header-parent').toggleClass('opened');
          });
      }
  }

}(Drupal, jQuery));