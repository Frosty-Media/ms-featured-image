( function ($) {

  "use strict";

  // WP 3.5+ uploader
  $(function () {
    var file_frame;
    window.formfield = '';

    $(document.body).on('click', 'input[type="button"].button.wpsf-browse', function (e) {

      e.preventDefault();

      var button = $(this);

      window.formfield = $(this).closest('td');

      // If the media frame already exists, reopen it.
      if (file_frame) {
        file_frame.open();
        return;
      }

      // Create the media frame.
      file_frame = wp.media.frames.file_frame = wp.media({
        frame: 'post',
        state: 'insert',
        title: button.data('uploader_title'),
        button: {
          text: button.data('uploader_button_text')
        },
        library: {
          type: 'image'
        },
        multiple: false  // Set to true to allow multiple files to be selected
      });

      file_frame.on('menu:render:default', function (view) {
        // Store our views in an object.
        var views = {};

        // Unset default menu items
        view.unset('library-separator');
        view.unset('gallery');
        view.unset('featured-image');
        view.unset('embed');

        // Initialize the views in our view object.
        view.set(views);
      });

      // When an image is selected, run a callback.
      file_frame.on('insert', function () {

        var attachment = file_frame.state().get('selection').first().toJSON();
        window.formfield.find('input[type="text"]').val(attachment.url);
      });

      // Finally, open the modal
      file_frame.open();
    });

    // WP 3.5+ uploader
    window.formfield = '';

    $('input[type="button"].button.wpsf-clear').on('click', function (e) {
      e.preventDefault();
      $(this).closest('td').find('input[type="text"]').val('');
    });

    // Wraoper fix 3.8
    $('div#ms_featured_image form > table.form-table').wrap('<div class="inside"/>')

  });//function()

}(jQuery));