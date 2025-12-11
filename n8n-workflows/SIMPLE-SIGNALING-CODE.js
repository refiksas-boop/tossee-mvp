// ============================================
// TIKTAI ŠĮ KODĄ KOPIJUOK Į N8N "CODE" NODE!
// JUST COPY THIS CODE TO N8N "CODE" NODE!
// ============================================

// PERSISTENT STORAGE (Išsaugojimas)
const store = this.getWorkflowStaticData('global');

// INIT (Inicializacija)
if (!store.rooms) store.rooms = {};

// Gauti duomenis
const body = $json.body ?? $json;
const roomId = body.roomId;
const type = body.type;
const data = body.data;
const user = body.user;

// Patikrinti ar yra roomId
if (!roomId) {
  return { ok: false, error: "Missing roomId" };
}

// Jei kambarys neegzistuoja ir tipas ne "check"
if (!store.rooms[roomId] && type !== "check") {
  return { ok: false, error: "Room not found" };
}

// Sukurti kambarį jei neegzistuoja (check)
if (!store.rooms[roomId] && type === "check") {
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
  return { ok: true, message: "Offer stored" };
}

else if (type === "answer") {
  // WebRTC Answer
  if (!room.answers) room.answers = [];
  room.answers.push(data);
  return { ok: true, message: "Answer stored" };
}

else if (type === "candidate") {
  // ICE Candidate
  if (!room.candidates) room.candidates = [];
  room.candidates.push({
    candidate: data,
    from: user || "unknown",
    timestamp: Date.now()
  });
  return { ok: true, message: "Candidate stored" };
}

else if (type === "check") {
  // Gauti visus signalus
  return {
    ok: true,
    roomData: {
      offers: room.offers || [],
      answers: room.answers || [],
      candidates: room.candidates || []
    }
  };
}

else if (type === "disconnect") {
  // Atsijungti
  if (user && room.users) {
    if (!room.disconnectedUsers) room.disconnectedUsers = [];
    if (!room.disconnectedUsers.includes(user)) {
      room.disconnectedUsers.push(user);
    }

    // Jei abu atsijungė - ištrinti kambarį
    if (room.disconnectedUsers.length >= room.users.length) {
      delete store.rooms[roomId];
      return { ok: true, message: "Room closed" };
    }
  }
  return { ok: true, message: "User disconnected" };
}

else {
  // Nežinomas tipas
  return { ok: false, error: "Invalid type. Use: offer, answer, candidate, check, disconnect" };
}
