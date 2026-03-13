# DigitalOcean Spaces – CORS setup for file uploads

If you see **"Upload to Spaces failed (often CORS)"** or the upload fails with a network/CORS error in the browser console, your Space must allow cross-origin requests from your app.

## 1. Open CORS settings

1. In [DigitalOcean](https://cloud.digitalocean.com/), go to **Spaces** and select your Space (bucket).
2. Open **Settings** and find **CORS Configurations** (or **Edit CORS**).

## 2. Add a CORS rule

Add a rule that allows your app’s origin and the methods/headers used for uploads.

**Using the DigitalOcean control panel:**

- **Origin**
  - Production: your app URL, e.g. `https://yourdomain.com`
  - Development: `http://localhost:8000` or `http://127.0.0.1:8000`
  - Or use `*` only for quick testing (not recommended in production).
- **Allowed methods:** `GET`, `PUT`, `HEAD`, `POST` (at least **PUT** for presigned uploads).
- **Allowed headers:** `*` or at least `Content-Type` and `Authorization` (presigned URLs may send these).
- **Max age:** e.g. `3600`.

**Example CORS XML** (if you configure via API/s3cmd):

```xml
<CORSConfiguration>
  <CORSRule>
    <AllowedOrigin>https://yourdomain.com</AllowedOrigin>
    <AllowedOrigin>http://localhost:8000</AllowedOrigin>
    <AllowedMethod>GET</AllowedMethod>
    <AllowedMethod>PUT</AllowedMethod>
    <AllowedMethod>HEAD</AllowedMethod>
    <AllowedMethod>POST</AllowedMethod>
    <AllowedHeader>*</AllowedHeader>
    <MaxAgeSeconds>3600</MaxAgeSeconds>
  </CORSRule>
</CORSConfiguration>
```

Replace `https://yourdomain.com` and `http://localhost:8000` with the origins your app actually uses.

## 3. Check .env

Ensure your Space is correctly configured in `.env`:

- `DIGITALOCEAN_SPACES_KEY` / `DIGITALOCEAN_SPACES_SECRET`
- `DIGITALOCEAN_SPACES_BUCKET`
- `DIGITALOCEAN_SPACES_REGION` (e.g. `nyc3`)
- `DIGITALOCEAN_SPACES_ENDPOINT` = `https://nyc3.digitaloceanspaces.com` (replace region if different)

Then run: `php artisan config:clear`.

## 4. Browser console

If it still fails, open DevTools → **Console** and **Network**. A red CORS error or “blocked by CORS policy” confirms the issue is CORS; adjust the Space’s CORS rule to match the origin, method, and headers shown there.
