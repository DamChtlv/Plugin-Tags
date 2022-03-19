=== Plugin Tags ===
Contributors: damchtlv
Tags: plugin tags, plugin notes, plugin keywords, plugin management
Requires at least: 3.0 or higher
Tested up to: 5.8
Requires PHP: 5.6
Stable tag: 1.2.3
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add tags & filters to the plugins list to quickly & easily see what they do.

== Description ==

Add **tags** & **filters** to the **Plugins list** to quickly & easily see what they do. 🏷

**Few examples** of use:
- **Utilities**: *Admin, SEO, Cache, Pagebuilder...*
- **Project version**: *v1.0, v1.1...*
- **Notes / reminders**: *Unstable, Waiting for hotfix...*

== 🔨 How to use ==

Want to **change a tag text**? ✒
- Click on the text and write directly!

Want to **change a tag color**? 🌈
- Hover the tag and click on the 🖌 icon

Want to **filter your plugins** based on **their tags**? 🔍
- Hover the tag and click on the 📌 icon
- *(it's going to reload the page with the filter added above plugins, click on it again to remove it).*

== ⭐ Features ==

**Colors** 🎨
*(Based on **user preferences** to avoid **"rainbow-effect"**)*

**Filters / Views** 📌
*(To **filter plugins** which have a similar tag)*

**Fast / Lightweight** 🚀
*(When you change a tag text / color, it's **instantly saved** using ajax technology)*


== Frequently Asked Questions ==

= Does this plugin have hooks (filters) ? =

**Yes, there is one filter**: `ptags/option` which contains **all the data saved by the plugin** in an array which is stored **in a single option**.

You can use the code below to preset your favorite configuration *(used "**Hello Dolly**" plugin as example)*:

`

// Change plugin tags config
add_filter( 'ptags/option', 'my_ptags_option' );
function my_ptags_option( $option ) {

    // Get current plugins & tags data
    $plugins = isset( $option['plugins'] ) ? $option['plugins'] : array();
    $tags    = isset( $option['tags'] ) ? $option['tags'] : array();

    // Edit plugins data
    $plugins = wp_parse_args(
        $plugins,
        array(

            // Plugin slug
            'hello-dolly' => array(
                'tag'   => __( 'To delete' ), // Tag text displayed next to the plugin version
                'color' => 1, // User preference schematic colors, from 1 to 4+
            ),

            // ... add more by duplicating lines above

        )
    );

    // Edit tags data
    $tags = wp_parse_args(
        $tags,
        array(

            // Filter text (should be same tag text as above)
            'To delete' => array(
                'view' => 1, // Boolean setting to display filter above plugins list
            ),

            // ... add more by duplicating lines above

        )
    );

    // We merge it with current data
    $new_option = wp_parse_args( array( 'plugins' => $plugins, 'tags' => $tags ), $option );

    // Return the new option
    return $new_option;
}

`

💡 *If you have no idea where to put this code, add it at the end of your `functions.php` which is in your theme folder.*

= Can i customize the look of tags? =

**Yes you can** and it's fairly simple because this plugin CSS stylesheet use **CSS variables**.
Just **add the code below** in a CSS stylesheet loaded in the admin & **customize values** as you pleased:

`

:root {
    --plugin-tag-color: #fff; // Tag text color
    --plugin-tag-pad: 0 7px; // Tag padding
    --plugin-tag-rad: 3px; // Tag border radius
    --plugin-tag-fs: .75rem; // Tag font-size
    --plugin-tag-bg: #bbb; // Tag background color
}

`


== Screenshots ==
1. Display "no tag" as default tag state.
2. When hovering the tag, you can change the color by clicking the 🖌.
3. Change tag text by clicking on it and write, clicking on 📌 add a filter view.
4. Filter view link is added above plugins (ex: "To delete").

== Installation ==

1. Upload the `plugin-tags` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the **Plugins** menu in WordPress

== Changelog ==
= 1.2 =
Updated default style to match WP UI

= 1.1 =
Updated readme

= 1.0 =
* Initial release
