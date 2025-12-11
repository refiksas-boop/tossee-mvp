# 🚀 GREITAS START - TOSSEE VIDEO CHAT

## 📋 KAS YRA KAS (WHAT IS WHAT)

### 1. **tossee.com** - Pagrindinis puslapis
Tu jau turi:
- `register-form.php` - registracijos forma
- `register-handler.php` - registracijos logika
- `login.html` - login puslapis

### 2. **chat.tossee.com** - Video chat puslapis ⭐ NAUJAS!
Nauji failai:
- `chat.tossee.com/index.php` - video chat interface
- `chat.tossee.com/session_config.php` - session sharing
- `chat.tossee.com/README.md` - deployment instrukcijos

### 3. **n8n workflows** - Backend logika
Du workflows n8n:
- **Matching** - sujungia vartotojus
- **Signaling** - perduoda video duomenis

---

## 🎯 KO REIKIA PADARYTI (WHAT YOU NEED TO DO)

### Žingsnis 1: N8N Setup (15 min)

1. **Atidaryti failą:**
   ```
   PAPRASTAS-N8N-SETUP.md
   ```

2. **Sekti instrukcijas:**
   - Sukurti 2 workflows n8n
   - Nukopijuoti kodą iš `SIMPLE-MATCHING-CODE.js`
   - Nukopijuoti kodą iš `SIMPLE-SIGNALING-CODE.js`
   - Aktyvuoti abu workflows

3. **Gauti webhook URLs:**
   - Matching: `https://tavo-n8n.app/webhook/matching`
   - Signaling: `https://tavo-n8n.app/webhook/signaling`

---

### Žingsnis 2: Chat.tossee.com Setup (10 min)

1. **Įkelti failus:**
   ```
   chat.tossee.com/index.php
   chat.tossee.com/session_config.php
   ```

2. **Pakeisti N8N URLs `index.php` faile:**

   Rasti šias eilutes (apie 60-61):
   ```javascript
   const N8N_MATCHING_WEBHOOK = "YOUR_N8N_MATCHING_WEBHOOK_URL";
   const N8N_SIGNALING_WEBHOOK = "YOUR_N8N_SIGNALING_WEBHOOK_URL";
   ```

   Pakeisti į savo:
   ```javascript
   const N8N_MATCHING_WEBHOOK = "https://tavo-n8n.app/webhook/matching";
   const N8N_SIGNALING_WEBHOOK = "https://tavo-n8n.app/webhook/signaling";
   ```

3. **Įkelti `session_config.php` į tossee.com TAIP PAT!**
   - Kopijuoti `session_config.php` į tossee.com root folderį
   - ABU domenai turi tą patį failą

---

### Žingsnis 3: Session Sharing Setup (5 min)

1. **Atidaryti `register-handler.php`** ant tossee.com

2. **Pridėti šią eilutę viršuje:**
   ```php
   <?php
   require_once 'session_config.php'; // ADD THIS LINE

   // Existing code continues...
   ```

3. **Pakeisti session set eilutę:**

   Rasti:
   ```php
   $_SESSION['tossee_id'] = $tossee_id;
   ```

   Pakeisti į:
   ```php
   setUserSession($tossee_id); // Uses helper function from session_config.php
   ```

4. **Patikrinti redirect URL:**

   Turėtų būti:
   ```php
   $redirect = "https://chat.tossee.com/";
   ```

---

### Žingsnis 4: SSL Setup (BŪTINA!)

**SVARBU:** WebRTC **NEVEIKIA** be HTTPS!

1. **Įsitikinti kad abu domenai turi SSL:**
   - ✅ https://tossee.com
   - ✅ https://chat.tossee.com

2. **Jei neturi SSL:**
   - Naudoti Let's Encrypt (nemokamas)
   - Arba CloudFlare (nemokamas SSL)

3. **Patikrinti `session_config.php`:**
   ```php
   ini_set('session.cookie_secure', 1); // = 1 if using HTTPS
   ```

---

## 🧪 TESTUOTI (TESTING)

### Test 1: N8N Webhooks

```bash
# Test matching:
curl -X POST https://tavo-n8n.app/webhook/matching \
  -H "Content-Type: application/json" \
  -d '{"user":"test","action":"join"}'

# Turėtų grąžinti:
# {"ok":true,"status":"waiting","position":1}
```

Arba atidaryti `test-n8n-webhooks.html` ir testuoti su interface.

---

### Test 2: Registration Flow

1. Eiti į https://tossee.com/register-form.php
2. Užsiregistruoti su nauju vartotoju
3. Po registracijos turėtų redirectinti į https://chat.tossee.com/
4. Turėtų matyti video chat puslapį

---

### Test 3: Video Chat Matching

1. **Browser Tab 1:**
   - Užsiregistruoti kaip User A
   - Redirectina į chat.tossee.com
   - Spausk "Start Chat"
   - Turėtų rodyti: "Waiting for stranger... (Position: 1)"

2. **Browser Tab 2 (Incognito mode):**
   - Užsiregistruoti kaip User B
   - Redirectina į chat.tossee.com
   - Spausk "Start Chat"
   - **ABU turėtų instantly susidurti!**

3. **Turėtų matyti:**
   - "Your Video" - tavo kamera
   - "Stranger's Video" - kito tab kamera
   - Status: "Connected! Enjoy your chat!"

---

## 🎯 FAILŲ LOKACIJOS (FILE LOCATIONS)

### Ant serverio turėtų būti:

```
/var/www/
├── tossee.com/
│   ├── register-form.php
│   ├── register-handler.php      ← UPDATE: Add session_config require
│   ├── login.html
│   ├── session_config.php        ← NEW: Copy from chat.tossee.com/
│   └── ...
│
└── chat.tossee.com/
    ├── index.php                 ← NEW: Video chat interface
    └── session_config.php        ← NEW: Session configuration
```

### N8N workflows:

```
N8N Dashboard:
├── Workflow: "Tossee Video Chat - Matching"
│   └── Nodes: Webhook → Code (SIMPLE-MATCHING-CODE.js) → Respond
│
└── Workflow: "Tossee Video Chat - Signaling"
    └── Nodes: Webhook → Code (SIMPLE-SIGNALING-CODE.js) → Respond
```

---

## ❌ TROUBLESHOOTING

### Klaida: "Redirected to login" po registracijos

**Priežastis:** Session nesidalina tarp domenų

**Sprendimas:**
1. Patikrinti kad `session_config.php` yra ant ABU domenų
2. Patikrinti kad `session.cookie_domain = '.tossee.com'` (su tašku!)
3. Patikrinti kad abu domenai turi HTTPS (arba abu HTTP)

---

### Klaida: "Empty response from webhook"

**Priežastis:** N8N workflows neaktyvūs arba blogai sukonfigūruoti

**Sprendimas:**
1. Atidaryti n8n dashboard
2. Patikrinti kad abu workflows **🟢 Active** (žali)
3. Patikrinti kad "Respond to Webhook" Response Body **tuščias**
4. Testuoti su curl (žiūrėti Test 1 viršuje)

---

### Klaida: "Camera access denied"

**Priežastis:** Nėra HTTPS arba user nesuteikė leidimo

**Sprendimas:**
1. Įsitikinti kad chat.tossee.com turi SSL certificate
2. Browser turėtų paklausti leidimo - leisk kamerai ir mikrofonui
3. Jei neleidžia - patikrinti browser settings

---

### Klaida: "Waiting forever" (nesujungia)

**Priežastis:** Polling neveikia arba signaling webhook neveikia

**Sprendimas:**
1. Atidaryti browser DevTools (F12)
2. Žiūrėti Console - turėtų matyti logs kas vyksta
3. Patikrinti Network tab - turėtų matyti POST requests kas 2s
4. Jei mato errors - patikrinti webhook URLs teisingi

---

## 📋 QUICK CHECKLIST

Prieš testujant, patikrinti:

- [ ] **N8N:**
  - [ ] 2 workflows sukurti
  - [ ] Kodas nukopijuotas iš SIMPLE-*.js failų
  - [ ] Abu workflows **Active** (🟢 žali)
  - [ ] Webhook URLs nukopijuoti

- [ ] **chat.tossee.com:**
  - [ ] index.php įkeltas
  - [ ] session_config.php įkeltas
  - [ ] N8N URLs įdėti į index.php
  - [ ] SSL certificate aktyvus

- [ ] **tossee.com:**
  - [ ] session_config.php įkeltas (tas pats failas!)
  - [ ] register-handler.php updated (require session_config)
  - [ ] Redirect URL set to chat.tossee.com

- [ ] **Testing:**
  - [ ] curl test grąžina JSON
  - [ ] Registration redirectina į chat
  - [ ] Session išlieka po redirect
  - [ ] 2 browser tabs gali susidurti

---

## 🎉 SĖKMĖS!

Kai viskas veikia:
1. User registruojasi → tossee.com
2. Automatiškai redirectina → chat.tossee.com
3. Spausk "Start Chat"
4. Sujungia su random stranger
5. Video chat starts!

---

## 📞 Jei reikia pagalbos:

1. **N8N problemos** → žiūrėti `PAPRASTAS-N8N-SETUP.md`
2. **Session problemos** → žiūrėti `chat.tossee.com/README.md`
3. **Klaidos** → žiūrėti `n8n-workflows/TROUBLESHOOTING.md`
4. **Testuoti webhooks** → atidaryti `test-n8n-webhooks.html`

---

## 🚀 BONUS: Lokal testuoti

Jei turi XAMPP/WAMP/MAMP:

1. Įdėti failus į:
   ```
   C:\xampp\htdocs\tossee\
   C:\xampp\htdocs\chat\
   ```

2. Pakeisti URLs į:
   ```php
   // register-handler.php
   $redirect = "http://localhost/chat/";
   ```

   ```php
   // session_config.php
   ini_set('session.cookie_domain', 'localhost');
   ini_set('session.cookie_secure', 0); // No HTTPS locally
   ```

3. Testuoti:
   - http://localhost/tossee/register-form.php
   - http://localhost/chat/

---

**VISKAS AIŠKU?** Rašyk jei reikia pagalbos! 😊
