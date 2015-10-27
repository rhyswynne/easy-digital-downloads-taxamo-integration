=== Easy Digital Downloads - Taxamo Integration ===
Contributors: rhyswynne
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VBQ4NPY2XX5KN
Tags: easy digital downloads, edd, taxamo, vatmoss, tax, vat, eu, translate-me, adopt-me
Requires at least: 3.9.2
Tested up to: 4.3
Stable tag: trunk
Licence: GPLv2 or later

No longer maintained. This plugin allows you to use Taxamo's EU VAT recording system in Easy Digital Downloads.

== Description ==

This plugin allows you to use [Taxamo's EU VAT recording system](http://winwar.co.uk/recommends/taxamo) within [Easy Digital Downloads](http://www.easydigitaldownloads.com).

In January 2015 EU Legislation required you to record the selling location of all sales from the EU and pay VAT on each purchase. In order to comply, services such as Taxamo will allow you to record the relevant data. This plugin will automatically track your transactions in Easy Digital Downloads, applying the correct VAT rate dependant on the user's location, as well as handles refunds as well.

More details are available on the official [Easy Digital Downloads - Taxamo Integration](https://winwar.co.uk/plugins/easy-digital-downloads-taxamo-integration/?utm_source=description&utm_medium=wordpressorgreadme&utm_campaign=eddtaxamointegration) page at Winwar Media's site.

> <strong>Adopt Me!</strong>

> As of October 26th, this plugin is no longer maintained. [The reasons for this are detailed in this blog post](https://winwar.co.uk/2015/10/discontinuation-of-easy-digital-downloads-taxamo-integration/?utm_source=adoptme&utm_medium=wordpressorgreadme&utm_campaign=eddtaxamointegration). In short, I've no longer got time to maintain it. I am happy to facilitate the transfer of this plugin from me to any other party, as well as assist with setting up the new owner. If you wish to adopt this plugin, [please contact me directly](https://winwar.co.uk/contact-us/?utm_source=adoptme&utm_medium=wordpressorgreadme&utm_campaign=eddtaxamointegration)

= Translation Credits =
The plugin has been translated to the following languages.

* Finnish - [Sami Keijonen](https://foxland.fi/) [@samikeijonen](https://twitter.com/samikeijonen)
* French - [Fx Bénard](http://fxbenard.com/) [@fxbenard](https://twitter.com/fxbenard)
* Swedish - [The WordPress Translations Project](http://wp-translations.org)

To contribute a translation, you can do so by [checking out the project on Transifex](https://www.transifex.com/projects/p/easy-digital-downloads-taxamo-integration/)

= About Winwar Media =
This plugin is made by [**Winwar Media**](http://winwar.co.uk/?utm_source=about&utm_medium=wordpressorgreadme&utm_campaign=eddtaxamointegration), a WordPress Development and Training Agency in Manchester, UK.

Why don't you?

* Check out our book, [bbPress Complete](http://winwar.co.uk/books/bbpress-complete/?utm_source=about&utm_medium=wordpressorgreadme&utm_campaign=eddtaxamointegration)
* Check out our other [WordPress Plugins](http://winwar.co.uk/plugins/?utm_source=about&utm_medium=wordpressorgreadme&utm_campaign=eddtaxamointegration), including [WP Email Capture](http://wpemailcapture.com/?utm_source=about&utm_medium=wordpressorgreadme&utm_campaign=eddtaxamointegration)
* Follow us on Social Media, such as [Facebook](https://www.facebook.com/winwaruk), [Twitter](https://twitter.com/winwaruk) or [Google+](https://plus.google.com/+WinwarCoUk)
* [Send us an email](http://winwar.co.uk/contact-us/?utm_source=about&utm_medium=wordpressorgreadme&utm_campaign=eddtaxamointegration)! We like hearing from plugin users.

== Installation ==

1. Upload \`taxamo-edd-integration` folder to the \`/wp-content/plugins/\` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Sign up for Taxamo, and note the Private & Public Key.
1. Add in the Downloads > Settings > Taxes your Public & Private key in the relevant box.
1. Switch on Taxes (in Downloads > Settings > Taxes).


== Changelog ==

= 1.6.2 =
* Fixed a bug that caused the wrong tax to be calculated should there be no fallback.

*Time Taken - 1 hour*

= 1.6.1 =
* Changed tracking code.
* Tested in 4.3.

= 1.6 =
* Added the ability to disable custom ID & custom invoicing, allowing Taxamo to set these values.
* Updated the Taxamo integration script to 1.0.22.
* Added Swedish Translation

*Time Taken - 2 hours*

= 1.5.1 =
* Fixes bug that reports incorrect values for non-EU based orders but with other tax rates, with Inclusive pricing switched on.

*Time Taken - 2 hours*

= 1.5 =
* Allows the ability to set prices inclusive of tax, rather than simply exclusive.
* Fixes a small bug that if you cannot check out if your cart total is zero because of a discount code, when checking out in a country that doesn't match your IP address.

*Time Taken - 6 hours, 30 minutes*

= 1.4 =
* Allows tax to fall back should Taxamo returns zero. Useful for non EU companies using the system if they have alternate tax defined.
* Tested with Easy Digital Downloads 2.3.
* Fixed a VAT error occurred when a user was - for example - buying from a UK shop, with a German VAT number, who is located in France.
* Impoved Validnation checks on VAT number.

*Time Taken - 2 hours*

= 1.3.1 =
* Uses the EDD Customer IP rather than the IP, stops wrong information being recorded with some orders.

= 1.3 =
* Improved speed of the plugin, a lot less API calls!
* If the user's IP matches the shop location, we don't automatically show the confirmation box.
* Fixes a bug so that if you have an order placed before the plugin is created that is refunded, no errors appear.

Thanks to [Nate Wright](http://themeofthecrop.com) for his work on this update!

*Time Taken - 3 1/2 hours+*

= 1.2 =
* Returns correct tax rate should discount codes be used.
* Transactions that are free downloads are not submitted to Taxamo
* Should the self-confirmation box not be displayed (such as for a free transaction), then it is not checked.
* Added French Translation

Thanks to [Eric Daams](http://164a.com/) & [Fx Bénard](http://fxbenard.com/) for his work on this update!

*Time Taken - 1 hour+*

= 1.1 =
* Code Cleanup - removed a lot of redundant files
* Added Finnish Translation
* Removed Updater Files
* Fire Script.js only on checkout page.
* Added ABSPATH check.

A big thank you to [Foxland](https://foxland.fi/) and [@samikeijonen](https://twitter.com/samikeijonen) for his work on this update!

*Time Taken - 1 1/2 hours+*

= 1.0.2 =
* Remove warning on sitewide carts if cart is empty.

*Time Taken - 16 minutes*

= 1.0.1 =
* First Push to the WordPress.org repository.

*Time Taken - 23 hours*

= 1.0 =
* First release