# Tossee Core Plugin - Instaliavimo Instrukcija

## 1. INSTALIAVIMAS

### Įkelkite plugin'ą į WordPress

1. Nukopijuokite visus šiuos failus į `/wp-content/plugins/tossee-core/` aplanką:
   - `tossee-core.php` (pagrindinis plugin failas)
   - `database-setup.php`
   - `register-handler.php`
   - `tossee-api.php`
   - `profile-shortcode.php`
   - `admin-users.php`
   - `register-form.php` (jei turite)

2. Eikite į WordPress Admin → Plugins
3. Aktyvuokite "Tossee Core" plugin'ą

## 2. SUKURKITE PUSLAPIUS

### Profile Puslapis

1. Eikite į Pages → Add New
2. Pavadinimas: "My Profile"
3. URL slug: `/profile`
4. Į content įdėkite shortcode:
   ```
   [tossee_profile]
   ```
5. Paskelbite puslapį

### Register Puslapis (jei dar neturite)

1. Eikite į Pages → Add New
2. Pavadinimas: "Register"
3. URL slug: `/register`
4. Į content įdėkite shortcode:
   ```
   [tossee_register_form]
   ```
5. Paskelbite puslapį

## 3. KAIP VEIKIA

### REST API Endpoint

Plugin'as sukuria REST API endpoint'ą:
```
GET /wp-json/tossee/v1/profile
```

Šis endpoint:
- Tikrina ar vartotojas prisijungęs (per session/cookie)
- Grąžina prisijungusio vartotojo duomenis
- **NEGRĄŽINA** password_hash (saugumo sumetimais)

### Profile Puslapis

1. Vartotojas atidaro `/profile` puslapį
2. JavaScript automatiškai iškviečia API: `/wp-json/tossee/v1/profile`
3. API grąžina vartotojo duomenis
4. Puslapis automatiškai užpildo laukus:
   - Username
   - Email
   - First Name / Last Name
   - Gender
   - Date of Birth
   - Country / City
   - Photo (avatar)

### Session/Cookie Sistema

Plugin'as naudoja `tossee_uid` session kintamąjį ir cookie:
- Po registracijos: `$_SESSION['tossee_uid'] = $tossee_id`
- Po login: `$_SESSION['tossee_uid'] = $tossee_id`
- Cookie: `tossee_uid` (30 dienų)

## 4. TESTAVIMAS

1. Užsiregistruokite naują vartotoją per `/register`
2. Po registracijos būsite nukreipti į chat.tossee.com
3. Atidarykite naują tab ir eikite į `https://tossee.com/profile`
4. Turėtumėte matyti savo profilio duomenis!

## 5. DEBUG

Jei profilis neužsikrauna, patikrinkite:

1. **Console (F12)**: Ar yra JavaScript klaidų?
2. **Network tab**: Ar API requestas pavyko?
   - Turėtų būti: `GET /wp-json/tossee/v1/profile` → Status 200
3. **Response**: Ar API grąžina duomenis?

### Tipinės klaidos:

- **401 Not Logged In**: Vartotojas neprisijungęs (nėra session)
- **404 User Not Found**: Vartotojas nerastas DB
- **500 Server Error**: PHP klaida (patikrinkite error logs)

## 6. KO DAR TRŪKSTA

Duomenų bazės lentelėje nėra šių stulpelių (juos reikės pridėti vėliau):
- `hobbies`
- `about`

Dabar jie rodomi kaip "-" profile puslapyje.
