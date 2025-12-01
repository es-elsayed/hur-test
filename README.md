# Hurr - نظام إدارة الرصيد المالي

نظام Laravel شامل لإدارة العمليات المالية (الإيداعات والسحوبات) مع واجهة مستخدم عربية متكاملة.

##  التشغيل السريع

### 1. تثبيت المتطلبات
```bash
composer install
```
### 2. إعداد ملف البيئة `.env`
```bash
cp .env.example .env
php artisan key:generate

-- setup DB

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3️. إعداد قاعدة البيانات
```bash
php artisan migrate:fresh --seed
```

هذا الأمر سيقوم بـ:
- إنشاء جداول قاعدة البيانات
- إضافة 50 عملية مالية (30 إيداع + 20 سحب)
- إضافة 20 عضو (10 عملاء + 10 فريلانسرز)
- إضافة 17 مشروع
- إنشاء 20 كود خصم مع 10 استخدامات

**ملاحظة:** إذا كانت البيانات موجودة بالفعل، لا حاجة لتشغيل هذا الأمر مرة أخرى.

### 4️ تشغيل السيرفر
```bash
php artisan serve
```

### 5️ تسجيل الدخول
افتح المتصفح على:
```
http://127.0.0.1:8000/login
```

استخدم أحد الحسابات من قاعدة البيانات:
- **البريد الإلكتروني:** أي بريد من جدول `members` (مثلاً: يمكنك استخدام `php artisan tinker` ثم `Member::first()->email`)
- **كلمة المرور:** `password` (الافتراضية لجميع الحسابات)

### 6️ الوصول للوحة التحكم
بعد تسجيل الدخول، سيتم توجيهك تلقائياً إلى:
```
http://127.0.0.1:8000/balance
```

---

##  المميزات

###  نظام المصادقة
- تسجيل دخول بسيط باستخدام جدول `members`
- حماية صفحات Dashboard بوسيط `auth`
- تمييز بين العملاء (Clients) والفريلانسرز (Freelancers)
- عرض معلومات المستخدم في Sidebar

###  صفحة الرصيد
- عرض الرصيد الإجمالي المحسوب تلقائياً للمستخدم المسجل
- جدول العمليات المالية مع Ajax Pagination
- تفاصيل كل عملية في Modal منبثق
- **إيداع رصيد** (للعملاء فقط) مباشرة من Dashboard
- **سحب رصيد** (للعملاء والفريلانسرز) مباشرة من Dashboard
- دعم كامل للغة العربية (RTL)
- تصميم responsive مع Tailwind CSS

###  العمليات المالية
**الإيداعات (Income):**
- متاحة للعملاء فقط
- المبلغ الأساسي + العمولة (8% للعملاء)
- الضريبة (15% من العمولة)
- الخصم من كوبونات الخصم (إن وجد)
- بيانات الدفع (طريقة الدفع، رقم البطاقة، إلخ)
- **يمكن إنشاء إيداع مباشرة من Dashboard** عبر زر "إيداع رصيد"

**السحوبات (Outcome):**
- متاحة للعملاء والفريلانسرز
- المبلغ الأساسي - العمولة (8% للعملاء، 15% للفريلانسرز) - الضريبة
- بيانات السحب (رقم الحساب البنكي، اسم البنك، إلخ)
- **يمكن إنشاء سحب مباشرة من Dashboard** عبر زر "سحب الرصيد"

###  التفاصيل المعروضة في Modal
- المبلغ الأساسي، العمولة، الضريبة، الخصم
- المبلغ الإجمالي
- رقم العملية (Transaction Reference)
- معلومات العضو (الاسم، النوع)
- معلومات المشروع (إن وجد)
- الحالة (مكتمل/قيد التنفيذ)
- التاريخ والوقت
- بيانات إضافية مترجمة للعربية

---

##  البنية التقنية

### Backend
- **Laravel 11** - Framework
- **SQLite** - Database
- **Enums** - ProcessType, ActionStatus
- **Services** - BalanceService لمنطق العمليات
- **Resources** - API Response Transformers
- **Policies** - Authorization
- **Factories & Seeders** - Test Data

### Frontend
- **Blade Templates** - Server-side rendering
- **Tailwind CSS** - Styling (via CDN)
- **jQuery** - Ajax requests
- **JavaScript** - Dynamic UI

### API Endpoints
```
GET  /api/balances?page={page}              # قائمة العمليات (مع pagination)
GET  /api/balances/{id}                     # تفاصيل عملية محددة
POST /api/balances/deposit                  # إنشاء عملية إيداع (يُستخدم من Dashboard)
POST /api/balances/withdraw                 # إنشاء عملية سحب (يُستخدم من Dashboard)
POST /api/balances/calculate-deposit-fees   # حساب رسوم الإيداع
POST /api/balances/calculate-withdrawal-fees # حساب رسوم السحب
PATCH /api/balances/{id}/complete           # إكمال عملية
```

**ملاحظة:** عمليات الإيداع والسحب من Dashboard تستخدم نفس REST API الموجود.

---

##  الملفات الرئيسية

### Controllers
- `app/Http/Controllers/Auth/LoginController.php` - تسجيل الدخول والخروج
- `app/Http/Controllers/Web/BalanceController.php` - عرض الصفحة
- `app/Http/Controllers/Api/BalanceController.php` - API Endpoints

### Services
- `app/Services/BalanceService.php` - منطق العمليات المالية

### Models
- `app/Models/Balance.php` - العمليات المالية
- `app/Models/Member.php` - الأعضاء (عملاء/فريلانسرز)
- `app/Models/Project.php` - المشاريع
- `app/Models/Transaction.php` - معاملات الدفع
- `app/Models/Voucher.php` - كوبونات الخصم
- `app/Models/Invoice.php` - الفواتير

### Views
- `resources/views/auth/login.blade.php` - صفحة تسجيل الدخول
- `resources/views/layouts/dashboard.blade.php` - Layout رئيسي
- `resources/views/pages/balance/index.blade.php` - صفحة الرصيد
- `resources/views/components/layout/*` - Header, Sidebar, Footer
- `resources/views/components/balance/deposit-modal.blade.php` - مودال الإيداع
- `resources/views/components/balance/withdraw-modal.blade.php` - مودال السحب

### Seeders
- `database/seeders/CompleteDataSeeder.php` - إنشاء بيانات شاملة

---

##  الإعدادات

### رسوم العمولات
تُعرّف في `config/fees.php`:
```php
'commission' => [
    'client' => 0.08,        // 8% للعملاء
    'freelancer' => 0.15,    // 15% للفريلانسرز
],
'vat' => 0.15,              // 15% ضريبة القيمة المضافة
```

### الحسابات التلقائية
**للإيداع:**
```
المبلغ الإجمالي = المبلغ الأساسي + العمولة + الضريبة - الخصم
```

**للسحب:**
```
المبلغ الصافي = المبلغ الأساسي - العمولة - الضريبة
```
