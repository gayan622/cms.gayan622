# Gayan622 Backend — Laravel 11 API + Filament Admin

## Stack
- **Laravel 11** — REST API
- **Filament v3** — Admin panel (dashboard, orders, products, blog, SEO, reports)
- **MySQL** — database
- **DomPDF** — invoice PDF generation
- **Spatie Laravel Permission** — roles (optional)

---

## Quick Start

```bash
# 1. Install dependencies
composer install

# 2. Set up environment
cp .env.example .env
php artisan key:generate

# 3. Create database and configure .env
#    DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Run migrations + seed
php artisan migrate --seed

# 5. Create storage symlink (for uploaded images)
php artisan storage:link

# 6. Start development server
php artisan serve
# → http://localhost:8000
```

---

## Admin Panel

Access at: **http://localhost:8000/admin**

Default credentials (from seeder):
```
Email:    admin@gayan622.online
Password: Admin@1234
```

### Admin Sections

| Section | Description |
|---|---|
| **Dashboard** | Revenue stats, chart, recent orders |
| **Projects / Blog** | Add/edit portfolio projects with screenshots |
| **Products** | Shop products with pricing, stock, images |
| **Categories** | Product categories |
| **Manufacturers** | Product brands |
| **Orders** | View orders, update status, mark paid, download invoice |
| **Coupons** | Create discount codes (percent or fixed LKR) |
| **Messages** | Contact form submissions, mark read/replied |
| **SEO Settings** | Per-page title, description, keywords, OG image |
| **Analytics** | Revenue chart, top products, stock report |

---

## API Endpoints

### Public

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/projects` | All projects (supports `?search=&category=`) |
| GET | `/api/projects/featured` | Featured projects (home page) |
| GET | `/api/projects/{slug}` | Single project detail |
| GET | `/api/products` | Products (supports `?category=&sort=&search=`) |
| GET | `/api/products/{slug}` | Single product |
| GET | `/api/categories` | Product categories |
| POST | `/api/coupons/validate` | Validate coupon `{code, order_total}` |
| POST | `/api/orders` | Create order + get PayHere hash |
| GET | `/api/orders/{id}` | Order detail |
| GET | `/api/orders/{id}/invoice` | Download PDF invoice |
| POST | `/api/payhere/notify` | PayHere payment webhook |
| POST | `/api/contact` | Contact form submission |
| GET | `/api/seo/{page}` | SEO settings for a page |

### Protected (requires Bearer token)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/admin/reports/dashboard` | Dashboard stats |
| GET | `/api/admin/reports/sales` | Sales chart data |
| GET | `/api/admin/reports/top-products` | Best selling products |
| GET | `/api/admin/reports/stock` | Stock levels |
| GET | `/api/admin/reports/coupons` | Coupon usage stats |

---

## PayHere Setup

1. Sign up at https://www.payhere.lk and get your **Merchant ID** and **Merchant Secret**
2. Add to `.env`:
```
PAYHERE_MERCHANT_ID=your_merchant_id
PAYHERE_MERCHANT_SECRET=your_secret
PAYHERE_MODE=sandbox   # change to 'production' when live
```
3. In your PayHere merchant portal, set the **Notify URL** to:
   `https://yourdomain.com/api/payhere/notify`
4. The Laravel backend generates the MD5 hash server-side — never expose your secret to the frontend.

---

## Directory Structure

```
app/
├── Http/Controllers/Api/
│   ├── ProjectController.php     ← Blog / portfolio
│   ├── ProductController.php     ← Shop products
│   ├── CouponController.php      ← Coupon validation
│   ├── OrderController.php       ← Orders + PayHere + invoices
│   ├── ContactController.php     ← Contact form
│   └── ReportController.php      ← Analytics API
├── Models/
│   ├── Project.php
│   ├── Product.php               ← with scopes: active(), featured(), lowStock()
│   ├── Order.php + OrderItem.php
│   ├── Coupon.php                ← isValid() + calculateDiscount()
│   ├── Category.php, Manufacturer.php
│   ├── SeoSetting.php, ContactMessage.php
│   └── Models.php                ← (combined file for remaining models)
├── Services/
│   └── InvoiceService.php        ← DomPDF invoice generation
├── Mail/
│   └── ContactFormMail.php
└── Filament/
    ├── Pages/
    │   ├── Dashboard.php         ← Stats + revenue chart + recent orders
    │   └── ReportsPage.php       ← Full analytics page
    └── Resources/
        ├── ProjectResource.php
        ├── ProductResource.php
        ├── OrderResource.php
        ├── CouponResource.php
        └── OtherResources.php    ← Category, Manufacturer, Contact, SEO

database/migrations/
    ├── ..._create_projects_table.php
    ├── ..._create_shop_tables.php          ← categories, manufacturers, products
    ├── ..._create_coupons_table.php
    ├── ..._create_orders_table.php         ← orders + order_items
    └── ..._create_settings_tables.php     ← seo_settings + contact_messages

resources/views/
    ├── pdf/invoice.blade.php               ← PDF invoice template
    ├── emails/contact.blade.php            ← Contact notification email
    └── filament/pages/reports.blade.php   ← Analytics page view
```

---

## Coupon Codes (from seeder)

| Code | Type | Value | Notes |
|---|---|---|---|
| `SAVE20` | Percent | 20% | Max 100 uses, expires 1 year |
| `GAYAN10` | Percent | 10% | Unlimited uses |
| `FLAT500` | Fixed | LKR 500 | Min order LKR 5,000 |
| `NEWUSER30` | Percent | 30% | Max 50 uses, expires 3 months |

---

## Deployment Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Set `PAYHERE_MODE=production`
- [ ] Configure real SMTP credentials for mail
- [ ] Run `php artisan config:cache && php artisan route:cache`
- [ ] Set up queue worker: `php artisan queue:work` (if using queued mail)
- [ ] Set up cron: `* * * * * php /path/to/artisan schedule:run`
- [ ] Point PayHere notify URL to production domain
