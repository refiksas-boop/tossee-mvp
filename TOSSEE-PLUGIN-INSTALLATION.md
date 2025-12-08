# Tossee Core Plugin - Installation & Usage

## 📦 Instaliacijos instrukcijos

### 1. Upload plugin'ą į WordPress

1. Sukurkite naują folderį `wp-content/plugins/tossee-core/`
2. Įkelkite `tossee-core.php` failą į šį folderį
3. Eikite į WordPress Admin → Plugins
4. Aktyvuokite "Tossee Core" plugin'ą

**Alternatyvus būdas:**
```bash
# FTP arba SSH
cd /path/to/wordpress/wp-content/plugins/
mkdir tossee-core
cd tossee-core
# Upload tossee-core.php failą čia
```

### 2. Sukurkite puslapius

Sukurkite šiuos puslapius WordPress admin:

#### Registracijos puslapis
- URL: `/register`
- Content: `[tossee_register_form redirect_to="https://chat.tossee.com/"]`

#### Login puslapis
- URL: `/login`
- Content: `[tossee_login_form redirect_to="https://chat.tossee.com/"]`

### 3. Parametrai (optional)

Galite nurodyti custom redirect URL:

```
[tossee_register_form redirect_to="https://jūsų-url.com/success"]
[tossee_login_form redirect_to="https://jūsų-url.com/dashboard"]
```

---

## 🔑 Kaip veikia ID sistema

### Unikalus Tossee ID
- Kiekvienas registruojamas vartotojas gauna **unikalų `tossee_id`**
- Formatas: `tossee_xxxxxxxxxxxxx` (12 atsitiktinių simbolių)
- Šis ID **niekada nesikeičia** ir yra vartotojo pagrindinis identifikatorius

### Cookie ir Session
Po sėkmingos registracijos arba prisijungimo:
1. **Cookie** `tossee_uid` nustatomas 30 dienų
2. **Session** `$_SESSION['tossee_uid']` taip pat išsaugoma
3. Cookie veikia visame `.tossee.com` domene (subdomenai)

### Prisijungusio vartotojo tikrinimas

```php
// Gauti prisijungusio vartotojo ID
$user_id = tossee_get_current_user_id();

// Gauti visus prisijungusio vartotojo duomenis
$user = tossee_get_current_user();

if ($user) {
    echo "Hello, " . $user->username;
    echo "Your Tossee ID: " . $user->tossee_id;
}
```

---

## 📝 Shortcode'ai

### Registracijos forma
```
[tossee_register_form]
```

Su custom redirect:
```
[tossee_register_form redirect_to="https://chat.tossee.com/"]
```

### Login forma
```
[tossee_login_form]
```

Su custom redirect:
```
[tossee_login_form redirect_to="https://chat.tossee.com/"]
```

---

## 🔐 API funkcijos

### Vartotojo informacija
```php
// Gauti vartotoją pagal Tossee ID
$user = tossee_get_user_by_id('tossee_xxxxxxxxxxxxx');

// Gauti vartotoją pagal email
$user = tossee_get_user_by_email('user@example.com');

// Gauti prisijungusį vartotoją
$current_user = tossee_get_current_user();

// Gauti tik prisijungusio vartotojo ID
$uid = tossee_get_current_user_id();
```

### Session valdymas
```php
// Prisijungti vartotoją programatiškai
tossee_set_uid_cookie($tossee_id);

// Atsijungti
tossee_logout();
```

### Validacijos
```php
// Tikrinti ar username laisvas
if (tossee_is_username_available('johndoe')) {
    // Username available
}

// Tikrinti ar email laisvas
if (tossee_is_email_available('john@example.com')) {
    // Email available
}

// Tikrinti amžių (18+)
if (tossee_is_valid_age('2000-01-15')) {
    // Age is valid
}
```

---

## 👥 Admin Panel

### Vartotojų valdymas
Eikite į: **WordPress Admin → Tossee Users**

Galite:
- Peržiūrėti visus vartotojus
- Blokuoti/atblokuoti
- Ištrinti vartotoją
- Reset slaptažodį (išsiunčia email)
- Pridėti notes

### Vartotojo profilio view
- Spustelėkite "View" prie vartotojo
- Matysite pilną profilį su foto
- Galite pridėti admin notes

---

## 🔄 Integracijos su kitais sistemomis

### Chat sistema (chat.tossee.com)

Kai vartotojas sėkmingai užsiregistruoja arba prisijungia, jis automatiškai nukreipiamas su `tossee_uid` cookie.

Chat pusėje galite skaityti cookie:
```php
// PHP
$tossee_uid = $_COOKIE['tossee_uid'] ?? null;

if ($tossee_uid) {
    // Gauti vartotojo info iš Tossee DB
    $user = tossee_get_user_by_id($tossee_uid);
}
```

```javascript
// JavaScript
const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
};

const tosseeUid = getCookie('tossee_uid');
console.log('User ID:', tosseeUid);
```

---

## 🛡️ Security Features

✅ Password hashing su `PASSWORD_DEFAULT` (bcrypt)
✅ SQL injection apsauga su `$wpdb->prepare()`
✅ XSS apsauga su `esc_html()`, `esc_url()`, `esc_attr()`
✅ CSRF apsauga su WordPress nonces
✅ Age verification (18+)
✅ Photo verification requirement
✅ Email validation
✅ Account blocking system

---

## 📊 Database struktūra

### wp_tossee_users lentelė

| Laukas | Tipas | Aprašymas |
|--------|-------|-----------|
| id | BIGINT | Auto-increment ID |
| tossee_id | VARCHAR(40) | Unikalus Tossee ID |
| username | VARCHAR(60) | Username |
| email | VARCHAR(120) | Email (unique) |
| password_hash | VARCHAR(255) | Bcrypt hash |
| first_name | VARCHAR(60) | Vardas |
| last_name | VARCHAR(60) | Pavardė |
| gender | VARCHAR(10) | Lytis |
| country | VARCHAR(80) | Šalis |
| city | VARCHAR(80) | Miestas |
| dob | DATE | Gimimo data |
| photo | LONGTEXT | Base64 nuotrauka |
| is_blocked | TINYINT(1) | 0=active, 1=blocked |
| notes | TEXT | Admin notes |
| created_at | DATETIME | Registracijos data |
| updated_at | DATETIME | Paskutinis update |

---

## 🎨 Dizainas

Plugin'as naudoja modernų dark theme dizainą su gradient'ais:
- **Background:** `#140D42`
- **Gradients:** Purple → Blue (`#a64dff`, `#00c6ff`, `#0072ff`)
- **Responsive** dizainas
- **Camera integration** registracijai
- **Terms modal** su checkbox

---

## ⚠️ Troubleshooting

### Cookie neveikia subdomenų tarpe

Patikrinkite `tossee_set_uid_cookie()` funkciją ir įsitikinkite, kad domain'as nustatytas teisingai:

```php
$domain = '.tossee.com'; // Su tašku priekyje!
```

### Session nepradedama

Įsitikinkite, kad WordPress leidžia sessions. Pridėkite į `wp-config.php`:
```php
define('WP_SESSION_COOKIE', 'wordpress_session');
```

### Database lentelė nesukuriama

Deaktyvuokite ir vėl aktyvuokite plugin'ą:
1. Plugins → Deactivate "Tossee Core"
2. Plugins → Activate "Tossee Core"

Arba rankiniu būdu SQL:
```sql
CREATE TABLE wp_tossee_users (
    -- ... (žiūrėkite kode)
);
```

---

## 📧 Support

Jei turite klausimų arba reikia pagalbos:
- Patikrinkite `error_log` failą (jei WP_DEBUG įjungtas)
- Peržiūrėkite browser console errors
- Patikrinkite database `wp_tossee_users` lentelę

---

## 🚀 Quick Start Checklist

- [ ] Upload `tossee-core.php` į `wp-content/plugins/tossee-core/`
- [ ] Aktyvuoti plugin'ą WordPress admin
- [ ] Sukurti `/register` puslapį su `[tossee_register_form]`
- [ ] Sukurti `/login` puslapį su `[tossee_login_form]`
- [ ] Testuoti registraciją
- [ ] Testuoti prisijungimą
- [ ] Patikrinti Admin → Tossee Users panel
- [ ] Patikrinti cookie subdomenų tarpe

**Paruošta! 🎉**
