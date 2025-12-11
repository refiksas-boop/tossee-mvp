# 🔴 N8N 500 Error - Greitai Pataisymas

## Problema
```
POST https://n8n.tossee.com/webhook/next 500 (Internal Server Error)
```

## Priežastis
N8N Code node reikalauja **grąžinti masyvą**, o ne objektą tiesiogiai.

---

## ✅ Kaip Pataisyti

### 1. Eikite į N8N: https://n8n.tossee.com

### 2. Atidarykite workflow su `/webhook/next`

### 3. Code node - pakeiskite visus `return` statements:

#### BLOGAI ❌
```javascript
return {
  ok: true,
  status: "matched"
};
```

#### GERAI ✅
```javascript
return [{
  json: {
    ok: true,
    status: "matched"
  }
}];
```

---

## 📝 Pilnas Pataisytas Kodas

### Webhook: `/webhook/next`

**Pakeiskite VISĄ Code node kodą šiuo:**

```javascript
// COPY FROM: n8n-next-endpoint-fixed.js
```

Arba nukopijuokite iš failo: **`n8n-next-endpoint-fixed.js`**

---

### Webhook: `/webhook/signal`

**Pakeiskite VISĄ Code node kodą šiuo:**

```javascript
// COPY FROM: n8n-signal-endpoint-fixed.js
```

Arba nukopijuokite iš failo: **`n8n-signal-endpoint-fixed.js`**

---

## 🔍 Pagrindinis Skirtumas

### Senasis (BLOGAI):
```javascript
const body = $json.body ?? $json;  // ❌ $json neveikia naujose N8N versijose

return {                            // ❌ Turi būti masyvas
  ok: true
};
```

### Naujas (GERAI):
```javascript
const body = $input.item.json.body || $input.item.json;  // ✅ Teisingas N8N sintaksė

return [{                           // ✅ Grąžina masyvą
  json: {
    ok: true
  }
}];
```

---

## ⚡ Po Pakeitimo

1. **Save** workflow
2. **Activate** workflow (jei neaktyvus)
3. Grįžkite į video chat puslapį
4. Paspaudkite "Next"
5. Turėtų veikti! ✅

---

## 🧪 Kaip Testuoti

### Console turėtų rodyti:
```
[NEXT] clicked
[NEXT] response: {"ok":true,"status":"waiting"}
[STATUS] Waiting for partner...
```

**Ne daugiau 500 errorų!**

---

## 🆘 Jei Vis Dar Neveikia

1. N8N → Executions → Žiūrėkite paskutinį execution
2. Ar matote error message?
3. Screenshot ir atsiųskite man

---

## ✅ Checklist

- [ ] `/webhook/next` Code node pakeistas
- [ ] `/webhook/signal` Code node pakeistas
- [ ] Workflows saved
- [ ] Workflows active (green toggle)
- [ ] Video chat testuotas - veikia!
