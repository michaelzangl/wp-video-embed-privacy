=== Embed videos and respect privacy ===
Contributors: michael.zangl
Tags: youtube, germany, deutschland
Requires at least: 4.5
Tested up to: 4.9.4
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to embed youtube videos without sending data to google on every page view. Required for a GDPR (German: DSGVO) compliant website.

== Description ==

This is meant to be used in the european union, but can be used in any other country where you want some more privacy for your users.

No settings required, youtube links get replaced. Adjust the displayed message according to your local needs.

= German Background =

Das deutsche Datenschutzrecht - und jetzt die europäische DSGVO - ist relativ streng. Der normale Youtube Einbindenlink entspricht nicht (unbedingt) den Datenschutzbestimmungen.

Dieses Plugin baut erst eine Verbindung zu Youtube auf, wenn der Nutzer aktiv auf den Abspielen-Knopf klickt.

Es ist kein Setup erforderlich und es gibt dementsprechend auch keine Einstellungen.

== Installation ==

You need to enable url_allow_fopen.

As any other wordpress plugin.

Download the relase zip from the github repository or from wordpress.org, upload it to your wordpress.

It simply works out of the box, no configuration required.

== Frequently Asked Questions ==

= How to add a youtube video =

Simply post the youtube link as usual

= Can I enable caching =

Yes, rename the file in the video-embed-privacy/preview directory.

== Screenshots ==

None so far.

== Changelog ==

= 1.0 =
* First version

= 1.2 =
* Add link to google privacy page

= 2.0 =
* Add settings
* Internationalize
* Prevent external requests to preview generator.

