# NEW AOM database setup

## Existing XAMPP installation

1. Sign in to `/admin` as a Super Admin.
2. Open **CMS setup** from the dashboard warning, then choose **Install CMS database**. The setup is idempotent and preserves existing content.
3. Use **Website Content** to edit the seeded homepage, About, services, Knowledge Centre, events, gallery, crowdfunding, and FAQ entries. Starter content for these sections is localized in English, Tamil, French, Simplified Chinese, and Swiss German; select a language before saving further translations.
4. Upload images and videos in **Media Library**, then copy the returned `uploads/cms/...` path into the content item's media URL field.
5. Change the seeded Super Admin password from **Users & Roles** before using the site in production.

The setup page creates `cms_content`, inserts missing starter records, and fills missing localized service descriptions without overwriting existing descriptions. The public API merges localized fields over the English record, so new CMS content without a translation continues to use its English text. The equivalent SQL files are `cms_schema.sql` and `cms_seed.sql`.

## Fresh database installation

Create the `new_aom_db` database and import these files in order:

1. `schema.sql`
2. `admin_schema.sql`
3. `cms_schema.sql`
4. `cms_seed.sql`

Keep the database credentials in `Backend/config/db.php` private and configure them for the local server.

## Frontend API

By default, the React app uses the XAMPP API at `http://localhost/new-aom/new-aom-react/Backend/api`. To override it, set `VITE_API_BASE_URL` before starting or building the frontend.
