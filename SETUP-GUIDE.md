# Tossee Chat API - Greitas Setup Guide

**Šis guide'as padės jums paeiliui sukonfigūruoti visą sistemą.**

---

## 🎯 Prieš pradedant

Jums reikės:
- SSH/FTP prieigos prie serverio
- Stripe paskyros ([stripe.com](https://stripe.com))
- DB credentials (host, name, user, password)

---

## 📝 ŽINGSNIS 1: Patikrinti Setup

Paleiskite patikrinimo scriptą, kad pamatytumėte kas reikia padaryti:

```bash
cd /home/u234011694/domains/tossee.com/public_html/chat/api
php check-setup.php
```

**Rezultatas parodys:**
- ✅ Kas jau veikia
- ❌ Kas reikia pataisyti
- ⚠️ Kas reikia sukonfigūruoti

---

## 📝 ŽINGSNIS 2: Įdiegti Stripe PHP Library

Jei `check-setup.php` rodo `❌ Stripe library missing`:

```bash
cd /home/u234011694/domains/tossee.com/public_html/chat/api
composer require stripe/stripe-php
```

**Patikrinti ar įdiegta:**
```bash
ls -la vendor/autoload.php
# Turėtų parodyti failą
```

---

## 📝 ŽINGSNIS 3: Sukonfigūruoti Database

### A) Rasti DB credentials

**Per cPanel → MySQL Databases** arba hosting kontrolę rasite:
- Database Name (pvz: `u234011694_tossee`)
- Database User (pvz: `u234011694_user`)
- Database Password (slaptažodis)
- Database Host (dažniausiai `localhost`)

### B) Atnaujinti config.php

Atidaryti `chat/api/config.php` ir pakeisti:

```php
define('DB_HOST', 'localhost');              // Jūsų DB host
define('DB_NAME', 'u234011694_tossee');      // Jūsų DB pavadinimas
define('DB_USER', 'u234011694_user');        // Jūsų DB user
define('DB_PASS', 'tikras_slaptazodis');     // Jūsų DB password
```

### C) Testuoti DB connection

```bash
php check-setup.php
# Turėtų rodyti: [4/10] Testing database connection... ✅ Connected
```

---

## 📝 ŽINGSNIS 4: Atnaujinti Database Schema

Pridėti mokėjimų laukus į `wp_tossee_users` lentelę:

```bash
cd /home/u234011694/domains/tossee.com/public_html/chat/api
php migrate-database.php
```

**Turėtų parodyti:**
```
=== Tossee Database Migration ===

✅ Table wp_tossee_users exists

➕ Adding column 'free_used_seconds'... ✅ SUCCESS
➕ Adding column 'paid_seconds'... ✅ SUCCESS
➕ Adding column 'unlimited_until'... ✅ SUCCESS
➕ Adding column 'call_started_at'... ✅ SUCCESS

=== Migration Summary ===
✅ Added: 4 columns
⏭  Skipped: 0 columns
❌ Errors: 0

✅ Migration completed successfully!
```

**Jei jau buvo paleista anksčiau:**
```
⏭  Column 'free_used_seconds' already exists - skipping
⏭  Column 'paid_seconds' already exists - skipping
...
✅ Migration completed successfully!
```

---

## 📝 ŽINGSNIS 5: Gauti Stripe API Keys

### A) Prisijungti prie Stripe Dashboard

1. Eiti į [dashboard.stripe.com](https://dashboard.stripe.com)
2. Įsijungti **Test Mode** (viršutiniame dešiniame kampe toggle)

### B) Gauti API Keys

1. Eiti į **Developers → API keys**
2. Nukopijuoti:
   - **Secret key** (`sk_test_...`)
   - **Publishable key** (`pk_test_...`)

### C) Atnaujinti config.php

Atidaryti `chat/api/config.php` ir pakeisti:

```php
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_ACTUAL_KEY_HERE');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_ACTUAL_KEY_HERE');
```

**⚠️ SVARBU:** Test mode yra `sk_test_...`, Production yra `sk_live_...`

---

## 📝 ŽINGSNIS 6: Sukonfigūruoti Stripe Webhook

### A) Sukurti Webhook Endpoint

1. Eiti į [Stripe Dashboard → Webhooks](https://dashboard.stripe.com/webhooks)
2. Spausti **"Add endpoint"**
3. Užpildyti:
   - **Endpoint URL**: `https://tossee.com/chat/api/stripe-webhook.php`
   - **Events to send**: Ieškoti ir pasirinkti `checkout.session.completed`
4. Spausti **"Add endpoint"**

### B) Gauti Webhook Secret

1. Po sukūrimo, spausti ant endpoint'o
2. Nukopijuoti **Signing secret** (`whsec_...`)

### C) Atnaujinti config.php

```php
define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET_HERE');
```

---

## 📝 ŽINGSNIS 7: Patikrinti visą Setup

```bash
cd /home/u234011694/domains/tossee.com/public_html/chat/api
php check-setup.php
```

**Turėtų parodyti:**
```
=== Tossee Setup Checker ===

[1/10] Checking PHP version... ✅ 8.x.x
[2/10] Checking Stripe library... ✅ Found
[3/10] Checking database config... ✅ Configured
[4/10] Testing database connection... ✅ Connected
[5/10] Checking wp_tossee_users table... ✅ Exists
[6/10] Checking payment columns... ✅ All present
[7/10] Checking Stripe API key... ✅ Configured (TEST mode)
[8/10] Checking webhook secret... ✅ Configured
[9/10] Checking .htaccess... ✅ Configured
[10/10] Checking file permissions... ✅ Writable

=== Summary ===
✅ Passed: 10/10
❌ Errors: 0
⚠️  Warnings: 0

🎉 All checks passed! System ready for testing.
```

---

## 📝 ŽINGSNIS 8: Testuoti API Endpoints

### A) Ping Test

```bash
curl https://tossee.com/chat/api/ping.php
```

**Turėtų grąžinti:**
```
PING OK
Timestamp: 2026-01-12 10:30:00
Tossee Chat API is running
```

### B) Access Check

```bash
curl "https://tossee.com/chat/api/access.php?uid=tossee_test123"
```

**Turėtų grąžinti JSON:**
```json
{
  "uid": "tossee_test123",
  "allowed": false,
  "unlimited": false,
  "remaining_seconds": 0,
  "unlimited_until": null,
  "free_remaining": 0,
  "paid_remaining": 0
}
```

Arba:
```json
{
  "error": "User not found"
}
```

---

## 📝 ŽINGSNIS 9: Testuoti Payment Flow

### A) Atsidaryti Pricing Page

Naršyklėje atidaryti:
```
https://tossee.com/pricing?uid=tossee_JŪSŲ_TIKRAS_ID
```

### B) Pasirinkti Planą

1. Spausti **"Buy Now"** ant bet kurio plano (pvz: +30 Minutes)
2. Turėtų nukreipti į Stripe Checkout
3. Jei klaida - tikrinti `chat/api/error.log`

### C) Užpildyti Test Card

Stripe test mode kortelės:
- **Card number**: `4242 4242 4242 4242`
- **Expiry**: Bet kuri ateities data (pvz: `12/34`)
- **CVC**: Bet koks 3 skaitmenų kodas (pvz: `123`)
- **Email**: Bet koks email

### D) Patvirtinti Mokėjimą

1. Spausti **"Pay"**
2. Turėtų nukreipti atgal į `chat.tossee.com?payment=success`
3. Patikrinti DB ar laikas pridėtas

### E) Tikrinti Webhook

1. Eiti į [Stripe Dashboard → Webhooks](https://dashboard.stripe.com/webhooks)
2. Spausti ant savo endpoint'o
3. Tikrinti **"Recent deliveries"**
4. Turėtų rodyti `checkout.session.completed` su **✅ 200** status

Jei rodo **❌ 400/500**:
```bash
tail -f /home/u234011694/domains/tossee.com/public_html/chat/api/error.log
```

---

## 📝 ŽINGSNIS 10: Patikrinti DB Update

Po sėkmingo mokėjimo, tikrinti ar DB atnaujintas:

```sql
SELECT tossee_id, free_used_seconds, paid_seconds, unlimited_until
FROM wp_tossee_users
WHERE tossee_id = 'tossee_YOUR_ID';
```

**Turėtų parodyti:**
- **+30 min planas**: `paid_seconds = 1800`
- **24h Unlimited**: `unlimited_until = 2026-01-13 10:30:00`

---

## 🚀 Production Deployment

Kai viskas veikia **test mode**, perjungti į **live mode**:

### 1. Gauti Live API Keys

Stripe Dashboard → Išjungti Test Mode → API Keys:
- `sk_live_...`
- `pk_live_...`

### 2. Sukurti Live Webhook

Stripe Dashboard (Live mode) → Webhooks → Add endpoint:
- URL: `https://tossee.com/chat/api/stripe-webhook.php`
- Event: `checkout.session.completed`
- Nukopijuoti `whsec_...` (live secret)

### 3. Atnaujinti config.php

```php
define('STRIPE_SECRET_KEY', 'sk_live_...');          // LIVE key
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_...');     // LIVE key
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');        // LIVE webhook secret
```

### 4. Testuoti su tikra kortele

⚠️ **SVARBU**: Live mode naudoja tikrus pinigus!

---

## 🐛 Troubleshooting

### Problema: "vendor/autoload.php not found"

```bash
cd /home/u234011694/domains/tossee.com/public_html/chat/api
composer require stripe/stripe-php
```

### Problema: "Database connection failed"

Tikrinti `config.php` DB credentials:
```bash
php -r "
require 'config.php';
echo 'Host: ' . DB_HOST . PHP_EOL;
echo 'Name: ' . DB_NAME . PHP_EOL;
echo 'User: ' . DB_USER . PHP_EOL;
"
```

### Problema: Webhook returns 400/500

```bash
# Žiūrėti real-time logus
tail -f chat/api/error.log

# Arba visus logus
cat chat/api/error.log
```

### Problema: WordPress rodo "Critical Error"

Tikrinti `.htaccess`:
```bash
cat chat/api/.htaccess | grep RewriteEngine
# Turėtų rodyti: RewriteEngine Off
```

---

## ✅ Checklist

- [ ] ✅ PHP 7.4+ installed
- [ ] ✅ Stripe library installed (`vendor/autoload.php`)
- [ ] ✅ `config.php` updated with DB credentials
- [ ] ✅ Database connection works
- [ ] ✅ Migration completed (4 new columns)
- [ ] ✅ Stripe API keys configured
- [ ] ✅ Stripe webhook created and configured
- [ ] ✅ `check-setup.php` shows all green
- [ ] ✅ `ping.php` returns PING OK
- [ ] ✅ Test payment successful
- [ ] ✅ Webhook receives 200 OK
- [ ] ✅ Database updated after payment

---

## 📞 Pagalba

Jei kyla problemų:
1. Paleisti `php check-setup.php`
2. Tikrinti `chat/api/error.log`
3. Stripe Dashboard → Webhooks → Recent deliveries
4. `chat/api/README.md` - pilna dokumentacija

---

**Sėkmės! 🚀**
