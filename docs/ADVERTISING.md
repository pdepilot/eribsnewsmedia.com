# Advertising manager

ERIBS Media resolves every public ad through `AdvertisingService`. Network code pasted by an administrator is printed as supplied. The application does not rewrite AdSense, Monetag, Adsterra, or Media.net tags, and it does not store network API secrets.

## Architecture

- `ad_networks` holds a network name, type, and optional publisher id. Types include `adsense`, `monetag`, `adsterra`, `medianet`, `custom`, `third_party`, and `direct`.
- `ad_placements` are the named slots in the public templates. Hyphens in Blade (`homepage-top`) are stored as underscores (`homepage_top`).
- `ad_units` hold pasted network code in `markup`, plus device, page, schedule, priority, and weight.
- Direct advertisers use `advertisers`, `ad_campaigns`, and `advertisement_creatives`. Banner files are stored on the `public` disk under `ads/Y/m`.
- `AdManagerService` filters by schedule, page, and device, then picks the highest priority and applies weight inside that group. A campaign is eligible when its dates include now, so no scheduler is required to hide a future or expired campaign.
- Impressions and clicks for direct campaigns are stored as hashes of the session id and IP. Raw IP addresses are not stored. Network revenue is not invented.

## Add a network

In the portal, open Advertising → Ad Networks → create a row. Leave it disabled until the owner pastes a real tag into an ad unit. Do not invent a publisher id.

## Add a placement

Placements are seeded. A new one needs a unique slug and a matching `<x-ad-slot placement="your-slug" />` in a public template. Do not drop ad markup inside article body text.

## Add an ad unit

Choose the network and placement, paste the network's code into the unit markup, and set device (`all`, `desktop`, `tablet`, `mobile`) and page (`all`, `homepage`, `article`, `category`, `search`, `tag`). The code is not modified. `fallback_code` is used only when nothing else qualifies for that slot.

## Direct advertisers

Create an advertiser, a creative (image on the public disk, or HTML), and a campaign with start and end. Clicks on image creatives go through `/ad/{campaign}/click` and then to the https destination. Third-party tags keep their own click handling.

## ads.txt

Advertising → Ads.txt stores full lines. Structured rows in `ads_txt_entries` are appended. `GET /ads.txt` returns `text/plain`. No publisher id is hard-coded.

## Analytics

Advertising → Analytics filters today, yesterday, 7 days, 30 days, or a custom range. Totals are SQL counts, not a PHP loop over every event.

## Security

Portal routes require the `ads.manage` permission. Uploaded creatives must be JPEG, PNG, GIF, or WebP. Destination URLs must be http or https. Ad HTML is trusted administrator input.

## Deployment

Production has no Node and no `exec()`. This module does not need `npm` or `php artisan storage:link`. Keep the existing `public/storage` link. After pulling the code, run:

```
php artisan migrate --force
php artisan db:seed --class=AdvertisingSeeder --force
```

`AdvertisingSeeder` only adds placements and inactive demo networks. It does not create publisher IDs and it does not change users. Do not run the full `NewsroomSeeder` on a live database.

`public/.user.ini` raises the PHP upload limit for image creatives where the host reads it.

## Names already in the database

The live tables were extended instead of recreated. A few spec names map onto columns that were already there:

- network and unit status is `enabled`
- campaign dates are `start_at` and `end_at`
- creatives live in `advertisement_creatives`
- pasted network code is `markup`, with `fallback_code` used only when nothing else qualifies
- existing `ads_txt_lines` are still printed, and structured `ads_txt_entries` are appended
