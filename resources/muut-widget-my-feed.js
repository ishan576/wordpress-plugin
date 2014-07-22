/**
 * Contains the objects that are used for Muut Online Users widget.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */

jQuery(document).ready(function($) {

  // Once Muut is loaded...
  muutObj().on('load', function() {
    // Functionality for the My Feed widget.
    var widget_my_feed_wrapper = $('#muut-widget-my-feed-wrapper');

    // IF THE MY FEED WIDGET IS CURRENTLY ACTIVE AND BEING DISPLAYED.
    if (widget_my_feed_wrapper.length > 0) {

      // When our custom "Login" link is clicked, execute the click event for the Muut login item to begin login.
      widget_my_feed_wrapper.find('.muut_login').on('click', function(e) {
        e.preventDefault();
        $('.m-login').click();
      });

      // Listen for the websocket events for login and logout to reload/reset the widget on login and logout.
      muutObj().user.on('login', function() {
        muutObj().load('feed');
      });
      muutObj().user.on('logout', function() {
        widget_my_feed_wrapper.find('.muut_login').show();
      });

      // If the user is logged in, hide the login link.
      if (widget_my_feed_wrapper.find('.m-logged').length > 0 ) {
        widget_my_feed_wrapper.find('.muut_login').hide();
      }
    }
  });
});