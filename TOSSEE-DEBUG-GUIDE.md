# Tossee Plugin Debug Guide

## ⚠️ PROBLEMA: Vartotojai neužsiregistruoja

Jei registracijos forma neveikia, sekite šiuos žingsnius:

---

## 1. ĮJUNKITE DEBUG MODE

### Atidaryti `wp-config.php`

Raskite šias eilutes ir pakeiskite:

```php
// BEFORE:
define('WP_DEBUG', false);

// AFTER:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

Jei šių eilučių nėra, pridėkite **PRIEŠ** eilutę `/* That's all, stop editing! */`:

```php
// Enable Debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

/* That's all, stop editing! Happy publishing. */
```

---

## 2. PALEISTI DEBUG SCRIPT'Ą

1. Upload `debug-tossee.php` į WordPress **root** direktoriją (ten pat kur `wp-config.php`)

2. Atidaryti naršyklėje:
   ```
   https://jūsų-domenas.com/debug-tossee.php
   ```

3. Patikrinti:
   - ✓ Plugin is ACTIVE
   - ✓ Table exists
   - ✓ Test insert successful

4. Jei kažkas **RAUDONA (✗)** - pažymėkite ir sekite instrukcijas

---

## 3. PATIKRINTI DATABASE LENTELĘ

### Per phpMyAdmin arba MySQL:

```sql
SHOW TABLES LIKE 'wp_tossee_users';
```

Jei lentelė neegzistuoja, sukurkite rankiniu būdu:

```sql
CREATE TABLE `wp_tossee_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tossee_id` varchar(40) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(60) DEFAULT '' NOT NULL,
  `last_name` varchar(60) DEFAULT '' NOT NULL,
  `gender` varchar(10) DEFAULT '' NOT NULL,
  `country` varchar(80) DEFAULT '' NOT NULL,
  `city` varchar(80) DEFAULT '' NOT NULL,
  `dob` date NOT NULL,
  `photo` longtext NOT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `tossee_id` (`tossee_id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 4. PATIKRINTI ERROR LOG

### Raskite `debug.log` failą:

```
/wp-content/debug.log
```

### Per FTP/SSH:

```bash
tail -f /path/to/wordpress/wp-content/debug.log
```

### Ieškokite eilučių su `[TOSSEE]`:

```
[TOSSEE][info] === Registration handler called ===
[TOSSEE][info] Attempting to insert user: username=testuser, email=test@example.com
[TOSSEE][error] Registration failed: DB error - ...
```

**Jei matote error** - nukopijuokite ir ieškokite sprendimo žemiau.

---

## 5. DAŽNIAUSIOS PROBLEMOS IR SPRENDIMAI

### Problema 1: "Table doesn't exist"

**Sprendimas:**
1. Deaktyvuoti plugin'ą: Plugins → Deactivate
2. Aktyvuoti iš naujo: Plugins → Activate
3. Arba sukurti lentelę rankiniu būdu (žiūrėti #3)

---

### Problema 2: "Photo data too large" arba "max_allowed_packet"

**Sprendimas:** Padidinti MySQL limits

Pridėti į `wp-config.php`:

```php
@ini_set('upload_max_filesize', '32M');
@ini_set('post_max_size', '32M');
```

Arba per `php.ini`:

```ini
upload_max_filesize = 32M
post_max_size = 32M
max_allowed_packet = 64M
```

Restart web serverį.

---

### Problema 3: "Duplicate entry for key 'email'"

**Sprendimas:** Email jau egzistuoja database'je

Pašalinkite esamą vartotoją:

```sql
DELETE FROM wp_tossee_users WHERE email = 'test@example.com';
```

Arba naudokite kitą email'ą.

---

### Problema 4: "Registration handler not called"

**Sprendimas:** Form action neteisingas

Patikrinkite ar forma turi:

```html
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="tossee_custom_register">
    <!-- ... -->
</form>
```

---

### Problema 5: "Cookie not set" arba "Session not working"

**Sprendimas 1:** Session support

Pridėti į `wp-config.php` (prieš `require_once ABSPATH . 'wp-settings.php';`):

```php
if (!session_id()) {
    session_start();
}
```

**Sprendimas 2:** Cookie domain

Jei testuojate lokaliai (localhost), cookie gali neveikti subdomenų tarpe.
Tai normalu - veiks produkcijoje su tikru domenu.

---

### Problema 6: Form submituojasi bet nieko nevyksta

**Patikrinkite:**

1. Browser Console (F12) - ar yra JavaScript klaidų?
2. Network tab - ar POST request išsiunčiamas?
3. Ar yra redirect? (turėtų būti 302 redirect)

**Debug JavaScript:**

Atidarykite registracijos formą ir paspauskite F12 → Console. Bandykite submit'inti formą ir žiūrėkite ar yra klaidų.

---

## 6. MANUAL TEST

### Testuoti registraciją rankiniu būdu:

1. Eikite į: `https://jūsų-domenas.com/debug-tossee.php`
2. Scroll žemyn iki "Test Registration Form"
3. Užpildykite formą:
   - Username: testuser123
   - Email: test123@example.com
   - Password: test123
   - DOB: 1990-01-01
   - Photo: (jau užpildytas)
4. Spauskite "Test Register"

**Ką tikėtis:**
- Turėtų redirect'inti į success arba home page
- Vartotojas turėtų atsirasti Admin → Tossee Users

Jei **ERROR** - žiūrėkite `debug.log`.

---

## 7. CHECK ADMIN PANEL

### WordPress Admin → Tossee Users

Jei vartotojai registruojasi bet nematote admin panel:

1. Patikrinkite ar jūs prisijungę kaip administrator
2. Išvalykite browser cache
3. Patikrinkite ar plugin'as aktyvuotas

### SQL užklausa tiesiogiai patikrinti:

```sql
SELECT * FROM wp_tossee_users ORDER BY created_at DESC LIMIT 10;
```

Jei čia matote vartotojus bet admin panel nematote - problema WordPress admin panelėje.

---

## 8. COMMON CHECKS

### ✅ Checklist:

- [ ] Plugin'as aktyvuotas (Plugins → Tossee Core → Active)
- [ ] WP_DEBUG įjungtas (`wp-config.php`)
- [ ] Lentelė `wp_tossee_users` egzistuoja (phpMyAdmin)
- [ ] `debug.log` failas rodomas (wp-content/debug.log)
- [ ] POST limits pakankmai dideli (32M+)
- [ ] Registracijos forma turi teisingą `action` URL
- [ ] JavaScript veikia (F12 console be klaidų)
- [ ] Cookie domenas teisingas (production: `.tossee.com`)

---

## 9. TEST FLOW

### Pilnas testas nuo pradžios:

1. **Aktyvuoti plugin'ą**
   - WordPress Admin → Plugins → Activate "Tossee Core"

2. **Sukurti register puslapį**
   - Pages → Add New
   - Title: "Register"
   - Permalink: `/register`
   - Content: `[tossee_register_form]`
   - Publish

3. **Atidarti register puslapį**
   - `https://jūsų-domenas.com/register`

4. **Užpildyti formą:**
   - Username: johndoe
   - Email: john@example.com
   - Password: pass123
   - DOB: 1990-05-15
   - Enable camera → Take photo

5. **Submit**

6. **Tikėtinas rezultatas:**
   - Redirect į home arba success page
   - Cookie `tossee_uid` nustatytas
   - Vartotojas matomas WordPress Admin → Tossee Users

7. **Jei klaida:**
   - Žiūrėti `debug.log`
   - Žiūrėti Browser Console (F12)
   - Bandyti debug script (`debug-tossee.php`)

---

## 10. STILL NOT WORKING?

### Collect this information:

1. **WordPress Version:** (Dashboard → Updates)
2. **PHP Version:** (Tools → Site Health → Info → Server)
3. **Error from debug.log:** (copy [TOSSEE] lines)
4. **Browser Console errors:** (F12 → Console → screenshot)
5. **Test form result:** (from debug-tossee.php)

### Share:
- Error messages
- Debug.log output
- Screenshot of admin panel

---

## 11. QUICK FIX: Re-install Plugin

Kartais paprasčiausias sprendimas:

1. **Backup database** (jei yra svarbių duomenų)
2. Deactivate plugin
3. Delete plugin folder: `/wp-content/plugins/tossee-core/`
4. Upload fresh `tossee-core.php` into `/wp-content/plugins/tossee-core/`
5. Activate plugin
6. Test again

---

## 📞 Support Commands

### Enable verbose logging:

Add to `wp-config.php`:

```php
define('TOSSEE_DEBUG', true);
```

Then check `debug.log` for detailed info about each registration attempt.

---

## ✅ SUCCESS INDICATORS

Registracija veikia jei:

- ✓ Debug log rodo: `[TOSSEE][info] User registered successfully`
- ✓ Admin → Tossee Users rodo naują vartotoją
- ✓ Database lentelėje yra naujas įrašas
- ✓ Cookie `tossee_uid` nustatytas (F12 → Application → Cookies)
- ✓ Redirect veikia

**Sėkmės!** 🎉
