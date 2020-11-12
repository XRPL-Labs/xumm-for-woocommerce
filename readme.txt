=== XUMM for WooCommerce ===
Contributors: xumm, wietsewind
Donate link: https://xumm.dev/beta/test 
Tags: xumm, crypto, xrp, ledger, cryptocurrency
Requires at least: 4.7
Tested up to: 5.5.3
Stable tag: trunk
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://xrpl-labs.com/static/documents/XRPL-Labs-Terms-of-Service-V1.pdf

Accept XRP, EUR, USD, BTC & ETH, using a single plugin with the greatest XRP ledger client (wallet): XUMM!

== Description ==

Allow easy on ledger, non custodial XRP ledger payments, in XRP or IOU's (issued currencies).

XRP transactions are usually user initiated: open your wallet, enter the destination, amount, etc. and then you submit your transaction. In retail / e-commerce (and many other) scenarios, by "reversing" this process, the payment flow will become less prone to mistakes and much more user friendly.

== Frequently Asked Questions ==

= What currencies are supported? =

The following store currencies are supported: XRP, EUR, USD, BTC, ETH.

= Can I use any other store currencies? =

At this moment this is not possible due to the fact that we do not have a reliable exchange rate for these currencies.

= How do I know if my XRP account is setup correctly? =

The best way is to make a test payment, this will check all stages. However you can quickly see if you have any errors.
Also you can see if the trustline button is enabled this means you need to click it and set a trustline.

== Installation ==

= Minimum Requirements =

* PHP version 5.6 or greater
* WordPress 4.7 or greater
* WooCommerce 2.2.0 or greater

= Automatic installation =

1. Search for XUMM for WooCommerce plugin, at the plugin section in your admin panel.
2. Activate the plugin.
3. Before proceding make sure the store currency in WooCommerce -> settings -> General is set to either: XRP, Euro, US Dollar, Bitcoin, Ethereum.
3. Go to WooCommerce -> settings -> Payments & enable the GateWay to manage the plugin settings.
4. Get the API keys from the XUMM API console and insert the correct webhook.
5. You can now signin with your XRP account using XUMM, to use that as the destination address.
6. Finally you can configure the currency and issuer, this controls what you will receive inside your XRP account.
7. If the Add trustline button is enabled please click the button and set the trustline using XUMM.
8. All should be ok by now, please check with a test transaction.

= Manual installation =

1. Download the zip file
2. Upload the zipfile inside the WordPress plugin section
3. Continue to follow the automatic installation steps from point 2.

== Screenshots ==

1. Setup screen for payments.
2. This is the standard setup to receive XRP. It shows a connected message and no errors.
3. What the user will see when submitting the payment.
4. The setup page in the admin panel on the XUMM api.

== Changelog ==

= 0.2 =
* Pay with XRP, EUR, USD, BTC, ETH
* Public Beta first release

== Upgrade Notice ==

= 0.2 =
First public release for the WooCommerce plugin.
