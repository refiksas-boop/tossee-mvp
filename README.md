# ieškau.lt - Universalus Skelbimų Portalas

Pilna funkcionalumo skelbimų platforma, kuri apjungia:
- **Transportą** (kaip autoplius.lt)
- **Nekilnojamą turtą** (kaip aruodas.lt)
- **Paslaugas** (kaip paslaugos.lt)
- **Prekes** (kaip skelbiu.lt)
- **Darbo skelbimus** (kaip cvbankas.lt)
- **Gyvūnus** (kaip getpet.lt)

## Technologijos

- **Backend:** Laravel 11 (PHP 8.3+)
- **Frontend:** Blade Templates + Tailwind CSS + Alpine.js
- **Duomenų bazė:** MySQL 8 / MariaDB (suderinama)
- **Mokėjimai:** Stripe (test mode, struktūra ateičiai)
- **Failų saugykla:** Local storage + pasiruošimas AWS S3

## Funkcijos

### ✅ Įgyvendinta (MVP)

#### Duomenų bazė
- **22 lentelės** su pilnais ryšiais:
  - Users (vartotojai su rolėmis)
  - User Profiles (profilio info)
  - Locations (hierarchinė lokacijų sistema)
  - Categories & Subcategories (kategorijų sistema)
  - Listings (skelbimai su EAV atributais)
  - Messages & Conversations (žinučių sistema)
  - Favorites (mėgstami skelbimai)
  - Reviews (atsiliepimai)
  - Plans & Payments (monetizacija)
  - Ir kitos...

#### Vartotojų Sistemos
- **4 rolės:** user, business, moderator, admin
- Registracija / Login
- Profilio valdymas
- Verslo paskyros palaikymas

#### Skelbimų Sistema
- EAV (Entity-Attribute-Value) modelis dinaminius atributams
- Kiekviena kategorija gali turėti savo specialius laukus
- Skelbimų statusai: draft, pending, active, rejected, archived
- Featured & Urgent skelbimų palaikymas
- Nuotraukų įkėlimas (iki X nuotraukų)

#### Frontend
- Responsive dizainas su Tailwind CSS
- Alpine.js interaktyvumui
- Pagrindinis puslapis su:
  - Hero sekcija + paieška
  - Populiarios kategorijos
  - VIP skelbimai
  - Naujausi skelbimai
- Layout su navigacija ir footer

#### Kategorijos (6 pagrindinės)
1. **Transportas** - Lengvieji automobiliai, Motociklai, Sunkvežimiai, Dalys
2. **Nekilnojamas turtas** - Butai, Namai, Sklypai, Komercinės patalpos
3. **Paslaugos** - Statybos, IT, Grožis, Transportas
4. **Prekės** - Elektronika, Buitinė technika, Baldai, Drabužiai
5. **Darbas** - IT srityje, Statyba, Prekyba, Kita
6. **Gyvūnai** - Šunys, Katės, Paukščiai, Kiti

## Instalacija

### Reikalavimai

- PHP 8.3+
- Composer
- Node.js & NPM
- MySQL 8+ arba MariaDB

### Žingsniai

1. **Klonuoti projektą:**
```bash
git clone <repository-url>
cd tossee-mvp
```

2. **Įdiegti PHP dependencies:**
```bash
composer install
```

3. **Įdiegti frontend dependencies:**
```bash
npm install
```

4. **Sukonfigūruoti .env failą:**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Sukonfigūruoti duomenų bazę `.env` faile:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ieskau_lt
DB_USERNAME=root
DB_PASSWORD=your_password
```

6. **Sukurti duomenų bazę:**
```bash
mysql -u root -p
CREATE DATABASE ieskau_lt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

7. **Paleisti migracijas:**
```bash
php artisan migrate
```

8. **Paleisti seederius (pradiniai duomenys):**
```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=LocationSeeder
php artisan db:seed --class=ListingTypeSeeder
```

9. **Sukompiliuoti frontend assets:**
```bash
npm run dev
# arba production:
npm run build
```

10. **Paleisti serverį:**
```bash
php artisan serve
```

Projektas bus prieinamas adresu: `http://localhost:8000`

## Duomenų Bazės Schema

### Pagrindinės Lentelės

- **users** - Vartotojai su rolėmis (user, business, moderator, admin)
- **listings** - Skelbimai su kategorijomis, lokacija, kaina, statusu
- **attributes & listing_attribute_values** - EAV modelis dinaminius atributams
- **conversations & messages** - Vidinė žinučių sistema
- **favorites** - Mėgstami skelbimai
- **reviews** - Atsiliepimų sistema
- **plans & payments** - Monetizacijos sistema

## Sukurta su Laravel 11 + Tailwind CSS + Alpine.js
