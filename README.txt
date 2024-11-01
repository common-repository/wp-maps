===  Wp Maps ===
Contributors: mimothemes
Donate link: http://mimo.studio/
Tags: google map,google maps,easy google maps,google maps locations,google map shortcode,map shortcode,address,cross-browser,custom google map,custom google maps,easy map,geo,geocoder,gmaps, google,google earth,google map,google map plugin,google maps plugin,googlemaps,map markers,map plugin,map styles,map widget,maps,marker,openstreetmap,place,polygons,polylines,post map,routes, streetview,widget map,wp google map,wp google maps,wp map,wp maps,wordpress google maps,wordpress google map, api,directions,driving,map custom color,custom markers colors,custom icons,custom map colors,custom icon colors,custom map markaers colors,custom markers,google map custom markers,custom google routes 
Requires at least: 4.3
Tested up to: 4.5.2
Stable tag: 4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate Google Maps easily in your site, no coding required. Use custom icons and colors for each location or route. Show unlimited maps.

== Description ==



This plugin gives you the possibility to easily integrate Google Maps in your site with a couple of clicks. Just set your locations and show your maps easily in your pages and posts.


Show unlimited maps per category or post ID. Organize locations in categories to show different Google Maps per Category.

<strong>Map Icons font</strong>: Visit Map Icons font to see the icons and icon types you have available. The Map Icon library have been integrated in this plugin to show beautiful maps.
[Visit Map Icons](http://map-icons.com/)

<strong>Locations Post Type</strong>: To save your locations, with images and descriptions and show them in a map.



With this Google Maps Plugin you can:
<ul>
	<li>Create, edit and delete your custom locations</li>
	<li>Add as many locations as you need</li>
	<li>Easy to use, no coding required</li>
	<li>100% Responsive Maps</li>
	<li>Google Maps Streetview supported</li>
	<li>UTF-8 character support</li>
	<li>Add colors, icons, icon type, images, links and descriptions to map markers</li>
	<li>Organize locations in categories</li>
	<li>Create several maps showing different locations</li>
	<li>Create Routes</li>
</ul>

== Installation ==



= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'wp-maps'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `wp-maps.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `wp-maps.zip`
2. Extract the `wp-maps` directory to your computer
3. Upload the `wp-maps` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


== Frequently Asked Questions ==

None by the moment.

== Screenshots ==

1. Shows the map in action showing a route
2. Each Location Options

== Upgrade Notice == 

No upgrades yet

== Changelog ==

= 1.0.1 =

-Fixed bug php notice when no locations found, added info message


= 1.0 =

First Version

= 2.0 =

Divided plugin in 3, using Mimo Maps and Mimo Maps for Woocommerce

= 3.0 =

-Mayor upgrade
-Included Map Icons font
-Deleted Maps Post Type
-Deleted posibility to create different colored maps
-Changed way to create locations, now it is needed to include the coordinates by hand


== Upgrade Notice ==

= 3.0 =
This is a mayor upgrade where plugin has been completely modified. Upgrading would mean you need to re-create your locations



== Usage instructions ==

Set category icons and colors in plugin Settings, Goto Settings/Wp Maps</br>

Use [wpmaps] shortcode to show the map with all locations(all posts or products locations), using shortcode:

[wpmaps post_id="id of location post"] Shows only one post location

[wpmaps category_slug="slug"] Shows locations in posts from one category


Shortcode atributtes:



<ul>
	<li><strong>id</strong> = Id of Map custom Post type(to take it style), default empty</li>
	<li><strong>post_id</strong> = Id of post to show(only one post), default empty</li>
	<li><strong>category_slug</strong> = slug of category to show, default empty</li>
	<li><strong>posts_per_page</strong> = Number of posts to show(only posts with locations), default -1</li>
	<li><strong>post_type</strong> = post or product, default locations post that is called 'wpmaps_location'</li>
</ul>



== Developer instructions ==



You can pass your options to the opstions array:

<code>

$args = array(
'post_id' => '',
'category_slug' => '',
'posts_per_page' => '',
'post_type' => '',

);


if (  class_exists('WpMaps_Display')  ) WpMaps_Display::display_map( $args );</br>

</code>


Find plugin and issues solved at http://mimo.studio or fork it on Github at https://github.com/mimomedia/wp-maps