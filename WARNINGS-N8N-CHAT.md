# ⚠️ Tossee.com n8n Chat Integration - Warnings & Common Mistakes

## 🚨 CRITICAL: Ko NIEKADA nedaryti

### 1. Nekeisti aktyvios WordPress temos per file system
❌ **NIEKADA nepervadinėk aktyvios temos folderio**
```bash
# BLOGAI:
mv wp-content/themes/astra wp-content/themes/astra-off
```

**Kodėl tai gadina:**
- Database vis dar rodo `template = 'astra'`
- WordPress negali rasti temos
- Visas puslapis redirect'ina į `/wp-login.php`
- ERR_TOO_MANY_REDIRECTS klaida

**Kaip teisingai:**
```bash
# Per WordPress admin:
# Appearance → Themes → Activate kitą temą

# Arba per database:
mysql> UPDATE wp_options SET option_value = 'twentytwentyfive' WHERE option_name = 'template';
mysql> UPDATE wp_options SET option_value = 'twentytwentyfive' WHERE option_name = 'stylesheet';
```

### 2. Netrinti theme functions.php failų
❌ **NIEKADA netrink `wp-content/themes/*/functions.php`**

**Kodėl tai gadina:**
- Theme nebegali įsikelti
- WordPress redirect'ina į wp-admin
- Puslapis neveikia

### 3. Nekurti .htaccess su RewriteEngine Off
❌ **NIEKADA nekurk .htaccess su išjungtu rewrite engine WordPress aplinkoje**
```apache
# BLOGAI:
RewriteEngine Off
```

**Kodėl tai gadina:**
- WordPress reikia mod_rewrite veikimui
- Visi pretty permalinks nebeveikia
- API endpoints grąžina 404
- Puslapis redirect'ina

**Kaip teisingai:**
```apache
# GERAI - default WordPress .htaccess:
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
```

### 4. Nedirbti su klaidingu WordPress root path
❌ **Patikrink kur yra WordPress prieš paleisdamas komandas**

```bash
# BLOGAI - spėjamas path:
cd /home/username/public_html

# GERAI - patikrintas path:
pwd  # Pažiūrėk kur esi
ls -la wp-load.php  # Patikrink ar WordPress čia
```

**Teisingas path tossee.com:**
```
/home/u234011694/domains/tossee.com/public_html/
```

---

## 🔧 n8n Chat Integration - Kas veikia ir kas ne

### ✅ Kas VEIKIA:

1. **n8n workflows aktyvuoti ir laukia**
   - Webhook URL: `https://n8n.tossee.com/webhook/next`
   - Webhook URL: `https://n8n.tossee.com/webhook/signal`
   - Workflows visada `Always On`

2. **PHP proxy failai**
   - Location: `/home/u234011694/domains/tossee.com/public_html/chat/`
   - `matching.php` - forward'ina į n8n next webhook
   - `signaling.php` - forward'ina į n8n signal webhook
   - Valdo CORS headers

3. **Test be WordPress**
   - Simple PHP failai (pvz. test123.php su phpinfo()) veikia
   - n8n webhook'ai atsako į tiesiogines užklausas

### ❌ Kas NEVEIKIA (buvo sugadinta):

1. **WordPress tema mismatch**
   - Database rodė vieną temą, serveris turėjo kitą
   - Sprendimas: Suderinti database su esamomis temomis

2. **Disabled mu-plugins**
   - Must-use plugins buvo pervadinti į `mu-plugins-OFF`
   - Kai kurie funkcionalumas gali neveikti
   - Jei reikia: `mv mu-plugins-OFF mu-plugins`

3. **404 errors ant API endpoints**
   - Prižastis: Neteisingi .htaccess failai
   - Sprendimas: Atkurti default WordPress .htaccess

---

## 🐛 Debugging Guide

### Problema: ERR_TOO_MANY_REDIRECTS

**Galimos priežastys:**
1. Theme mismatch (database ≠ file system)
2. Sugadinti .htaccess failai
3. Plugin kuris verčia redirect'inti
4. Must-use plugin problemos

**Kaip diagnozuoti:**
```bash
# 1. Patikrink database theme settings:
php -r "require 'wp-load.php'; echo get_option('template');"

# 2. Patikrink ar tema egzistuoja:
ls -la wp-content/themes/

# 3. Jei nesutampa - suderink:
# Sukurk fix-db-direct.php (be WordPress loading)
```

### Problema: API endpoints grąžina 404

**Galimos priežastys:**
1. Neteisingi .htaccess rewrite rules
2. PHP failai neteisingoje vietoje
3. Permissions problemos

**Kaip diagnozuoti:**
```bash
# 1. Patikrink ar failai egzistuoja:
ls -la /path/to/wordpress/chat/matching.php

# 2. Patikrink .htaccess:
cat /path/to/wordpress/.htaccess

# 3. Test be WordPress:
# Sukurk test.php su tik phpinfo()
# Jei veikia - problema WordPress konfigūracijoje
```

### Problema: n8n workflows neatsako

**Galimos priežastys:**
1. Workflows ne "Always On" būsenoje
2. Network/firewall problemos
3. Neteisingi webhook URLs

**Kaip diagnozuoti:**
```bash
# Test webhook directly:
curl -X POST https://n8n.tossee.com/webhook/next \
  -H "Content-Type: application/json" \
  -d '{"test": true}'

# Turėtų grąžinti response, ne 404
```

---

## 📋 Recovery Checklist

Jei puslapis sugriuvo, darykite tokia tvarka:

### 1. Patikrink ar PHP veikia
```bash
echo "<?php phpinfo();" > test.php
# Atsidaryk: https://tossee.com/test.php
# Jei veikia - serveris OK, problema WordPress
```

### 2. Patikrink theme settings
```bash
# Sukurk fix-db-direct.php (nekrauna WordPress):
# - Parse wp-config.php kaip text
# - Connect į MySQL tiesiogiai
# - UPDATE wp_options tema settings
```

### 3. Patikrink .htaccess
```bash
cat .htaccess
# Turi būti default WordPress rules
# Jei nėra arba blogai - atkurk default
```

### 4. Disable plugins
```bash
# Per database:
UPDATE wp_options
SET option_value = 'a:0:{}'
WHERE option_name = 'active_plugins';
```

### 5. Check mu-plugins
```bash
ls -la wp-content/mu-plugins/
# Jei pervadinti į mu-plugins-OFF - gal tai problema
```

---

## 💡 Best Practices

### WordPress Development:

1. **Visada backup prieš keitimus**
   ```bash
   cp -r wp-content/themes/astra wp-content/themes/astra-backup
   ```

2. **Nekeisk temos per file system**
   - Naudok WordPress admin
   - Arba keisk database IR file system kartu

3. **Test lokalioj aplinkoje**
   - Ne production serveryje
   - Naudok staging environment

4. **Naudok WP-CLI jei įmanoma**
   ```bash
   wp theme activate twentytwentyfive
   wp plugin deactivate --all
   ```

### n8n Integration:

1. **Testuok webhooks tiesiogiai**
   - Naudok curl arba Postman
   - Patikrink n8n logs

2. **CORS headers**
   - Užtikrink kad API proxy grąžina teisingus CORS headers
   - Test iš browser console

3. **Error handling**
   - Logink visas klaidas
   - Naudok try-catch PHP proxy failuose

---

## 🔍 Useful Commands

### WordPress diagnostics:
```bash
# Check current theme:
php -r "require 'wp-load.php'; echo get_option('template');"

# Check active plugins:
php -r "require 'wp-load.php'; print_r(get_option('active_plugins'));"

# Check site URL:
php -r "require 'wp-load.php'; echo get_option('siteurl');"
```

### File system checks:
```bash
# Find WordPress root:
find /home -name "wp-load.php" 2>/dev/null

# Find .htaccess files:
find . -name ".htaccess" -type f

# Check permissions:
ls -la wp-content/
```

### Database direct access:
```bash
# Get DB credentials from wp-config.php:
grep "DB_NAME\|DB_USER\|DB_PASSWORD\|DB_HOST" wp-config.php

# Connect:
mysql -h [host] -u [user] -p [database]
```

---

## 📞 Recovery Scripts

### fix-db-direct.php
Šis scriptas taiso database NEKRAUDAMAS WordPress (išvengia redirect loop):

```php
<?php
// Parse wp-config.php be WordPress loading
$config = file_get_contents('wp-config.php');
preg_match("/define\(\s*'DB_NAME',\s*'([^']+)'/", $config, $dbname);
preg_match("/define\(\s*'DB_USER',\s*'([^']+)'/", $config, $dbuser);
preg_match("/define\(\s*'DB_PASSWORD',\s*'([^']+)'/", $config, $dbpass);
preg_match("/define\(\s*'DB_HOST',\s*'([^']+)'/", $config, $dbhost);

$mysqli = new mysqli($dbhost[1], $dbuser[1], $dbpass[1], $dbname[1]);

// Fix theme
$mysqli->query("UPDATE wp_options SET option_value = 'twentytwentyfive' WHERE option_name = 'template'");
$mysqli->query("UPDATE wp_options SET option_value = 'twentytwentyfive' WHERE option_name = 'stylesheet'");

// Disable plugins
$mysqli->query("UPDATE wp_options SET option_value = 'a:0:{}' WHERE option_name = 'active_plugins'");

echo "Fixed!";
$mysqli->close();
?>
```

**Naudojimas:**
1. Upload į WordPress root
2. Atsidaryk: https://tossee.com/fix-db-direct.php
3. Ištrink po naudojimo!

---

## ⚡ Summary

**Pagrindinis insights:**
1. WordPress tema mismatch = redirect loop
2. .htaccess su RewriteEngine Off = visko sugadinimas
3. Bet koks file su `require 'wp-load.php'` = redirect jei WordPress sugadintas
4. Simple PHP failai be WordPress = veikia net kai WordPress sugadintas
5. Database tiesiogiai per mysqli = galima taisyti kai WordPress neveikia

**Golden rule:**
> Visada suderink file system su database. Niekada nekeisk vieno be kito!
