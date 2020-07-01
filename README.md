# Google Places Sync plugin for Craft CMS 3.x

Syncs Google Places API data to entries.

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require headjamcreative/craft-google-places

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Google Places Sync.

## Configuring Google Places Sync

Ensure you add a Google Cloud Console api key in the app settings for this plugin to work. Be aware that this may come with additional cost from Google for the api queries. @see <https://developers.google.com/places/web-service/get-api-key>

## Using Google Places Sync

Add a 'Google Places Sync' field to your entry or global, then in the lookup filed add either a business name, address, or an international phone number (including country code, e.g. +61249291154). At present the plugin only retrieves the first available candidate. If this is not the correct candiate, you can manually lookup the place_id and override the id field. The lookup field is ignored if an id is already present.

## Scheduling updates

The default controller action finds all elements with a 'Google Places Sync' field and updates that entry. You can use it in a cron job or similar by hitting the endpoint `https://example.com/actions/craft-google-places/default`.