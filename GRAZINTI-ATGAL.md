# Kaip grąžinti VISKĄ atgal

## Greitas būdas (automatinis)

```bash
cd /home/user/tossee-mvp
./RESTORE.sh
```

Arba su slaptažodžiu, jei neturi SSH rakto:
```bash
./RESTORE.sh
# Įveski slaptažodį kai paklaus
```

---

## Rankinis būdas (jei skriptas neveikia)

### 1. Prisijunk prie serverio

Per PuTTY arba terminalą:
- **Host:** 62.72.34.8
- **Port:** 65002
- **Username:** u234011694
- **Password:** (tavo slaptažodis)

Arba komanda:
```bash
ssh -p 65002 u234011694@62.72.34.8
```

### 2. Grąžink plugins

```bash
cd /home/u234011694/domains/tossee.com/public_html/wp-content/

# Pašalinti tuščią plugins aplanką
rm -rf plugins

# Grąžinti seną plugins
mv plugins.DISABLED plugins
```

### 3. Grąžink Astra temą

```bash
cd themes/

# Pašalinti "fixed" astra versiją
rm -rf astra

# Grąžinti senąją Astra temą
mv astra.DISABLED astra
```

### 4. Patikrinti

Dabar atidaryti naršyklėje:
- https://tossee.com/

Turėtų rodyti puslapį kaip anksčiau (ne tuščią!).

---

## Kas buvo pakeista (ir dabar grąžinama)

1. **Plugins** - buvo pervadinti į `plugins.DISABLED` → grąžiname į `plugins`
2. **Astra tema** - buvo pervadinta į `astra.DISABLED` → grąžiname į `astra`

**Nieko daugiau nebuvo pakeista!**

---

## Jei vis tiek neveikia

Patikrink wp-admin:
1. Eiti į: https://tossee.com/wp-admin/
2. Prisijungti
3. **Appearance → Themes** - aktyvuoti Astra
4. **Plugins** - patikrinti ar aktyvūs

---

## Dėl "naujo IP"

**Svarbu:** Aš NEKŪRIAU naujo serverio ar IP!

Kas iš tikrųjų įvyko:
- Senas serveris: **62.72.20.176** (ten buvo failai)
- Naujas serveris: **62.72.34.8** (čia Hostinger VPS)

**DNS jau buvo nukreiptas į naująjį serverį** (62.72.34.8) - aš tik tai radau ir parodžiau.

Failai buvo ant senojo serverio, bet DNS rodė į naująjį - todėl nieko neveikė.

Dabar visi failai yra ant naujo serverio (62.72.34.8) ir viskas turėtų veikti.

---

## Kaip patikrinti ar veikia

```bash
# Homepage
curl -I https://tossee.com/
# Turėtų būti: HTTP/2 200

# Chat
curl -I https://chat.tossee.com/
# Turėtų būti: HTTP/2 200
```

---

**Vykdyk `./RESTORE.sh` ir viskas grįš atgal!**
