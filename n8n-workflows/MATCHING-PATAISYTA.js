// ============================================
// MATCHING WORKFLOW - PATAISYTA VERSIJA
// Compatible su tavo frontend kodu
// ============================================

// GLOBAL STORE
globalThis.store = globalThis.store || {};
const store = globalThis.store;

store.queue ||= [];
store.rooms ||= {};  // ← TURI BŪTI OBJEKTAS, ne masyvas!

const now = Date.now();

// GET BODY
const body = $json.body ?? $json;
const action = body.action;
const userId = body.userId;

if (!userId || !action) {
  return {  // ← BE "json:"
    ok: false,
    message: "Missing userId or action"
  };
}

// CLEAN QUEUE (pašalinti senus vartotojus > 60s)
store.queue = store.queue.filter(u => now - u.joinedAt < 60000);

// FIND EXISTING ROOM
for (const [id, room] of Object.entries(store.rooms)) {
  if (room.users.includes(userId)) {
    return {  // ← BE "json:"
      ok: true,
      status: "matched",
      roomId: id,
      users: room.users,
      partnerId: room.users.find(u => u !== userId)
    };
  }
}

//
// ACTION HANDLING
//
if (action === "join" || action === "check") {

  // Pašalinti vartotoją iš eilės (jei buvo)
  store.queue = store.queue.filter(q => q.userId !== userId);

  // Ieškoti partnerio
  const partnerIndex = store.queue.findIndex(q => q.userId !== userId);

  if (partnerIndex === -1) {
    // Nėra partnerio - pridėti į eilę
    store.queue.push({ userId, joinedAt: now });

    return {  // ← BE "json:"
      ok: true,
      status: "waiting",
      position: store.queue.length
    };
  }

  // Yra partneris - sukurti kambarį
  const partner = store.queue.splice(partnerIndex, 1)[0];

  const roomId = "room_" + Math.random().toString(36).substring(2, 10);

  store.rooms[roomId] = {
    users: [userId, partner.userId],
    offers: [],
    answers: [],
    candidates: [],
    createdAt: now
  };

  return {  // ← BE "json:"
    ok: true,
    status: "matched",
    roomId,
    users: [userId, partner.userId],
    partnerId: partner.userId
  };
}

// UNKNOWN ACTION
return {  // ← BE "json:"
  ok: false,
  message: "Unknown action: " + action
};
