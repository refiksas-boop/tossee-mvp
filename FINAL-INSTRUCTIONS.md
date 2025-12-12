# 🎯 GALUTINĖS INSTRUKCIJOS: chat.tossee.com

## ✅ KAS JAU PADARYTA:

1. ✅ Sukurti PHP API failai (matching.php, signaling.php)
2. ✅ Sukurtas naujas index.html su teisingais endpoint'ais
3. ✅ Failai įkelti į VPS: `/home/u234011694/domains/chat.tossee.com/public_html/`
4. ✅ DNS sukonfigūruotas (chat.tossee.com → 62.72.34.8)

## ❌ KAS DAR REIKIA PADARYTI:

**Subdomain rodo į NETEISINGĄ vietą!**

- Dabar rodo: `/home/u234011694/domains/tossee.com/public_html/chat`
- Turi rodyti: `/home/u234011694/domains/chat.tossee.com/public_html`

---

## 🔧 SPRENDIMAS (2 minutės):

### Variantas 1: Per hPanel (PAPRASČIAUSIA)

1. **Eikite į**: https://hpanel.hostinger.com/
2. **Prisijunkite** su savo Hostinger paskyra
3. **Pasirinkite**: **Websites → tossee.com → Manage**
4. **Kairėje meniu**: **Domains → Subdomains**
5. **Raskite** `chat.tossee.com` ir **paspauskite ⚙️** (Settings arba Manage)
6. **Pakeiskite "Document Root" lauką** į:
   ```
   /home/u234011694/domains/chat.tossee.com/public_html
   ```
7. **Išsaugokite** (Save)
8. **Palaukite 2-5 minutes**
9. **Testuokite**: https://chat.tossee.com/

---

### Variantas 2: Per VPS Terminal

**Jei nerandate nustatymo hPanel, įvykdykite VPS terminale:**

```bash
# Parodykite esamą konfigūraciją
find /etc/nginx /etc/apache2 ~/.config -name "*chat*" 2>/dev/null

# Arba kontaktuokite Hostinger support su klausimu:
# "How do I change chat.tossee.com document root to /home/u234011694/domains/chat.tossee.com/public_html?"
```

---

## 🧪 KAIP TESTUOTI:

Po pakeitimo:

1. **Išvalykite cache**: Ctrl+Shift+Delete arba Ctrl+F5
2. **Atidarykite**: https://chat.tossee.com/
3. **Paspauskite F12** → Network tab
4. **Patikrinkite**:
   - ✅ `/api/matching.php` turėtų grąžinti **200 OK** (ne 404!)
   - ✅ Turėtų matytis JSON klaida apie "Method not allowed" (nes nėra POST duomenų)

---

## 📁 FAILŲ STRUKTŪRA:

```
/home/u234011694/domains/chat.tossee.com/public_html/
├── index.html              (20KB - video chat UI)
└── api/
    ├── matching.php        (1.8KB - n8n proxy for matching)
    └── signaling.php       (1.8KB - n8n proxy for WebRTC)
```

---

## 🆘 JEI VIS DAR NEVEIKIA:

1. Kontaktuokite **Hostinger Support** ir paprašykite:
   > "Please update chat.tossee.com subdomain Document Root to:
   > /home/u234011694/domains/chat.tossee.com/public_html"

2. Arba pasidalinkite **hPanel screenshot** su subdomain nustatymais.

---

## ✅ KAI VEIKS:

Video chat matching sistema:
- ✅ Automatiškai startuos kai atidarysite puslapį
- ✅ Sujungs jus su kitu vartotoju
- ✅ WebRTC video/audio veiks peer-to-peer
- ✅ n8n matching workflow valdys queue sistemą

---

**Sukurta**: 2025-12-12
**Deployment path**: `/home/u234011694/domains/chat.tossee.com/public_html/`
**Branch**: `claude/init-workflow-storage-01CSdmmpqgG8rCgDRg9DXb8u`
