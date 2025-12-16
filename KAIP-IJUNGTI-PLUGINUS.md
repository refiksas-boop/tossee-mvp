# 🚀 KAIP ĮJUNGTI VISUS PLUGINUS - GREITAS BŪDAS

## Būdas 1: Per naršyklę (GREIČIAUSIAS!)

### 1. Upload failą į serverį

**Per Hostinger File Manager:**
1. Eik į https://hpanel.hostinger.com/
2. Prisijunk
3. Pasirink tossee.com → **File Manager**
4. Eik į: `domains/tossee.com/public_html/`
5. **Upload** failą `restore-now.php` iš šio projekto
6. Failas turi būti čia: `/public_html/restore-now.php`

**Arba per FTP/SSH:**
```bash
scp -P 65002 restore-now.php u234011694@62.72.34.8:~/domains/tossee.com/public_html/
```

### 2. Atidaryti naršyklėje

Eik į: **https://tossee.com/restore-now.php**

### 3. Spausk mygtuką

Spausk: **🔄 GRĄŽINTI VISKĄ ATGAL**

### 4. Patikrink

- Atidaryti: https://tossee.com/
- Turėtų veikti normaliai!

### 5. IŠTRINK failą

Per tą patį puslapį spausk: **🗑️ Ištrinti restore-now.php**

Arba per File Manager ištrink rankiniu būdu.

---

## Būdas 2: Per File Manager (rankinis)

1. Eik į: https://hpanel.hostinger.com/
2. File Manager → `public_html/wp-content/`
3. **Pervadink aplankus:**
   - `plugins.DISABLED` → `plugins`
   - `themes/astra.DISABLED` → `themes/astra`
4. Atidaryti: https://tossee.com/

---

## Būdas 3: Per SSH

```bash
ssh -p 65002 u234011694@62.72.34.8
cd /home/u234011694/domains/tossee.com/public_html/wp-content/
mv plugins.DISABLED plugins
cd themes/
mv astra.DISABLED astra
```

---

## Ko tikėtis po įjungimo:

✅ tossee.com - veiks normaliai
✅ wp-admin - prieinamas
✅ Visi pluginai - aktyvūs
✅ Astra tema - aktyvi

⚠️ **Redirect loop** gali grįžti, jei neesi prisijungęs. Bet bent jau matai puslapį!

---

**REKOMENDUOJAMAS: Būdas 1 (restore-now.php) - greičiausias ir lengviausias!**
