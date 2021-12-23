=== WP Extra File Types ===
Contributors: davide.airaghi
Tags: file type, upload, media library
Requires at least: 4.0
Tested up to: 5.8.2
Stable tag: 0.5.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin to let you extend the list of allowed file types supported by the Wordpress Media Library


== Description ==
This plugin let you add file types to the default list of file extensions 
supported by the Media Library upload procedure.


== Installation ==
1. Create the directory wp-extra-file-types in your '/wp-content/plugins/' directory
2. Upload all the plugin's file to the newly created directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.5.2 =
* files cleanup

= 0.5.1 =
* security optimizations
* code optimizations

= 0.5 =
* adapted to WP 5.8.2
* bug fixes
* code optimizations

= 0.4.4.1 =
* user interface fix

= 0.4.4 =
* released experimental fix to have better integration with GravityForms

= 0.4.3 =
* fix on custom file types

= 0.4.2 =
* mime types database updated

= 0.4.1 =
* changed .exe mime type

= 0.4.0 =
* added more extensions and mime types
* added the option "skip wordpress check" to disable WordPress internal checks
* changes to settings page

= 0.3.6 =
* do not display file types already allowed by WordPress
* added the option "skip strict mime type check"
* fixed a problem related to mime types identification strings different between PHP finfo_* functions and user defined list

= 0.3.5 =
* added .pages
* added .numbers
* added .keynote

= 0.3.4 =
* added .mobi

= 0.3.3 =
* added .notebook
* added .gallery

= 0.3.2 =
* added .woff2 file type

= 0.3.1 = 
* added .gpx file type, thanks to "SpriterQC" for the suggestion

= 0.3.0 =
* added custom file types administration, you can add/remove/modify new entries

= 0.2.4 =
* renamed .bid to .bld, thanks to "wbdesinger"

= 0.2.3 = 
* added .m4r file type, thanks to "ywait4ever" for the suggestion

= 0.2.2 = 
* added .bid file type, thanks to "wbdesigner" for the suggestion

= 0.2.1 = 
* added .msp and .msu file types, thanks to "zkiller" for the suggestion

= 0.2 =
* added a long list of mime-types
* added administration page

= 0.1 =
* first release
