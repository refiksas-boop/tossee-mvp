# 🎯 PAPRASTAS N8N SETUP (SIMPLE N8N SETUP)

## KĄ DARYTI (WHAT TO DO)

Tu turi sukurti **2 workflows** n8n:

1. **Matching** - sujungia vartotojus
2. **Signaling** - perduoda video duomenis

---

## 📝 WORKFLOW 1: MATCHING

### Žingsnis 1: Sukurti naują workflow

1. N8N eik į **"Workflows"**
2. Spausk **"Add Workflow"**
3. Pavadinimas: **"Video Chat - Matching"**
4. Spausk **"Save"**

### Žingsnis 2: Pridėti 3 nodes

Tu turi pridėti **3 nodes** šia tvarka:

```
┌──────────┐     ┌───────────────┐     ┌────────────────────┐
│ Webhook  │ ──→ │ Code          │ ──→ │ Respond to Webhook │
│          │     │               │     │                    │
└──────────┘     └───────────────┘     └────────────────────┘
```

### Žingsnis 3: Konfigūruoti WEBHOOK node

1. Spausk **+** mygtuką
2. Ieškoti **"Webhook"**
3. Pasirinkti **Webhook**

#### Nustatymai:

- **HTTP Method:** `POST` ✅
- **Path:** `matching` ✅
- **Response Mode:** `When Last Node Finishes` ✅
- **Response Code:** `200` ✅

#### Gauti URL:

- Po "Webhook URLs" sekcijos pamatysi URL
- KOPIJUOK **Production URL** (ne Test URL!)
- Pavyzdžiui: `https://tavo-n8n.app/webhook/matching`

### Žingsnis 4: Konfigūruoti CODE node

1. Spausk **+** ant Webhook node (dešinėje)
2. Ieškoti **"Code"**
3. Pasirinkti **Code** (NE "Function"!)

#### Nustatymai:

- **Mode:** `Run Once for All Items` ✅
- **Language:** `JavaScript` ✅

#### LABAI SVARBU! (VERY IMPORTANT!)

1. **IŠTRINT** visą kodą iš code editor
2. **ATIDARYTI** failą: `n8n-workflows/SIMPLE-MATCHING-CODE.js`
3. **NUKOPIJUOTI** VISĄ kodą iš to failo
4. **ĮKLIJUOTI** į n8n code editor

#### Kaip nukopijuoti:

```bash
# Terminale:
cat n8n-workflows/SIMPLE-MATCHING-CODE.js

# Arba atidaryti failą ir nukopijuoti viską (Ctrl+A, Ctrl+C)
```

**Patikrinti:**
- Pirma eilutė turėtų prasidėti: `// ============================================`
- Turėtų būti apie 100 eilučių kodo
- Paskutinė eilutė turėtų būti: `};`

### Žingsnis 5: Konfigūruoti RESPOND TO WEBHOOK node

1. Spausk **+** ant Code node
2. Ieškoti **"Respond to Webhook"**
3. Pasirinkti **Respond to Webhook**

#### Nustatymai:

- **Response Code:** `200` ✅
- **Response Body:** **PALIKTI TUŠČIĄ!** ❗❗❗ (LEAVE EMPTY!)

#### LABAI SVARBU! (VERY IMPORTANT!)

**Response Body** turi būti **VISIŠKAI TUŠČIAS**. Neįrašyk nieko!

#### Pridėti CORS Headers:

1. Spausk **"Add Option"**
2. Pasirinkti **"Response Headers"**
3. Spausk **"Add Header"** 3 kartus:

| Header Name | Header Value |
|------------|--------------|
| `Access-Control-Allow-Origin` | `*` |
| `Access-Control-Allow-Methods` | `POST, OPTIONS` |
| `Access-Control-Allow-Headers` | `Content-Type` |

### Žingsnis 6: Sujungti nodes

**PATIKRINTI kad yra linijos tarp visų node!**

```
[Webhook] ──→ [Code] ──→ [Respond to Webhook]
     ↑           ↑              ↑
   Turi būti linijos tarp jų!
```

Jei nėra linijos:
1. Spausk ant mažo apskritimo dešinėje node pusėje
2. Traukti į kitą node

### Žingsnis 7: AKTYVUOTI workflow

1. Spausk **"Save"** (viršuje)
2. Spausk **toggle switch** viršuje
3. Turi rodyti: **🟢 Active** (žalias)

### Žingsnis 8: Testuoti

```bash
# Terminale:
curl -X POST https://tavo-n8n.app/webhook/matching \
  -H "Content-Type: application/json" \
  -d '{"user":"test123","action":"join"}'
```

**Turėtų grąžinti:**
```json
{
  "ok": true,
  "status": "waiting",
  "position": 1,
  "message": "Waiting for stranger..."
}
```

✅ **JEI MATAI ŠĮ RESPONSE - VEIKIA!** (IF YOU SEE THIS - IT WORKS!)

---

## 📝 WORKFLOW 2: SIGNALING

### Žingsnis 1: Sukurti naują workflow

1. **"Add Workflow"**
2. Pavadinimas: **"Video Chat - Signaling"**
3. **"Save"**

### Žingsnis 2: Pridėti 3 nodes (tokius pačius kaip Workflow 1)

```
┌──────────┐     ┌───────────────┐     ┌────────────────────┐
│ Webhook  │ ──→ │ Code          │ ──→ │ Respond to Webhook │
└──────────┘     └───────────────┘     └────────────────────┘
```

### Žingsnis 3: Konfigūruoti WEBHOOK node

- **HTTP Method:** `POST` ✅
- **Path:** `signaling` ✅ (NE "matching"!)
- **Response Mode:** `When Last Node Finishes` ✅
- **Response Code:** `200` ✅

**KOPIJUOK Production URL!**

### Žingsnis 4: Konfigūruoti CODE node

1. **IŠTRINT** visą kodą
2. **ATIDARYTI** failą: `n8n-workflows/SIMPLE-SIGNALING-CODE.js`
3. **NUKOPIJUOTI** visą kodą
4. **ĮKLIJUOTI** į n8n

### Žingsnis 5: Konfigūruoti RESPOND TO WEBHOOK node

- **Response Code:** `200` ✅
- **Response Body:** **TUŠČIAS!** ❗
- **CORS Headers:** Tokie patys kaip Workflow 1

### Žingsnis 6: Sujungti ir aktyvuoti

1. Patikrinti kad visi nodes sujungti
2. **"Save"**
3. **Toggle switch** → 🟢 Active

### Žingsnis 7: Testuoti

```bash
curl -X POST https://tavo-n8n.app/webhook/signaling \
  -H "Content-Type: application/json" \
  -d '{"roomId":"test","type":"check"}'
```

**Turėtų grąžinti:**
```json
{
  "ok": true,
  "roomData": {
    "offers": [],
    "answers": [],
    "candidates": []
  }
}
```

✅ **JEI MATAI ŠĮ RESPONSE - VEIKIA!**

---

## 🎯 DABAR ĮDĖTI URL Į HTML

### Žingsnis 1: Atidaryti failą

```bash
nano example-client-implementation.html
# Arba su bet kokiu text editor
```

### Žingsnis 2: Rasti šias eilutes (apie 40-41 eilutė)

```javascript
const N8N_MATCHING_WEBHOOK = "YOUR_N8N_WEBHOOK_URL_HERE";
const N8N_SIGNALING_WEBHOOK = "YOUR_N8N_WEBHOOK_URL_HERE";
```

### Žingsnis 3: Pakeisti į savo URL

```javascript
const N8N_MATCHING_WEBHOOK = "https://tavo-n8n.app/webhook/matching";
const N8N_SIGNALING_WEBHOOK = "https://tavo-n8n.app/webhook/signaling";
```

### Žingsnis 4: Išsaugoti

```bash
# Ctrl+S arba File → Save
```

---

## 🧪 TESTUOTI VISĄ SISTEMĄ

### 1. Atidaryti du tab

1. **Tab 1:** Atidaryti `example-client-implementation.html`
2. **Tab 2:** Atidaryti tą patį `example-client-implementation.html`

### 2. Abu tab spausk "Start Chat"

- Tab 1: Spausk "Start Chat" → Turėtų rodyti "Waiting for stranger..."
- Tab 2: Spausk "Start Chat" → ABU turėtų parodyti "Connected!"

### 3. Turėtų matyti video

- "Your Video" - tavo kamera
- "Stranger's Video" - kito tab kamera

✅ **JEI MATAI ABU VIDEO - VISKAS VEIKIA!**

---

## ❌ JEI NEVEIKIA (IF NOT WORKING)

### Klaida: "Unexpected end of JSON input"

**Priežastis:** N8N workflow neaktyvus arba blogai sukonfigūruotas

**Sprendimas:**
1. ✅ Patikrinti kad workflow yra **Active** (žalias)
2. ✅ Patikrinti kad visi 3 nodes **sujungti**
3. ✅ Patikrinti kad **Response Body TUŠČIAS**
4. ✅ Patikrinti kad **kodas nukopijuotas teisingai**

### Klaida: "Waiting forever"

**Priežastis:** Abu tab ne tuo pačiu metu

**Sprendimas:**
1. ✅ Atidaryti abu tab
2. ✅ Spausk "Start Chat" abiejuose beveik tuo pačiu metu
3. ✅ Jei neveikia - refresh ir bandyk iš naujo

### Klaida: "Connected but no video"

**Priežastis:** Kameros leidimas arba WebRTC problema

**Sprendimas:**
1. ✅ Leisti kamerai ir mikrofonui
2. ✅ Naudoti Chrome ar Firefox (ne Safari!)
3. ✅ Patikrinti Console (F12) ar nėra klaidų

---

## 📋 CHECKLIST

Prieš testuojant, patikrinti:

- [ ] Abu workflow **Active** (žalias)
- [ ] Visi nodes **sujungti** (yra linijos)
- [ ] Kodas **nukopijuotas** iš SIMPLE-*.js failų
- [ ] **Response Body** tuščias abiejuose workflows
- [ ] **CORS headers** pridėti
- [ ] **URL** įdėti į HTML failą
- [ ] Testuoti su **curl** - grąžina JSON
- [ ] Atidaryti **2 tabs** su HTML failu
- [ ] **Spausk "Start Chat"** abiejuose

---

## 🎉 SĖKMĖS! (GOOD LUCK!)

Jei dar neveikia:
1. Atidaryti `test-n8n-webhooks.html`
2. Įdėti savo URL
3. Spausk "Test Raw HTTP Response"
4. Parodys kas negerai

Arba rašyk man - pasakysiu ką daryti! 😊
