# ERIBS Media migration notes

The Laravel application lives in this repository. The saved site that was inspected is a single file:

`C:\Users\ADMIN\OneDrive\Desktop\ERIBS-NEWS-MEDIA\index.html` (about 180 KB). A copy is kept at `legacy/index.html`.

## What the saved site actually was

One cached WordPress homepage (LiteSpeed, 2026-10-05) for okeomanews.ng. There were no local CSS, JavaScript, image, or font files. The page is the Soledad theme (8.4.2) with Elementor, Yoast SEO, Advanced Ads, Contact Form 7, and Google Site Kit. WordPress admin, article bodies, and the About, Contact, Privacy, and Terms pages were not in the file.

Visual system taken from that page:

- Accent `#e53935`, header `#161616`, navigation `#111111`, top bar black, footer `#111111`
- Headings in Rubik, body in Open Sans
- Breaking-news ticker, search field (“Type and hit enter…”), featured mosaic, Popular Posts, News and Politics blocks, Latest Updates, right sidebar, mobile menu, back-to-top control
- Menu destinations: Politics, Contact Us, About Us, Privacy Policy, Terms of Use
- Homepage sections also linked a News category

The public Blade layout recreates that structure. It does not copy the commercial Soledad stylesheet or scripts.

## Kept as data, not pasted into templates

- Categories News and Politics
- AdSense client `ca-pub-9287364659605726` and slots `8023666262`, `7155551469`, `1547440485`, stored as inactive advertisement rows
- Analytics ID `GT-5M8TBNT`, stored and left disabled

Those third-party IDs belonged to the saved OkeomaNews homepage. Replace them with ERIBS accounts before turning them on.

## Deliberately not imported

- PopCash, Adcash, Adskeeper, and Bidvertiser snippets. They were popunder or auto-tag scripts. Placements in the advertisement admin replace them.
- WordPress, Yoast, Contact Form 7, and Soledad ajax endpoints. Search, contact, SEO, and RSS are Laravel routes.
- Article headlines from the homepage. The file had titles and remote image URLs only, including graphic crime reports. No article bodies were present, so none were seeded.
- The remote OkeomaNews logo. The ERIBS mark is `public/images/mark.svg`.
- Social links. In the snapshot they were `href="#"`.
- Comment widgets. The homepage listed a few comments and did not include a comment system in the requested newsroom model.

## URL changes

| Saved site | Laravel |
| --- | --- |
| `/?s=` | `/search?q=` |
| `/index.php/category/{slug}/` | `/category/{slug}` |
| `/index.php/{year}/{month}/{day}/{slug}/` | `/article/{slug}` |
| `/index.php/feed/` | `/rss.xml` |
| `/about-us/`, `/contact-us/`, `/privacy-policy-2/`, `/terms-of-use/` | `/about`, `/contact`, `/privacy-policy`, `/terms-of-use` |

`/latest-news` is new. The old menu did not have it; Latest Updates was a homepage block.

## Local newsroom login

Created only when `APP_ENV` is not `production`:

- Email: `admin@eribs.test`
- Password: `password`

Change it before any shared or production use.

## Production

Set `APP_ENV=production` and `APP_DEBUG=false`. Use MySQL or MariaDB (`DB_CONNECTION=mysql` in `.env.example`). Run `php artisan storage:link` and schedule `php artisan schedule:run` every minute so `articles:publish-scheduled` can publish due stories.
