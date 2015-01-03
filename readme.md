Easy Digital Downloads - Taxamo Integration
===========================================
Contributors: rhyswynne

Donate link: 

Tags: easy digital downloads, edd, taxamo, vatmoss, tax, vat, eu

Requires at least: 

Tested up to: 4.1

Stable tag: trunk

This plugin allows you to use Taxamo's EU VAT recording system in Easy Digital Downloads.

Description
-----------
This plugin allows you to use Taxamo's EU VAT recording system within Easy Digital Downloads.

In January 2015 EU Legislation required you to record the selling location of all sales from the EU and pay VAT on each purchase. In order to comply, services such as Taxamo will allow you to record the relevant data. This plugin will automatically track your transactions in Easy Digital Downloads, applying the correct VAT rate dependant on the user's location, as well as handles refunds as well.

Installation
------------
1. Upload `taxamo-edd-integration` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Sign up for Taxamo, and note the Private & Public Key.
1. Add in the Downloads > Settings > Extensions your Public & Private key in the relevant box.
1. Switch on Taxes (in Downloads > Settings > Taxes).

Frequently Asked Questions
--------------------------
**I am unable to submit to Taxamo when testing locally, and is getting a validation error, why is it?**

For some reason, Taxamo treats local IP's as being Irish, but your billing data is coming from elsewhere. As a result, Taxamo doesn't accept both pieces of data as by law there needs to be two non-contradictory pieces of data (which is the IP address, and the billing address).