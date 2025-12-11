// ============================================
// SIGNALING WORKFLOW - PATAISYTA VERSIJA
// Compatible su tavo frontend kodu
// ============================================

// GLOBAL STORE
globalThis.store = globalThis.store || {};
const store = globalThis.store;

store.rooms ||= {};

// GET BODY
const body = $json.body ?? $json;
const roomId = body.roomId;
const type = body.type;
const data = body.data;
const user = body.user || body.userId;

if (!roomId) {
  return {  // ← BE "json:"
    ok: false,
    error: "Missing roomId"
  };
}

// Sukurti kambarį jei neegzistuoja
if (!store.rooms[roomId]) {
  store.rooms[roomId] = {
    offers: [],
    answers: [],
    candidates: []
  };
}

const room = store.rooms[roomId];

// ============================================
// VEIKSMAI PAGAL TYPE
// ============================================

if (type === "offer") {
  // WebRTC Offer
  if (!room.offers) room.offers = [];
  room.offers.push(data);
  return {  // ← BE "json:"
    ok: true,
    message: "Offer stored"
  };
}

else if (type === "answer") {
  // WebRTC Answer
  if (!room.answers) room.answers = [];
  room.answers.push(data);
  return {  // ← BE "json:"
    ok: true,
    message: "Answer stored"
  };
}

else if (type === "candidate") {
  // ICE Candidate - TIESIOG DATA (compatible su senu kodu)
  if (!room.candidates) room.candidates = [];
  room.candidates.push(data);
  return {  // ← BE "json:"
    ok: true,
    message: "Candidate stored"
  };
}

else if (type === "check") {
  // Gauti visus signalus
  return {  // ← BE "json:"
    ok: true,
    roomData: {
      offers: room.offers || [],
      answers: room.answers || [],
      candidates: room.candidates || []
    }
  };
}

else if (type === "disconnect") {
  // Ištrinti kambarį
  if (store.rooms[roomId]) {
    delete store.rooms[roomId];
  }
  return {  // ← BE "json:"
    ok: true,
    message: "Room closed"
  };
}

else {
  // Nežinomas tipas
  return {  // ← BE "json:"
    ok: false,
    error: "Invalid type: " + type
  };
}
