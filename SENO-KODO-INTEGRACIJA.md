# 🔄 SENO KODO INTEGRACIJA (OLD CODE INTEGRATION)

Jei tu jau turi veikiančią frontend sistemą su kitu kodu, naudok šį guide.

---

## 🎯 KAS SKIRIASI (WHAT'S DIFFERENT)

### Tavo senas kodas:

```javascript
// Signaling request:
{
  user: userId,
  roomId: currentRoomId,
  type: "check"
}

// Response:
{
  roomData: {
    offers: [...],
    answers: [...],
    candidates: [...]  // Tiesiog kandidatų masyvas
  }
}

// ICE Candidate handling:
for (const c of room.candidates) {
  await pc.addIceCandidate(new RTCIceCandidate(c));
}
```

### Mano naujas kodas:

```javascript
// Signaling request:
{
  roomId: currentRoomId,
  type: "check"
  // No user field
}

// Response:
{
  roomData: {
    offers: [...],
    answers: [...],
    candidates: [           // Su metadata
      {
        candidate: {...},   // Tikras candidate
        from: "user_123",   // Kas atsiuntė
        timestamp: 123456
      }
    ]
  }
}

// ICE Candidate handling:
for (const item of room.candidates) {
  if (item.from !== userId) {
    await pc.addIceCandidate(new RTCIceCandidate(item.candidate));
  }
}
```

---

## ✅ SPRENDIMAS (SOLUTION)

### Option A: Naudok **SIMPLE-SIGNALING-CODE-V2.js**

Šis kodas yra 100% compatible su tavo senu frontend kodu!

#### Kas pakeista:

```javascript
// V1 (original):
room.candidates.push({
  candidate: data,
  from: user || "unknown",
  timestamp: Date.now()
});

// V2 (compatible):
room.candidates.push(data);  // Tiesiog data!
```

#### Kaip naudoti:

1. **Atidaryti n8n**
2. **Eiti į "Code" node** Signaling workflow
3. **IŠTRINTI** seną kodą
4. **NUKOPIJUOTI** VISĄ kodą iš `SIMPLE-SIGNALING-CODE-V2.js`
5. **Išsaugoti** ir **Aktyvuoti** workflow

**TADA TAVO SENAS FRONTEND KODAS VEIKS BE PAKEITIMŲ!** ✅

---

### Option B: Atnaujink frontend kodą

Jei nori naudoti naują sistemą su metadata (geriau!):

#### 1. Pakeisti `pollSignals()` funkciją:

```javascript
// RASTI ŠĮ KODĄ:
for (const c of room.candidates) {
  try {
    await pc.addIceCandidate(new RTCIceCandidate(c));
  } catch (e) {
    console.warn("addIceCandidate error:", e);
  }
}

// PAKEISTI Į:
for (const item of room.candidates) {
  try {
    // Ignore own candidates
    if (item.from !== userId) {
      await pc.addIceCandidate(new RTCIceCandidate(item.candidate));
    }
  } catch (e) {
    console.warn("addIceCandidate error:", e);
  }
}
```

#### 2. Naudoti **SIMPLE-SIGNALING-CODE.js** (original)

---

## 🧪 KAIP TESTUOTI (HOW TO TEST)

### Test 1: Patikrinti ar kandidatai siunčiami

```javascript
// Tavo console turėtų matyti:
console.log("Sending ICE candidate:", event.candidate);

// Send to n8n:
await sendSignal("candidate", event.candidate);
```

### Test 2: Patikrinti ar kandidatai gaunami

```javascript
// Poll signals response:
console.log("Room candidates:", room.candidates);

// Su V2 (senas kodas):
// [{ candidate: "...", sdpMid: "0", ... }, ...]

// Su V1 (naujas kodas):
// [{ candidate: {...}, from: "user_123", timestamp: 123 }, ...]
```

### Test 3: Patikrinti ar WebRTC jungiasi

```javascript
pc.onconnectionstatechange = () => {
  console.log("Connection state:", pc.connectionState);
  // Turėtų rodyti: connecting → connected
};
```

---

## 📋 KAS REIKIA DARYTI (WHAT TO DO)

### Jei turi senąjį frontend kodą:

1. ✅ Naudoti **SIMPLE-SIGNALING-CODE-V2.js** n8n
2. ✅ NIEKO NEKEISTI frontend kode
3. ✅ Testuoti - turėtų veikti!

### Jei nori naują sistemą:

1. ✅ Naudoti **SIMPLE-SIGNALING-CODE.js** (original) n8n
2. ✅ Pakeisti `pollSignals()` kaip parodyta Option B
3. ✅ Testuoti

---

## 🔍 KĄ PASITIKRINTI (CHECKLIST)

- [ ] N8N signaling workflow **Active**
- [ ] Kodas nukopijuotas iš **V2** (jei senas frontend)
- [ ] Arba kodas nukopijuotas iš **V1** ir frontend updated
- [ ] Webhook URL teisingas frontend kode
- [ ] CORS headers pridėti n8n "Respond to Webhook"
- [ ] Testuoti su 2 browser tabs
- [ ] Console rodo "Connection state: connected"

---

## ❌ TROUBLESHOOTING

### Klaida: "addIceCandidate error: TypeError"

**Priežastis:** Candidate format neteisingas

**Sprendimas:**
- Jei naudoji V1 n8n kodą → pakeisk frontend į `item.candidate`
- Jei naudoji V2 n8n kodą → palik frontend kaip yra `c`

---

### Klaida: Connection stuck on "connecting"

**Priežastis:** ICE kandidatai nesiunčiami arba negaunami

**Sprendimas:**
1. Console → Network tab → žiūrėk POST requests
2. Turėtų matyti requests kas 1.5s su `type: "check"`
3. Response turėtų turėti `candidates` masyve
4. Jei tuščias → patikrink ar `sendSignal("candidate", ...)` veikia

---

### Klaida: "Room not found"

**Priežastis:** Kambarys neegzistuoja n8n

**Sprendimas:**
- V2 kodas automatiškai sukuria kambarį su "check" type
- Patikrink kad `roomId` teisingas
- Patikrink kad matching workflow veikia ir grąžina `roomId`

---

## 📊 LYGINIMAS (COMPARISON)

| Feature | V1 (original) | V2 (compatible) |
|---------|--------------|-----------------|
| Kandidatų formatas | Objektas su metadata | Tiesiog candidate |
| Ignore own candidates | ✅ Taip (filter by `from`) | ❌ Ne (visi) |
| Timestamp tracking | ✅ Taip | ❌ Ne |
| Kodo paprastumas | Sudėtingesnis | Paprastesnis |
| Compatibility | Reikia naujinti frontend | ✅ Veikia su senu kodu |

---

## 🎯 REKOMENDACIJA (RECOMMENDATION)

### Jei turi veikiantį kodą:
→ **Naudok V2** - lengviau, nereikia nieko keisti

### Jei kurti naują sistemą:
→ **Naudok V1** - geresnis design, metadata useful

---

## 📞 GREITAS PATAISYMAS (QUICK FIX)

```bash
# 1. Atidaryti n8n
# 2. Eiti į Signaling workflow → Code node
# 3. Ctrl+A (select all)
# 4. Delete
# 5. Atidaryti terminalą:

cat n8n-workflows/SIMPLE-SIGNALING-CODE-V2.js

# 6. Copy visą output
# 7. Paste į n8n Code node
# 8. Save + Activate

# GOTOVO! ✅
```

---

## ✅ REZULTATAS (RESULT)

Po pataisymo:
- ✅ Tavo senas frontend kodas veikia
- ✅ N8N signaling veikia
- ✅ ICE kandidatai perduodami teisingai
- ✅ WebRTC connection successful!

---

Viskas aišku? Naudok **V2** jei turi seną kodą! 😊
