# Spideli — eMart (with Addons) Admin Panel

Customized build of the eMart multi-vendor admin panel for Spideli.

Laravel 10 for authentication, roles and permissions (MySQL); all
operational data lives in Firebase Firestore and is queried client-side
from the Blade views.

## Customizations

- **Region management** — regions CRUD, a region selector in the top bar,
  and per-region scoping of vendors, stores, orders, drivers, customers,
  service providers and the dashboard figures
- **Per-region admin users** — staff can be limited to one or more regions
- **Per-region currency** — records display in their own region's currency
- **Per-region delivery charges** — global default with per-region override
- **Per-region payment methods and services**
- **Carrier management** — delivery companies with documents, pricing,
  delivery times and conditions
- **Delivery management** — driver assignment, reassignment, resend
  notification and full assignment history, from the order screen
- **Delivery commission** and the **free order history limit** setting

## Setup

```
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan app:grant-region-permissions
```

Log out and back in after granting permissions — they are read at login.

The region-filtered lists also need their Firestore composite indexes to
exist before they return data.

## Not in this repository

`.env`, `vendor/`, storage runtime files, the Firebase service account key
and the internal working documents.
