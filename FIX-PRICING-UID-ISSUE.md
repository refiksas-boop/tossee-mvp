# 🔧 PRICING UID PROBLEMA - SPRENDIMAS

## 📋 PROBLEMA
- Chat puslapyje UID rodo ✅
- Pricing puslapyje UID nerodo ❌
- localStorage nepasiima tarp puslapių

---

## ✅ SPRENDIMAS (2 dalys)

### **1️⃣ DALIS: Atnaujink pricing puslapio PHP kodą**

Įdėk **naują failą** `tossee-pricing-redirect-fixed.php` su debug'u:
- ✅ Rodo debug box viršuje dešinėje (PHP UID, URL UID, localStorage, Final UID)
- ✅ Konsolėje rodo visą info
- ✅ Jei nėra UID - automatiškai nukreipia į chat puslapį
- ✅ Išsaugo UID iš URL į localStorage

**Kaip naudoti:**
```php
// WordPress plugine arba functions.php
require_once get_template_directory() . '/tossee-pricing-redirect-fixed.php';
```

---

### **2️⃣ DALIS: Chat puslapyje perduok UID į pricing URL**

**Problema:** Kai chat puslapyje nukreipia į pricing, URL neturi `?uid=XXX`

**Sprendimas:** Chat puslapyje, kai nukreipia į pricing, pridėk uid:

```javascript
// CHAT PUSLAPYJE (pvz. index.html arba chat.js)

// SENAS KODAS (be uid):
// window.location.href = 'https://tossee.com/pricing-plans';

// NAUJAS KODAS (su uid):
function redirectToPricing() {
  const uid = localStorage.getItem('tossee_id');

  if (!uid) {
    console.error('❌ No UID found in localStorage');
    alert('Session error. Please refresh the page.');
    return;
  }

  const pricingUrl = 'https://tossee.com/pricing-plans?uid=' + encodeURIComponent(uid);
  console.log('✅ Redirecting to pricing with UID:', pricingUrl);
  window.location.href = pricingUrl;
}

// Naudok vietoj tiesioginio redirect
redirectToPricing();
```

---

## 🔍 KAIP DEBUGINTI

### Pricing puslapyje bus:

**1. Debug box (dešinėje viršuje):**
```
🔍 Debug Info
PHP UID: user123 arba ❌ missing
URL UID: user123 arba ❌ missing
localStorage: user123 arba ❌ missing
Final UID: user123 arba ❌ MISSING!
```

**2. Console logs (F12 → Console):**
```
=== PRICING DEBUG START ===
PHP_UID from server: "user123"
localStorage tossee_id: "user123"
URL uid parameter: "user123"
✅ getUserId: using URL uid: user123
Final UID for checkout: user123
=== PRICING DEBUG END ===
```

---

## 🎯 TIKRINK ŠĮ PROCESĄ

### ✅ TEST 1: Ar chat puslapyje yra UID?
```javascript
// Chat puslapyje (F12 Console):
localStorage.getItem('tossee_id')
// Turėtų rodyti: "user_abc123..."
```

### ✅ TEST 2: Ar URL turi uid parametrą?
```
❌ BLOGAI:
https://tossee.com/pricing-plans

✅ GERAI:
https://tossee.com/pricing-plans?uid=user_abc123...
```

### ✅ TEST 3: Ar pricing puslapis gauna uid?
```javascript
// Pricing puslapyje (F12 Console):
localStorage.getItem('tossee_id')
// Turėtų rodyti: "user_abc123..."
```

---

## 🚨 GALIMOS PROBLEMOS

### ❌ localStorage neveikia tarp domenų
**Priežastis:** `chat.tossee.com` ir `tossee.com` - skirtingi domenai!

**Sprendimas:**
- Visada perduok `?uid=XXX` per URL
- Arba naudok tą patį domeną

---

### ❌ PHP_UID tuščias
**Priežastis:** URL neturi `?uid=XXX` parametro

**Sprendimas:**
- Užtikrink kad chat puslapyje redirect prideda uid
- Arba patikrink localStorage

---

### ❌ Redirect į chat vietoj pricing
**Priežastis:** `!finalUid` patikrinimas automatiškai nukreipia

**Sprendimas:**
- Tai yra saugos funkcija
- Jei nori išjungti, išimk šį kodą:
```javascript
if (!finalUid) {
  // Šis blokas nukreipia atgal į chat
  // Gali išjungti testui
}
```

---

## 📝 SUMMARY

**Ką daryti:**

1. ✅ Įdėk `tossee-pricing-redirect-fixed.php` į WordPress
2. ✅ Chat puslapyje pridėk uid prie pricing URL:
   ```javascript
   const url = 'https://tossee.com/pricing-plans?uid=' + localStorage.getItem('tossee_id');
   ```
3. ✅ Patikrins debug box pricing puslapyje
4. ✅ Žiūrėk console logs (F12)

**Rezultatas:**
- ✅ UID persiduos iš chat į pricing
- ✅ Checkout veiks su tinkamu UID
- ✅ Debug rodysi kas vyksta

---

## 🔗 GREITAI PATIKRINTI

```bash
# 1. Chat puslapyje:
localStorage.setItem('tossee_id', 'TEST_UID_12345');
# Dabar eik į pricing puslapį

# 2. Arba tiesiogiai eik su URL:
https://tossee.com/pricing-plans?uid=TEST_UID_12345
```

Debug box turėtų rodyti `TEST_UID_12345` visur ✅
