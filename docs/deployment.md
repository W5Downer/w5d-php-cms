# Deployment — upload to the w5d.tech web root

The deliverable is a **static build**. There is no installer, no database, no SSH command, and no
PHP runtime requirement on the host.

## Build

```bash
php bin/build.php                    # → dist/   (default template: ledger)
php bin/build.php --template=atlas   # → dist/   (atlas skin)
php bin/build.php --template=folio   # → dist/   (folio skin)
php bin/build.php --preview=1        # → dist/   with noindex + no canonical URLs
```

The build prints its own report and exits non-zero if any page failed to render, so a broken build
can never be uploaded silently.

## Upload

Copy the **contents** of `dist/` — not the `dist` folder itself — to the web root:

```
dist/index.html          → https://w5d.tech/index.html
dist/fr/index.html       → https://w5d.tech/fr/index.html
dist/ar/index.html       → https://w5d.tech/ar/index.html
dist/ontology/…          → https://w5d.tech/ontology/…
dist/assets/…            → https://w5d.tech/assets/…
dist/404.html            → https://w5d.tech/404.html
dist/robots.txt          → https://w5d.tech/robots.txt
dist/sitemap.xml         → https://w5d.tech/sitemap.xml
dist/_headers            → https://w5d.tech/_headers   (CSP; honoured by Netlify/Cloudflare Pages)
dist/site.webmanifest    → https://w5d.tech/site.webmanifest
```

Any method works: FTP, an SFTP client, a hosting control panel's file manager, `rsync`, or a CI job.
Uploading a single folder is enough because every internal link is root-relative.

## Why nothing else is needed

| Normally required | Here |
|---|---|
| PHP runtime | ❌ not needed — pages are pre-rendered HTML |
| MySQL/SQLite at the host | ❌ not needed — content is baked in at build time |
| Composer / `vendor/` | ❌ none — the engine has zero dependencies |
| `.htaccess` rewrite rules | ❌ not needed — real directories with `index.html` |
| Build step at the host | ❌ not needed — the build happens on your machine |

## Language routing

- English (default) at `/`
- French at `/fr/`
- Arabic at `/ar/` (RTL)

## Re-deploying

Change content or variables, run `php bin/build.php`, upload the contents of `dist/` again. Because
the build cleans `dist/` first, deleted pages disappear from the output.

## Optional: sub-path hosting

Set `base_url` in `config/app.php` (e.g. `'/site/'`) before building if the site must live under a
sub-directory rather than at the domain root.
