/**
 * Contains the functionality that will be used for Muut on the frontend
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
jQuery(document).ready( function($) {
  var __muut_frontend_strings = muut_frontend_functions_localized;

  // Adds the comments navigation link to the forum navigation.
  var body = $('body');
  if ( body.hasClass('muut-forum-home') && !body.hasClass('muut-custom-nav') && typeof muut_show_comments_in_nav != 'undefined' && muut_show_comments_in_nav ) {
    // Make sure the title of the comments page is "Comments".
    muutObj().on( 'load', function(page) {
      var comments_link_class = "unlisted ";
      if (typeof( muut_comments_base_domain ) == 'string' && page.relativePath == '/' + muut_comments_base_domain) {
        page.title = "Comments";
        var comments_link_class = "m-selected";
      }
      if ($('#muut_site_comments_nav').length == 0) {
        $(".m-forums").append('<p><a id="muut_site_comments_nav" href="#!/' + muut_comments_base_domain + '" title="' + __muut_frontend_strings.comments + '" data-channel="' + __muut_frontend_strings.comments + '"  class="' + comments_link_class + '">' + __muut_frontend_strings.comments + '</a></p>');
      }
    });
  }

  $.fn.extend({
    facelinkinit: function() {
      var facelinks = $(this).find('.m-facelink');
      $.each(facelinks, function() {
        if ( !$(this).hasClass('m-facelink-inited') ) {
          $(this).tooltip2({prefix: 'm-', delayIn: 0, delayOut: 0});
          if($(this).hasClass('m-is-admin')) {
            $(this).find(".m-tooltip").append("<em> (" + __muut_frontend_strings.admin + ")</em>");
          }
          $(this).on('click', function(e) {
            e.preventDefault();
            var el = $(this);
            var page = el.data('href').substr(2);
            muutObj().load(page);
          });
          $(this).addClass('m-facelink-inited');
        }
      });
    }
  });
});

// Function that contains the template for avatars.
var get_user_avatar_html = function(user) {
  var is_admin_class = '';
  if(user.is_admin) {
    is_admin_class = 'm-is-admin ';
  }

  // Construct the actual username without the '@'.
  if(user.path.substr(0,1) == '@') {
    var username = user.path.substr(1);
  }

  var username_for_class = username.replace(' ', '_');
  var online_user_href_markup = '';
  if ( typeof muut_forum_page_permalink == 'string' ) {
    online_user_href_markup = 'href="' + muut_forum_page_permalink + '#!/' + user.path + '"';
  }
  // Return the HTML for the face.
  var html = '<a class="m-facelink ' + is_admin_class + 'm-online m-user-online_' + username_for_class +'" title="' + user.displayname + '" ' + online_user_href_markup + ' data-href="#!/' + user.path + '"><img class="m-face" src="' + user.img + '"></a>';
  return html;
};