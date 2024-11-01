=== Two Factor Authentication ===
Contributors: frankbroersen
Donate link: http://tokenizer.com/
Tags: tokenizer, login, multi factor, two factor
Requires at least: 3.0.1
Tested up to: 3.6.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add Two Factor Authentication for Wordpress using your smartphone or tablet and the Free Tokenizer service (tokenizer.com).

== Description ==

To add Tokenizer Two-factor authentication to your website, follow these steps:

1. Download the Tokenizer app to your smartphone or tablet <https://tokenizer.com/download.html>
2. Open the App, and register your account
3. Go to your email inbox to confirm your account
4. Log in on <http://www.tokenizer.com>
5. Click the "Create service" button, and follow the steps to create a Tokenizer service, make sure 
that you verify it via the provided steps.
6. Get the Tokenizer `APP_ID` and `APP_KEY` from your email inbox

Now you can install the Tokenizer Plugin, via your Wordpress administrator panel.

== Installation ==

1. Upload the `tokenizer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enter your Tokenizer service information
4. If they are correct, you will be forced to login using Tokenizer

== Frequently Asked Questions ==

= How can I register a Tokenizer service =

Go to www.tokenizer.com, log in using your Tokenizer account, and click the "Add service" button.

== Screenshots ==

1. App settings screen, add your APP_ID and APP_KEY.
2. Tokenizer waiting page example
2. Rejected Tokenizer login

== Changelog ==

= 1.0.1 =
* Updated to support older php version (5.3)
* Force usage of user_email instead of user_login

= 1.0.0 =
* First version of Tokenizer Plugin
