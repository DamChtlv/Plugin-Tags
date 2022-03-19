(function ($) {

    // Events
    $(document).on( 'click', '.plugin-tags .js-change-color', change_tag_color );
    $(document).on( 'click', '.plugin-tags .js-toggle-tag-view', toggle_tag_view );
    $(document).on( 'input', '.plugin-tag', debounce( change_tag_name, 250 ) );

    // Update tag name
    function change_tag_name(event) {

        /** Form Data */
        const $tag = $(event.currentTarget),
            $tag_slug = $tag.parents('[data-slug]'),
            plugin_slug = $tag_slug.data('slug'),
            tag_name = $tag.text(),
            wp_ajax_action = 'ptags_update_tags';

        /** Ajax call */
        $.ajax({
            url:  plugin_tags.ajaxurl,
            type: 'POST',
            data: {
                action: wp_ajax_action,
                plugin_slug: plugin_slug,
                tag_name: tag_name,
            },
            // dataType:      'json',

            /** Loading */
            beforeSend: function() {},

            /** Complete */
            complete: function() {},

            // Reponse
            success: function(response) {

                // Error
                if (!response.success) {

                    // No Error data
                    if (!response.data)
                        return console.log('Server error.');

                    // Error string
                    if ($.type(response.data) === "string")
                        return console.log(response.data);

                    return;
                }

                // Success
                console.log(response.data);
            },

            // Server Error
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("XMLHttpRequest: "   + XMLHttpRequest);
                console.log("Status: "           + textStatus);
                console.log("Erreur: "           + errorThrown);
            }

        });

    }

    // Update color number for tag
    function change_tag_color(event) {

        const $button = $(event.currentTarget),
            $tag = $button.parent(),
            current_color_index = Number($button.attr('data-color'));

        // Define new color index
        let new_color_index = !isNaN(current_color_index) ? current_color_index + 1 : 0;
        if (new_color_index > plugin_tags.color_scheme.length - 1) {
            new_color_index = 0;
        }

        const color = plugin_tags.color_scheme[ new_color_index ];

        // Set color index
        $button.attr('data-color', new_color_index);

        // Set color
        $tag[0].style.setProperty( '--plugin-tag-bg', color );

        /** Form Data */
        const $tag_slug = $tag.parents('[data-slug]'),
            plugin_slug = $tag_slug.data('slug'),
            wp_ajax_action = 'ptags_update_tags';

        /** Ajax call */
        $.ajax({
            url:  plugin_tags.ajaxurl,
            type: 'POST',
            data: {
                action: wp_ajax_action,
                plugin_slug: plugin_slug,
                tag_color: new_color_index,
            },
            dataType: 'json',

            /** Loading */
            beforeSend: function() {},

            /** Complete */
            complete: function() {},

            // Reponse
            success: function(response) {

                // Error
                if (!response.success) {

                    // No Error data
                    if (!response.data)
                        return console.log('Server error.');

                    // Error string
                    if ($.type(response.data) === "string")
                        return console.log(response.data);

                    return;
                }

                // Success
                console.log(response.data);
            },

            // Server Error
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("XMLHttpRequest: "   + XMLHttpRequest);
                console.log("Status: "           + textStatus);
                console.log("Erreur: "           + errorThrown);
            }

        });

    }

    // Update tag view
    function toggle_tag_view(event) {

        const $button = $(event.currentTarget),
            $tag = $button.parent(),
            tag_name = $tag.text();

        /** Form Data */
        const $tag_slug = $tag.parents('[data-slug]'),
            plugin_slug = $tag_slug.data('slug'),
            wp_ajax_action = 'ptags_update_tags';

        /** Ajax call */
        $.ajax({
            url:  plugin_tags.ajaxurl,
            type: 'POST',
            data: {
                action: wp_ajax_action,
                plugin_slug: plugin_slug,
                tag_view: tag_name,
            },
            dataType: 'json',

            /** Loading */
            beforeSend: function() {},

            /** Complete */
            complete: function() {},

            // Reponse
            success: function(response) {

                // Error
                if (!response.success) {

                    // No Error data
                    if (!response.data)
                        return console.log('Server error.');

                    // Error string
                    if ($.type(response.data) === "string")
                        return console.log(response.data);

                    return;
                }

                // Success
                window.location.reload();
            },

            // Server Error
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("XMLHttpRequest: "   + XMLHttpRequest);
                console.log("Status: "           + textStatus);
                console.log("Erreur: "           + errorThrown);
            }

        });

    }

    // Helpers
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

})(jQuery);
