# JCNCM — Jesus Christ of New Creation Ministries

Single-page static site for Andhra Pradesh-based church ministry.

## Structure

```
index.html       # Entire site (HTML + CSS + JS inline)
logo.jpg         # Church logo (from YouTube channel)
photos/          # Worship + outreach photos
video/           # Hero background video loop
.cpanel.yml      # BigRock cPanel deploy config
```

## Local preview

Just open `index.html` in a browser. No build step.

## Deploy

Push to `main` → cPanel Git Version Control pulls from GitHub → `.cpanel.yml` copies files into `public_html/`.

## Languages

Site supports English, Telugu, Spanish, French (toggle in nav). Translations live in the `I18N` object inside `index.html`.
