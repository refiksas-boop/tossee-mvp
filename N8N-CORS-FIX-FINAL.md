# N8N FINAL SETUP - GUARANTEED CORS FIX

## PROBLEMA (PROBLEM)

Browser CORS error - n8n negrąžina tinkamų headers arba OPTIONS handling neveikia.

---

## SPRENDIMAS (SOLUTION)

### BŪDAS 1: Naudoti n8n CORS Proxy (REKOMENDUOJAMA)

Vietoj tiesioginių webhook'ų, naudoti **n8n HTTP Request node su CORS**.

#### Setup:

1. **Sukurti backend endpoint** (PHP ar serverless function)
2. **Tas endpoint** turės CORS headers
3. **Iš to endpoint** kviesti n8n webhooks (server-to-server, no CORS!)

---

### BŪDAS 2: Nginx/Cloudflare Proxy su CORS

Pridėti CORS headers per reverse proxy:

#### Nginx:
```nginx
location /webhook/ {
    if ($request_method = 'OPTIONS') {
        add_header 'Access-Control-Allow-Origin' '*';
        add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'Content-Type';
        add_header 'Content-Length' 0;
        return 204;
    }

    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type' always;

    proxy_pass http://n8n.tossee.com;
}
```

#### Cloudflare Worker:
```javascript
async function handleRequest(request) {
  // Handle preflight
  if (request.method === 'OPTIONS') {
    return new Response(null, {
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'POST, OPTIONS',
        'Access-Control-Allow-Headers': 'Content-Type',
      }
    });
  }

  // Forward to n8n
  const response = await fetch(request);
  const newResponse = new Response(response.body, response);

  // Add CORS headers
  newResponse.headers.set('Access-Control-Allow-Origin', '*');
  newResponse.headers.set('Access-Control-Allow-Methods', 'POST, OPTIONS');
  newResponse.headers.set('Access-Control-Allow-Headers', 'Content-Type');

  return newResponse;
}

addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request));
});
```

---

### BŪDAS 3: PHP Proxy (GREIČIAUSIAS!)

Sukurti PHP failą ant chat.tossee.com serverio:

#### `/api/matching.php`:
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Forward to n8n
$data = file_get_contents('php://input');

$ch = curl_init('https://n8n.tossee.com/webhook/matching');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $response;
?>
```

#### `/api/signaling.php`:
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Forward to n8n
$data = file_get_contents('php://input');

$ch = curl_init('https://n8n.tossee.com/webhook/signal');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $response;
?>
```

#### Frontend kodas (pakeisti):
```javascript
// Vietoj:
fetch("https://n8n.tossee.com/webhook/matching", ...)

// Naudoti:
fetch("https://chat.tossee.com/api/matching.php", ...)
fetch("https://chat.tossee.com/api/signaling.php", ...)
```

---

## REKOMENDUOJAMAS BŪDAS

**PHP Proxy** yra greičiausias ir paprasčiausias!

### Kodėl?
- ✅ 100% CORS kontrolė
- ✅ Veikia iš karto
- ✅ Nereikia keisti n8n
- ✅ Server-to-server (n8n nežinos apie CORS)
- ✅ Paprastas debug'inimas

---

## VEIKSMAI (ACTIONS)

1. Sukurti `/api/matching.php` ant chat.tossee.com
2. Sukurti `/api/signaling.php` ant chat.tossee.com
3. Frontend kode pakeisti URLs
4. Testuoti!

---

Nori kad sukurčiau pilnus PHP proxy failus?
