# chat.tossee.com - Video Chat Implementation

## 📁 Kas yra šiame folderyje (What's in this folder)

Šis folderis turi failus kuriuos reikia įkelti į **`chat.tossee.com`** domeną.

---

## 🚀 Kaip dėti (How to deploy)

### 1. Įkelti failą į serverį

```bash
# Jei naudoji FTP/SFTP:
# Įkelti index.php į chat.tossee.com root folderį

# Arba jei turi SSH:
scp index.php user@chat.tossee.com:/var/www/chat.tossee.com/
```

### 2. Pakeisti N8N webhook URLs

Atidaryti `index.php` ir rasti šias eilutes (apie 60-61):

```php
const N8N_MATCHING_WEBHOOK = "YOUR_N8N_MATCHING_WEBHOOK_URL";
const N8N_SIGNALING_WEBHOOK = "YOUR_N8N_SIGNALING_WEBHOOK_URL";
```

Pakeisti į savo tikrus URL:

```php
const N8N_MATCHING_WEBHOOK = "https://tavo-n8n.app/webhook/matching";
const N8N_SIGNALING_WEBHOOK = "https://tavo-n8n.app/webhook/signaling";
```

### 3. Patikrinti PHP Session konfigūraciją

**SVARBU:** `chat.tossee.com` ir `tossee.com` turi dalintis session!

#### Option A: Subdomain Sessions (REKOMENDUOJAMA)

Abiejuose domenuose (tossee.com IR chat.tossee.com) pridėti:

```php
// session_config.php
ini_set('session.cookie_domain', '.tossee.com'); // Note the dot!
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Jei naudoji HTTPS
session_start();
```

Tada abiejuose failuose (register-handler.php, index.php):

```php
require_once 'session_config.php';
```

#### Option B: Token based (Alternatyva)

Jei session sharing neveikia, naudoti URL parametrą:

1. **register-handler.php** pakeisti redirect:
```php
$redirect = "https://chat.tossee.com/?uid=" . urlencode($tossee_id) . "&token=" . urlencode(session_id());
```

2. **chat.tossee.com/index.php** patikrinti token:
```php
<?php
session_start();

// Check if coming from registration
if (isset($_GET['uid']) && isset($_GET['token'])) {
    // Validate token against database or session storage
    $_SESSION['tossee_id'] = $_GET['uid'];
}

if (!isset($_SESSION['tossee_id'])) {
    header('Location: https://tossee.com/login.html');
    exit;
}

$tossee_id = $_SESSION['tossee_id'];
?>
```

---

## 🧪 Testuoti (Testing)

### 1. Testuoti lokali

Jei turi xampp/wamp:

```bash
# Įkelti index.php į:
# C:\xampp\htdocs\chat\index.php

# Naviguoti į:
http://localhost/chat/index.php
```

### 2. Testuoti produkciją

1. Atidaryti https://tossee.com/register-form.php
2. Užsiregistruoti
3. Po registracijos turėtų redirectinti į https://chat.tossee.com/
4. Turėtų matyti video chat puslapį
5. Spausk "Start Chat" ir testuok!

### 3. Testuoti matching su dviem vartotojais

1. **Browser Tab 1:** Užsiregistruoti kaip User A → eik į chat → Start Chat
2. **Browser Tab 2 (Incognito):** Užsiregistruoti kaip User B → eik į chat → Start Chat
3. Abu turėtų susidurti!

---

## 🔧 Troubleshooting

### Klaida: "Redirected to login"

**Priežastis:** Session neperduodamas tarp domenų

**Sprendimas:**
- Patikrinti kad `session.cookie_domain` set to `.tossee.com`
- Patikrinti kad abu domenai naudoja HTTPS (arba abu HTTP)
- Arba naudoti Token based metodą (Option B viršuje)

### Klaida: "Empty response from webhook"

**Priežastis:** N8N webhooks neveikia

**Sprendimas:**
- Patikrinti kad abu n8n workflows **Active**
- Patikrinti kad URLs teisingi index.php faile
- Testuoti su `test-n8n-webhooks.html`

### Klaida: "Camera access denied"

**Priežastis:** HTTPS reikalinga WebRTC

**Sprendimas:**
- chat.tossee.com **TURI** turėti SSL certificate
- getUserMedia() veikia tik su HTTPS (arba localhost)

---

## 📋 Checklist prieš deployment

- [ ] N8N workflows sukurti ir **Active**
- [ ] N8N webhook URLs įdėti į index.php
- [ ] SSL certificate įkeltas ant chat.tossee.com
- [ ] Session sharing sukonfigūruotas
- [ ] index.php įkeltas į chat.tossee.com root
- [ ] Testuoti registration flow → redirect į chat
- [ ] Testuoti video permissions
- [ ] Testuoti matching su 2 vartotojais

---

## 📁 Failo struktūra (File structure)

### Serverio struktūra:

```
/var/www/
├── tossee.com/
│   ├── register-form.php
│   ├── register-handler.php
│   ├── login.html
│   └── session_config.php    ← Shared config
│
└── chat.tossee.com/
    ├── index.php              ← Video chat interface
    └── session_config.php     ← Same shared config
```

### Arba jei ant to paties serverio:

```
/var/www/html/
├── register-form.php
├── register-handler.php
├── login.html
├── session_config.php
└── chat/
    └── index.php
```

Tada redirect į:
```php
$redirect = "https://tossee.com/chat/";
```

---

## 🔐 Security Notes

### Production checklist:

1. **HTTPS only** - WebRTC requires SSL
2. **Validate sessions** - Don't trust $_GET parameters blindly
3. **Rate limiting** - Prevent spam of n8n webhooks
4. **Input validation** - Sanitize all user inputs
5. **CORS properly** - Only allow your domains

### Example security enhancements:

```php
// At top of index.php
<?php
// Force HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

session_start();

// Validate session
if (!isset($_SESSION['tossee_id']) || !preg_match('/^[a-zA-Z0-9_-]+$/', $_SESSION['tossee_id'])) {
    header('Location: https://tossee.com/login.html');
    exit;
}

// Regenerate session ID to prevent fixation
session_regenerate_id(true);

$tossee_id = htmlspecialchars($_SESSION['tossee_id'], ENT_QUOTES, 'UTF-8');
?>
```

---

## 📞 Support

Jei klausimai:
1. Patikrinti `PAPRASTAS-N8N-SETUP.md` - n8n configuration
2. Patikrinti `n8n-workflows/TROUBLESHOOTING.md` - common errors
3. Testuoti su `test-n8n-webhooks.html` - verify webhooks work

---

## ✅ Kas kitkas (What's next)

Po deployment, gali pridėti:
- Text chat funkcionalumą
- Screen sharing
- Filters/effects
- Report/block funkcionalumą
- User profiles matching (age, interests, etc.)
- Analytics
