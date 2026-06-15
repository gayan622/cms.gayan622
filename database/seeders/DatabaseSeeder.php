<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Project;
use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ─────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@gayan622.online'],
            [
                'name'     => 'Gayan Admin',
                'password' => Hash::make('Admin@1234'),
            ]
        );

        // ── Projects ───────────────────────────────────────────────────────────
        $projects = [
            ['title' => 'StudyAdvisor ERP System',    'short_description' => 'Full ERP for student management with mobile app.',    'category' => 'ERP System',   'client' => 'StudyAdvisor (Pvt) Ltd',          'year' => '2019–2025', 'technologies' => ['Laravel','MySQL','Java','Android'], 'featured' => true,  'emoji' => '🎓', 'gradient' => 'from-violet-600 to-indigo-600'],
            ['title' => 'CeyNet Web Portal',           'short_description' => 'Corporate portal with CMS and client dashboard.',     'category' => 'Web Portal',   'client' => 'CeyNet Software Solution (Pvt) Ltd', 'year' => '2007–2019', 'technologies' => ['PHP','jQuery','MySQL'],             'featured' => true,  'emoji' => '🌐', 'gradient' => 'from-pink-500 to-rose-500'],
            ['title' => 'Student Tracker App',         'short_description' => 'Android app for attendance and progress tracking.',   'category' => 'Mobile App',   'client' => 'StudyAdvisor (Pvt) Ltd',          'year' => '2021',      'technologies' => ['Android','Java','REST API'],        'featured' => true,  'emoji' => '📱', 'gradient' => 'from-cyan-500 to-sky-600'],
            ['title' => 'E-Commerce Platform',         'short_description' => 'Full shop with cart, coupons, and PayHere checkout.', 'category' => 'E-Commerce',   'client' => 'Personal Project',                'year' => '2024',      'technologies' => ['Next.js','Laravel','PayHere'],      'featured' => true,  'emoji' => '🛒', 'gradient' => 'from-emerald-500 to-teal-600'],
            ['title' => 'School Management System',    'short_description' => 'Multi-branch admin with teacher and student portals.','category' => 'ERP System',   'client' => 'Private School, Colombo',         'year' => '2022',      'technologies' => ['Laravel','Vue.js','MySQL'],         'featured' => false, 'emoji' => '🏫', 'gradient' => 'from-amber-500 to-orange-500'],
            ['title' => 'Real Estate Listings Portal', 'short_description' => 'Property portal with search, filters, and CRM.',     'category' => 'Web Portal',   'client' => 'Lanka Properties',                'year' => '2023',      'technologies' => ['Next.js','Laravel','Google Maps'],  'featured' => false, 'emoji' => '🏠', 'gradient' => 'from-blue-500 to-indigo-500'],
        ];

        foreach ($projects as $i => $data) {
            Project::firstOrCreate(['title' => $data['title']], array_merge($data, ['sort_order' => $i, 'is_active' => true]));
        }

        // ── Categories ─────────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Templates',  'description' => 'Ready-to-use website templates'],
            ['name' => 'UI Kits',    'description' => 'Component libraries and design systems'],
            ['name' => 'Full Stack', 'description' => 'Complete frontend + backend starter projects'],
            ['name' => 'Backend',    'description' => 'API boilerplates and backend starters'],
            ['name' => 'Mobile',     'description' => 'Android and mobile app starters'],
        ];

        foreach ($categories as $i => $cat) {
            Category::firstOrCreate(['name' => $cat['name']], array_merge($cat, ['is_active' => true, 'sort_order' => $i]));
        }

        // ── Manufacturers ──────────────────────────────────────────────────────
        $mfr = Manufacturer::firstOrCreate(['name' => 'Gayan622 Studio'], [
            'website'   => 'https://gayan622.online',
            'is_active' => true,
        ]);

        // ── Products ───────────────────────────────────────────────────────────
        $catMap = Category::pluck('id', 'name');

        $products = [
            ['name' => 'Business Website Template',  'category' => 'Templates',  'price' => 5000, 'sale_price' => 3500, 'stock' => 99, 'short_description' => 'Responsive Next.js template for corporate sites.',          'emoji' => '📦', 'gradient' => 'from-violet-500 to-purple-600', 'featured' => true],
            ['name' => 'Admin Dashboard UI Kit',     'category' => 'UI Kits',    'price' => 8000, 'sale_price' => 5500, 'stock' => 99, 'short_description' => 'React admin panel with 30+ components and dark mode.',       'emoji' => '🎨', 'gradient' => 'from-blue-500 to-cyan-500',    'featured' => true],
            ['name' => 'E-Commerce Full Stack',      'category' => 'Full Stack', 'price' => 12000,'sale_price' => 9000, 'stock' => 99, 'short_description' => 'Next.js + Laravel shop with PayHere integration.',            'emoji' => '🛒', 'gradient' => 'from-pink-500 to-rose-500',    'featured' => true],
            ['name' => 'Android App Starter Kit',    'category' => 'Mobile',     'price' => 4200, 'sale_price' => null, 'stock' => 99, 'short_description' => 'Android boilerplate with auth, API client, and nav.',         'emoji' => '📱', 'gradient' => 'from-emerald-500 to-teal-500', 'featured' => false],
            ['name' => 'Laravel API Boilerplate',    'category' => 'Backend',    'price' => 6000, 'sale_price' => null, 'stock' => 99, 'short_description' => 'Laravel 11 REST API with Sanctum, roles, and CI/CD.',          'emoji' => '🔧', 'gradient' => 'from-red-500 to-orange-500',   'featured' => false],
            ['name' => 'SEO Landing Page Template',  'category' => 'Templates',  'price' => 3500, 'sale_price' => null, 'stock' => 99, 'short_description' => 'High-converting landing page with Core Web Vitals score.',     'emoji' => '🚀', 'gradient' => 'from-amber-500 to-yellow-500', 'featured' => false],
        ];

        foreach ($products as $i => $data) {
            $catId    = $catMap[$data['category']] ?? 1;
            $prodData = array_merge(
                array_diff_key($data, ['category' => '']),
                ['category_id' => $catId, 'manufacturer_id' => $mfr->id, 'is_active' => true, 'sort_order' => $i]
            );
            Product::firstOrCreate(['name' => $data['name']], $prodData);
        }

        // ── Coupons ────────────────────────────────────────────────────────────
        $coupons = [
            ['code' => 'SAVE20',     'discount_type' => 'percent', 'discount_value' => 20, 'description' => '20% off all products',             'max_uses' => 100, 'expires_at' => Carbon::now()->addYear()],
            ['code' => 'GAYAN10',    'discount_type' => 'percent', 'discount_value' => 10, 'description' => '10% welcome discount',             'max_uses' => null],
            ['code' => 'FLAT500',    'discount_type' => 'fixed',   'discount_value' => 500,'description' => 'LKR 500 off on orders over 5,000', 'min_order' => 5000],
            ['code' => 'NEWUSER30',  'discount_type' => 'percent', 'discount_value' => 30, 'description' => '30% for new customers',            'max_uses' => 50,  'expires_at' => Carbon::now()->addMonths(3)],
        ];

        foreach ($coupons as $coupon) {
            Coupon::firstOrCreate(['code' => $coupon['code']], array_merge($coupon, ['is_active' => true]));
        }

        // ── SEO Settings ───────────────────────────────────────────────────────
        $pages = [
            ['page' => 'home',    'title' => 'Gayan622 Online — Web Developer & Designer in Sri Lanka', 'description' => 'Web Developer & Designer with 15+ years of experience. Specialising in Next.js, Laravel, React and Android. Based in Negombo, Sri Lanka.',     'keywords' => 'web developer sri lanka, laravel developer negombo, next.js developer, android developer sri lanka'],
            ['page' => 'about',   'title' => 'About — Gayan622 Online',                                 'description' => 'Career history, skills, and journey of Gayan — Web Developer & Designer from Negombo, Sri Lanka.',                                           'keywords' => 'gayan web developer about, career history, web design experience sri lanka'],
            ['page' => 'blog',    'title' => 'Projects — Gayan622 Online',                               'description' => 'Portfolio of web, mobile, and ERP projects built over 15+ years. Next.js, Laravel, Android development.',                                     'keywords' => 'web development portfolio, laravel projects, android app development, erp system sri lanka'],
            ['page' => 'shop',    'title' => 'Shop — Gayan622 Online',                                   'description' => 'Premium web development templates, UI kits, and full-stack boilerplates. Next.js, Laravel, Android starters.',                                'keywords' => 'next.js templates, laravel boilerplate, web development templates sri lanka'],
            ['page' => 'contact', 'title' => 'Contact — Gayan622 Online',                               'description' => 'Get in touch with Gayan for web development, mobile apps, or ERP solutions. Based in Negombo, Sri Lanka.',                                     'keywords' => 'hire web developer sri lanka, contact web designer negombo, freelance developer'],
        ];

        foreach ($pages as $page) {
            SeoSetting::firstOrCreate(['page' => $page['page']], $page);
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('   Admin login: admin@gayan622.online / Admin@1234');
        $this->command->info('   Coupon codes: SAVE20, GAYAN10, FLAT500, NEWUSER30');
    }
}
