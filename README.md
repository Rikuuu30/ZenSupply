# Zen Supply POS

Zen Supply is a CodeIgniter 4 point-of-sale and inventory application for a martial-arts equipment store. It uses MariaDB/MySQL and the existing `rikuuuDB` database. Customer and staff records are retained when the application is upgraded; products, sales, account archives, and role-aware staff management are added through migrations.

## What the application includes

- Staff sign-in, server-side route protection, session ID regeneration, sign-in throttling, CSRF-protected changes, and complete sign-out.
- Product catalog search, detail pages, create/edit/archive/restore, SKU validation, prices, stock, low-stock thresholds, and prepared product thumbnails.
- Customer create/read/update/archive/restore with search, pagination, optional phone, and preserved receipt history.
- Administrator-managed staff records with unique usernames, four access roles, hashed passwords, optional prepared avatars, blank-password retention on edit, archive/restore, and protection against removing the last active administrator.
- Single-item sale recording with an optional customer, server-calculated price and total, stock sufficiency checks, a transaction, a row lock and conditional decrement, and a unique form token to prevent duplicate receipts.
- Searchable and date-filterable sales history, stored product/customer/staff snapshots, printable receipts, live dashboard metrics, top sellers, and low-stock alerts.
- A configurable Tasks for Today link. The two applications remain separate and do not share sessions or databases.
- Responsive navigation, accessible form feedback, confirmation prompts for archival, empty states, reduced-motion support, and a user-selectable light/dark theme.
- A locally bundled 12-item starter catalog with product-specific artwork, searchable specifications, sample PHP prices/stock, low-stock thresholds, and a clear warning to verify sample values before real sales. The sales summary and history remain based only on recorded transactions.

## Requirements

- PHP 8.2+ with `mysqli`, `fileinfo`, `gd`, and the CodeIgniter-required extensions
- Composer
- MariaDB/MySQL with InnoDB support (XAMPP is suitable)
- Apache rewrite support if deployed through Apache

## Local setup (XAMPP)

1. Start Apache and MySQL in XAMPP.
2. In an existing `rikuuuDB` installation, take a database backup, then run `php spark migrate` from this project folder. The migration adds the new tables and columns without re-importing, resetting, or replacing current customer/staff records. Do not import the SQL file over an existing database.
3. For a new local database only, import [`database/rikuuuDB.sql`](database/rikuuuDB.sql) in phpMyAdmin. Then run `php spark migrate` to record the application migrations. To add the optional starter catalog to an empty inventory, run `php spark db:seed SampleCatalogSeeder`; it safely does nothing when any product already exists. These examples are not verified supplier data—confirm names/specifications, prices, sizes, and quantities before real retail use.
4. Run `composer install` in the project folder.
5. Copy `env` to `.env` if `.env` does not exist. Set the `database.default.*` values for your XAMPP MariaDB account. Keep `.env` private and do not commit it.
6. The default Tasks for Today URL is `http://localhost:8082/`. Change `tasks.baseURL` in `.env` when your Tasks app runs elsewhere.
7. Run `php spark serve --host localhost --port 8081`, then open `http://localhost:8081/`.

The local demo administrator is `admin.rikuuu` with password `ZSD123`. It is coursework-only: replace it and any temporary staff passwords before exposing the app to a network or using real data. The SQL export includes schema plus a generic administrator seed; it intentionally excludes live customer and staff records.

## Database and data safety

`database/rikuuuDB.sql` is a fresh-install schema and example-catalog export, not a live-data backup. It contains no real customer or staff records, and its sample prices/quantities should be reviewed before use. On an existing installation, `php spark migrate` is the safe upgrade path. Migrations add `products` and `sales`, along with role and active/archive flags. They do not drop or recreate `customers` or `users`.

Sales record the product, optional customer, and staff foreign keys as well as name and price snapshots. Product/staff removal is restricted by foreign keys; normal user actions archive records instead. A customer can be physically removed only if no sale references the customer, and the sale also preserves the customer-name snapshot. Receipts remain readable after catalog or account changes.

The stock decrement and sale insert execute inside one MariaDB transaction. A product row lock and a conditional `stock_quantity >= requested quantity` update prevent overselling if two users try to sell the remaining stock together. The unique idempotency token makes a repeated checkout return the original receipt instead of creating another.

## Roles

- **Administrator:** full access, including staff management.
- **Manager:** sales and customer access; can manage product inventory.
- **Inventory:** sales and customer access; can manage product inventory.
- **Cashier:** sales and customer access; cannot change inventory or staff roles.

The existing `admin.rikuuu` account is promoted to administrator by migration. Other existing accounts remain active with the `staff` role until an administrator edits them and assigns a suitable role; they retain access to sales and customer accounts.

## Image uploads

Product images and staff avatars accept genuine JPEG/PNG images up to 2 MB. The application checks MIME/image data and dimensions, generates a random filename, re-encodes to JPEG, and stores a display-sized thumbnail under `public/uploads/`. Existing photos are retained if no replacement is submitted. The upload folder disallows directory listing and executable script extensions. Ensure PHP GD and Fileinfo are enabled and the upload folders are writable by PHP.

## Automated verification

Run `composer test`. Tests use the in-memory SQLite connection, so enable the PHP `sqlite3` extension in XAMPP's `php.ini` (the POS runtime itself uses MariaDB/MySQL). On Windows, this is commonly `extension=php_sqlite3.dll`; restart the terminal after enabling it. For concurrency guarantees, test with InnoDB; SQLite tests cover normal workflow behavior but do not replace a MariaDB concurrent-sale test.

## GitHub and deployment

Keep `vendor/`, `.env`, writable runtime files, cache, logs, sessions, and uploaded customer/staff images out of the repository. Commit `composer.json` and `composer.lock`; install Composer dependencies on the target machine. Point the web server document root to `public/`, use HTTPS, configure a private production `.env`, enable secure cookie settings, and run the migrations against a backed-up production database.

The project is intended for local review at `http://localhost:8081/`. No hosted URL is included or claimed; if the assessment requires hosting, that remains a separate submission step.
