# 🎥 Tossee Video Chat - Pataisymai

## 🔴 Problemos, kurios buvo rasta

### 1. **Race Condition** - Matching sistema
**Problema:**
```javascript
// SENAS KODAS:
const idx = store.queue.findIndex(u => u.userId !== userId);
if (idx === -1) {
  store.queue.push({ userId, joinedAt: now });
  return { status: "waiting" };
}
```

**Kas vyksta:**
1. Vartotojas A paspaudžia "Next" → queue tuščias → prideda save, gauna "waiting"
2. Po 0.5s Vartotojas B paspaudžia "Next" → randa A → sukuria room, A pašalinamas iš queue
3. Po 2s Vartotojas A tikrina vėl → jo nebėra queue, bet jis nežino kad matched!
4. Vartotojas A pridedamas į queue vėl → **matching nepavyko**

### 2. **Duplicate Queue Entries**
Vartotojas gali būti queue kelis kartus jei greitai spaudo "Next".

### 3. **Lėtas Polling**
- Matching tikrinimas kas 2s - per lėtai
- Signal polling kas 1.5s - per lėtai

### 4. **Nėra cleanup**
Vartotojai lieka queue net jei disconnectin.

---

## ✅ Sprendimai

### 1. **Check Action** - Naujas metodas
```javascript
// NAUJAS KODAS - Frontend:
async function checkForMatch() {
  const res = await fetch("https://n8n.tossee.com/webhook/next", {
    method: "POST",
    body: JSON.stringify({
      user: userId,
      action: "check"  // <-- Tiesiog tikrina ar matched
    })
  });

  if (data.status === "matched") {
    // Pradeda WebRTC!
  }
}
```

**Kaip veikia:**
- Vartotojas A pridedamas į queue
- Kas 1s tikrina ar yra match (action: "check")
- Kai B sukuria room su A → A gauna "matched" per 1s

### 2. **Duplicate Prevention**
```javascript
// NAUJAS KODAS - Backend:
// Pašalina save iš queue prieš matching
store.queue = store.queue.filter(u => u.userId !== userId);

// Tada ieško partnerio
const partnerIndex = store.queue.findIndex(u => u.userId !== userId);
```

### 3. **Greičiau Polling**
- Matching check: **2s → 1s**
- Signal polling: **1.5s → 1s**

### 4. **Automatic Cleanup**
```javascript
// Pašalina senus įrašus (>60s)
store.queue = store.queue.filter(u => now - u.joinedAt < 60000);

// Disconnect signal
fetch("/webhook/signal", {
  body: JSON.stringify({
    roomId: currentRoomId,
    type: "disconnect"
  })
});
```

---

## 📁 Nauji Failai

### 1. `video-chat.html`
Frontend su pataisymais:
- Check action vietoj tik "match"
- Greičiau polling (1s)
- Disconnect signalai
- Debug logging

### 2. `n8n-next-endpoint.js`
N8N webhook endpoint `/webhook/next`:
- Tvarko du actions: "match" ir "check"
- Išsprendžia race conditions
- Prevencija duplicate entries
- Automatic cleanup

### 3. `n8n-signal-endpoint.js`
N8N webhook endpoint `/webhook/signal`:
- Tvarko WebRTC signaling
- Disconnect handling
- Better error handling

---

## 🚀 Kaip Naudoti

### 1. N8N Setup

**A. Next Endpoint:**
1. Sukurti naują webhook node: `/webhook/next`
2. POST method
3. Code node su `n8n-next-endpoint.js` kodu

**B. Signal Endpoint:**
1. Sukurti naują webhook node: `/webhook/signal`
2. POST method
3. Code node su `n8n-signal-endpoint.js` kodu

### 2. Frontend Deploy

Upload `video-chat.html` į serverį:
```
https://tossee.com/video-chat.html?uid=USER_ID
```

---

## 🧪 Kaip Testuoti

### Test Case 1: Du vartotojai laukia
1. Atidaryk 2 browser tabs
2. Pirmam: `/video-chat.html?uid=user1`
3. Antram: `/video-chat.html?uid=user2`
4. Abu paspaudžia "Next"
5. **Tikėtinas rezultatas:** Per 1-2s turi sujungti

### Test Case 2: Vienas laukia, kitas ateina vėliau
1. User1 paspaudžia "Next" → laukia
2. Po 5s User2 paspaudžia "Next"
3. **Tikėtinas rezultatas:** Iš karto match

### Test Case 3: Greitas clicking
1. User1 spaudo "Next" 5 kartus greitai
2. User2 paspaudžia "Next"
3. **Tikėtinas rezultatas:** Match įvyksta, be duplicates

### Test Case 4: Disconnect ir rejoin
1. User1 ir User2 sujungia
2. User1 paspaudžia "Next"
3. User3 paspaudžia "Next"
4. **Tikėtinas rezultatas:** User1 ir User3 sujungia

---

## 🔍 Debug

### Frontend Console
```javascript
// Matysi šiuos logs:
[NEXT] clicked
[NEXT] response: {"status":"waiting"}
[STATUS] Waiting for partner...
[MATCHED] room: room_xyz123 ["user1", "user2"]
[WebRTC] Starting as CALLER
[WebRTC] ICE state: connected
[STATUS] Connected!
```

### N8N Console
```javascript
// Next endpoint:
[MATCH] User user1 added to queue. Queue size: 1
[MATCH] Created room room_xyz123 for user2 and user1

// Signal endpoint:
[SIGNAL] Offer saved in room room_xyz123
[SIGNAL] Answer saved in room room_xyz123
[SIGNAL] ICE candidate added to room room_xyz123
```

---

## 🎯 Pagrindiniai Skirtumai

| Aspektas | Senasis Kodas | Naujas Kodas |
|----------|--------------|--------------|
| **Matching check** | Tik "match" request | "match" + "check" actions |
| **Race condition** | ❌ Vartotojas gali praleisti match | ✅ Tikrina kas 1s |
| **Duplicates** | ❌ Gali patekti kelis kartus | ✅ Auto-remove |
| **Polling speed** | 2s / 1.5s | 1s / 1s |
| **Cleanup** | ❌ Manual | ✅ Automatic |
| **Disconnect** | ❌ Nėra | ✅ Signal serveriui |
| **Debug** | Minimalus | Detali logging |

---

## ⚠️ Svarbūs Patarimai

1. **CORS Settings:** Įsitikink, kad N8N leidžia requests iš tossee.com
2. **HTTPS:** WebRTC reikalauja HTTPS (išskyrus localhost)
3. **Firewall:** Patikrink kad STUN/TURN serveriai pasiekiami
4. **Browser Console:** Visada tikrink console errors
5. **N8N Logs:** Žiūrėk N8N execution logs jei kas nors neveikia

---

## 🆘 Troubleshooting

### "Waiting for partner..." ilgai laukia
- Patikrink N8N logs - ar "match" request veikia
- Patikrink ar kitas vartotojas tikrai queue'je
- Patikrink CORS settings

### Match įvyksta, bet video nesimatote
- Patikrink WebRTC ICE state console'je
- Gali reikėti TURN serverio (ne tik STUN)
- Patikrink firewall/NAT settings

### "Room not found" error
- Patikrink ar roomId teisingai perduodamas
- Patikrink N8N signal endpoint logs
- Gali būti kad room buvo ištrintas per cleanup

---

## 📝 N8N Workflow Structure

```
┌─────────────────────┐
│  Webhook /next      │
│  POST               │
│  Body: {user, action}│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Code Node          │
│  n8n-next-endpoint  │
│  - Match logic      │
│  - Queue management │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Response           │
│  {status, roomId}   │
└─────────────────────┘

┌─────────────────────┐
│  Webhook /signal    │
│  POST               │
│  Body: {roomId,type}│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Code Node          │
│  n8n-signal-endpoint│
│  - Signaling logic  │
│  - Room data        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Response           │
│  {ok, roomData}     │
└─────────────────────┘
```

---

## ✅ Checklist Prieš Deploy

- [ ] N8N webhooks sukurti ir aktyvūs
- [ ] CORS settings nustatyti
- [ ] HTTPS enabled (production)
- [ ] Browser console clear be errorų
- [ ] Tested su 2+ vartotojais
- [ ] Disconnect veikia teisingai
- [ ] Cleanup automatinis veikia

---

Jei vis dar neveikia - atsiųsk:
1. Browser console logs
2. N8N execution logs
3. Network tab screenshot (fetch requests)
