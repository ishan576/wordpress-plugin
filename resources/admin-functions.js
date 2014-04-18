/**
 * Contains the functionality that will be used for Muut on the admin side.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
jQuery(document).ready( function($) {

  var muut_localized = muut_admin_functions_localized;

  /********************************************/
  /* CODE FOR FORUM PAGE EDITOR FUNCTIONALITY */
  /********************************************/
  // If this is a Muut forum, make sure to show the forum name field and disable the template selector.
  $('#muut_is_forum_true').click( function() {
    if ($('#muut_forum').val() == ''){
      $('#muut_forum').val($('#editable-post-name').text());
    }
    $('#muut_page_forum_settings').show();
    $('#page_template').prop('disabled', 'disabled');
    // Make sure the page template is set as default, even though we are disabling the dropdown.
    $('<input />').attr('type', 'hidden')
      .attr('name', "page_template")
      .attr('value', "default")
      .appendTo('#post');

    $('#page_template option').filter( function() {
        return $(this).val() == 'default';
    }).prop('selected', true);
  });
  if ($('#muut_is_forum_true').is(':checked')) {
    $('#page_template').prop('disabled', 'disabled');
  }

  // If we change it to not being a forum, make sure to hide the name field and re-enable the template selector.
  $( '#muut_is_forum_false').click( function() {
    $('#muut_page_forum_settings').hide();
    $('#page_template').prop('disabled', false);
  });

  // If a given setting is dependent upon another one's value, style/disable or enable it properly.
  // See explanation below these two function declarations.
  $.fn.check_requires_fields = function() {
    var requires_element = $( '#' + this.data('muut_requires') );
    var requires_function = $( this ).data('muut_require_func');
    requires_element.on('change', { parent: requires_element, passed_function: requires_function, current: this }, this.set_requires_fields );
    requires_element.change();
  }

  $.fn.set_requires_fields = function( event ) {
    var passed_function = event.data.passed_function;
    var parent = event.data.parent;
    var current = event.data.current;
    if ( eval( 'parent.' + passed_function ) ) {
      current.removeClass( 'disabled' );
      current.find('input').prop('disabled', false);
    } else {
      current.addClass( 'disabled' );
      current.find('input').prop('disabled', true);
    }
  }

  // Execute the above check.
  // The syntax is to set a tr data-muut_requires attribute to the id of another element in the page.
  // It will run a check that is the function (in string form) stored in the same tr's data-muut_require_func attribute.
  // If true, it enables assigns that tr the class "disabled" and any inputs in that tr are disabled.
  $('body.muut_settings tr[data-muut_requires]').check_requires_fields();

  /********************************************/
  /* CODE FOR CUSTOM NAVIGATION FUNCTIONALITY */
  /********************************************/

  var muut_inserted_header_block_index = 1;
  $('#muut_add_category_header').on('click', function(e) {
    if ( typeof categoryHeaderBlockTemplate === 'string' ) {
      e.stopPropagation();
      var insert_header_replacements = { '%ID%': 'new[' + muut_inserted_header_block_index + ']', '%TITLE%': muut_localized.new_header };
      var insert_block = categoryHeaderBlockTemplate.replace(/%\w+%/g, function(all) {
        return insert_header_replacements[all] || all;
      });
      $('#muut_forum_nav_headers').prepend(insert_block).find('.muut-header-title.x-editable').first().editable('toggle');
      muut_inserted_header_block_index++;
    }
  });

  var muut_inserted_forum_category_index = 1;
  $(document).on('click', '.new_category_for_header', function(e) {
    if ( typeof categoryBlockTemplate === 'string' ) {
      e.stopPropagation();
      var insert_category_replacements = { '%ID%': 'new[' + muut_inserted_forum_category_index + ']', '%TITLE%': '' };
      var insert_block = categoryBlockTemplate.replace(/%\w+%/g, function(all) {
        return insert_category_replacements[all] || all;
      });
      $(this).closest('.muut_forum_header_item').find('.muut_category_list').prepend(insert_block).first().find('.muut-category-title.x-editable').editable('toggle');
      muut_inserted_forum_category_index++;
    }
  });

  // If X-Editable is enabled, make sure the editables are by default done inline.
  //if ( typeof editable !== 'undefined') {
  $.fn.editable.defaults.mode = 'inline';
  $.fn.editable.defaults.showbuttons = false;
  //}
});