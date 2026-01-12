# Tossee Chat API - Payment Integration

Custom chat API su Stripe mokėjimų integracija WordPress svetainei.

## 📁 Struktūra

```
chat/api/
├── .htaccess              # Apache rewrite rules (blokuoja WP perimą)
├── config.php             # DB ir Stripe konfigūracija
├── vendor/                # Stripe PHP biblioteka (composer)
├── ping.php               # Health check endpoint
├── access.php             # Tikrina vartotojo prieigą
├── create-checkout.php    # Kuria Stripe checkout session
├── stripe-webhook.php     # Priima Stripe webhook events
├── matching.php           # Video chat matching (stub)
└── queue-manager.php      # Queue valdymas (stub)
```

## ⚙️ Setup Instrukcijos

### 1. Atnaujinti Database Schema

Paleisti šį SQL tiesiai DB arba užtikrinti, kad `database-setup.php` būtų paleistas:

```sql
ALTER TABLE wp_tossee_users
ADD COLUMN free_used_seconds INT(11) NOT NULL DEFAULT 0,
ADD COLUMN paid_seconds INT(11) NOT NULL DEFAULT 0,
ADD COLUMN unlimited_until DATETIME NULL DEFAULT NULL,
ADD COLUMN call_started_at DATETIME NULL DEFAULT NULL;
```

### 2. Užpildyti `config.php` Credentials

Atidaryti `chat/api/config.php` ir pakeisti:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'u234011694_tossee');  // Jūsų DB pavadinimas
define('DB_USER', 'u234011694_user');    // Jūsų DB user
define('DB_PASS', 'jūsų_slaptažodis');   // Jūsų DB password

// Stripe (iš https://dashboard.stripe.com/apikeys)
define('STRIPE_SECRET_KEY', 'sk_test_...');     // Secret key
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');    // Webhook signing secret
```

### 3. Sukonfigūruoti Stripe Webhook

1. Eiti į [Stripe Dashboard → Webhooks](https://dashboard.stripe.com/webhooks)
2. Sukurti naują endpoint:
   - **URL**: `https://tossee.com/chat/api/stripe-webhook.php`
   - **Events**: `checkout.session.completed`
3. Nukopijuoti **Signing secret** (`whsec_...`) į `config.php`

### 4. Patikrinti, kad viskas veikia

```bash
# 1. Tikrinti, ar Stripe biblioteka įdiegta
ls -la chat/api/vendor/autoload.php

# 2. Tikrinti, ar ping.php veikia
curl https://tossee.com/chat/api/ping.php
# Turėtų grąžinti: PING OK

# 3. Tikrinti access.php
curl "https://tossee.com/chat/api/access.php?uid=tossee_test123"
# Turėtų grąžinti JSON su allowed/remaining info
```

## 🔌 API Endpoints

### 1. `ping.php` - Health Check
```bash
GET /chat/api/ping.php
```
**Response**: Plain text "PING OK"

---

### 2. `access.php` - Tikrinti Prieigą
```bash
GET /chat/api/access.php?uid=tossee_yTrDnR7y
```

**Response**:
```json
{
  "uid": "tossee_yTrDnR7y",
  "allowed": true,
  "unlimited": false,
  "remaining_seconds": 1800,
  "unlimited_until": null,
  "free_remaining": 1800,
  "paid_remaining": 0
}
```

**Logika**:
- `unlimited_until > now` → unlimited access
- `remaining_seconds > 0` → allowed
- `remaining_seconds === 0` → show paywall

---

### 3. `create-checkout.php` - Sukurti Mokėjimo Sessiją
```bash
POST /chat/api/create-checkout.php
Content-Type: application/json

{
  "uid": "tossee_yTrDnR7y",
  "plan": "addon_30min"
}
```

**Plans**:
- `addon_30min` - +30 minutes ($0.99)
- `unlimited_24h` - 24h unlimited ($1.99)
- `unlimited_7d` - 7 days ($9.99)
- `unlimited_30d` - 30 days ($19.99)

**Response**:
```json
{
  "success": true,
  "sessionId": "cs_test_...",
  "url": "https://checkout.stripe.com/pay/cs_test_..."
}
```

**Frontend Integration**:
```javascript
async function buyPlan(uid, plan) {
  const response = await fetch('https://tossee.com/chat/api/create-checkout.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ uid, plan })
  });

  const data = await response.json();

  if (data.url) {
    window.location.href = data.url; // Redirect to Stripe
  }
}

// Usage:
buyPlan('tossee_yTrDnR7y', 'addon_30min');
```

---

### 4. `stripe-webhook.php` - Webhook Handler

**Automatinis**: Stripe siunčia `checkout.session.completed` event.

**Procesas**:
1. Verificuoja Stripe signature
2. Paima `uid` iš `client_reference_id` arba `metadata.uid`
3. Atnaujina DB:
   - **addon_30min**: `paid_seconds += 1800`
   - **unlimited_24h**: `unlimited_until = now + 24 hours`
   - **unlimited_7d**: `unlimited_until = now + 7 days`
   - **unlimited_30d**: `unlimited_until = now + 30 days`
4. Grąžina `200 OK` Stripe'ui

**Test Webhook**:
```bash
stripe trigger checkout.session.completed
```

---

## 🔒 Security

### `.htaccess` Apsauga

`.htaccess` failas užtikrina:
- WordPress **neperrašo** `/chat/api/*.php` requestų
- `config.php` **nepasiekiamas** per naršyklę
- CORS headers leidžia API calls

### Stripe Signature Validation

`stripe-webhook.php` **būtinai** tikrina webhook signature:
```php
$event = \Stripe\Webhook::constructEvent(
    $payload,
    $sig_header,
    STRIPE_WEBHOOK_SECRET
);
```

Jei signature neteisingas → grąžina `400 Bad Request`.

---

## 🧪 Testing Flow

### Test Mode

1. **Stripe Dashboard** → įjungti **Test mode**
2. Naudoti test keys (`sk_test_...`, `pk_test_...`)
3. Test korteles: `4242 4242 4242 4242` (Visa)

### Full Payment Flow Test

```bash
# 1. Tikrinti pradinę būseną
curl "https://tossee.com/chat/api/access.php?uid=tossee_test123"
# free_remaining: 1800, paid_remaining: 0

# 2. Sukurti checkout session
curl -X POST https://tossee.com/chat/api/create-checkout.php \
  -H "Content-Type: application/json" \
  -d '{"uid":"tossee_test123","plan":"addon_30min"}'

# 3. Atsidaryti grąžintą URL naršyklėje
# 4. Užpildyti test card info ir patvirtinti

# 5. Stripe siunčia webhook → stripe-webhook.php atnaujina DB

# 6. Tikrinti atnaujintą būseną
curl "https://tossee.com/chat/api/access.php?uid=tossee_test123"
# free_remaining: 1800, paid_remaining: 1800
```

---

## 🐛 Troubleshooting

### Problem: `vendor/autoload.php` not found

**Solution**:
```bash
cd /path/to/public_html/chat/api
composer require stripe/stripe-php
```

### Problem: WordPress rodo "Kritinė klaida"

**Priežastis**: WP routing perrašo `/chat/api/*.php`

**Solution**: Patikrinti `.htaccess`:
```apache
RewriteEngine Off
```

### Problem: Webhook gauna `400 ERR`

**Galimos priežastys**:
1. Neteisingas `STRIPE_WEBHOOK_SECRET`
2. `vendor/autoload.php` neegzistuoja
3. DB connection klaida

**Debug**:
```bash
tail -f chat/api/error.log
```

### Problem: `config.php` nepasiekiamas

**Priežastis**: `.htaccess` blokuoja (security feature)

**Sprendimas**: Tai normalu! `config.php` turėtų būti blokuotas per web.

---

## 📊 Database Schema

### `wp_tossee_users` Papildomi laukai:

| Laukas | Tipas | Aprašymas |
|--------|-------|-----------|
| `free_used_seconds` | INT | Panaudoti free sekundės (max 1800) |
| `paid_seconds` | INT | Likusios paid sekundės (iš addon planų) |
| `unlimited_until` | DATETIME | Unlimited prieiga iki šios datos (NULL = nėra) |
| `call_started_at` | DATETIME | Call pradžios laikas (tracking) |

### Logika:

```
if (unlimited_until > now) {
  → ALLOWED (unlimited)
} else {
  remaining = (1800 - free_used_seconds) + paid_seconds
  if (remaining > 0) {
    → ALLOWED
    → Decrement free_used_seconds arba paid_seconds
  } else {
    → SHOW PAYWALL
  }
}
```

---

## 🚀 Production Checklist

- [ ] Užpildyti `config.php` su production DB credentials
- [ ] Pakeisti Stripe keys iš **test** į **live** mode
- [ ] Sukonfigūruoti Stripe webhook su **live** URL
- [ ] Patikrinti `.htaccess` veikimą
- [ ] Test full payment flow su live card
- [ ] Sukurti DB backup
- [ ] Monitorinti `error.log` failus
- [ ] Stripe Dashboard → Įjungti email notifications

---

## 📞 Support

Jei kyla problemų:
1. Tikrinti `chat/api/error.log`
2. Stripe Dashboard → Webhooks → Recent deliveries
3. DB: `SELECT * FROM wp_tossee_users WHERE tossee_id = 'xxx'`

---

**Sukurta**: 2026-01-12
**Versija**: 1.0
**Claude Code**: Setup-Chat-API-4RJfQ
