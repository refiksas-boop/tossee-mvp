// ============================================
// MATCHING WORKFLOW - FINAL VERSION
// 100% Compatible su N8N ir tavo frontend
// ============================================

// GLOBAL STORE
if (!globalThis.store) {
  globalThis.store = {};
}
const store = globalThis.store;

// Initialize
if (!store.queue) {
  store.queue = [];
}
if (!store.rooms) {
  store.rooms = {};
}

const now = Date.now();

// GET BODY
const body = $json.body || $json;
const action = body.action;
const userId = body.userId;

if (!userId || !action) {
  return {
    ok: false,
    message: "Missing userId or action"
  };
}

// CLEAN QUEUE (pašalinti senus vartotojus > 60s)
store.queue = store.queue.filter(function(u) {
  return (now - u.joinedAt) < 60000;
});

// FIND EXISTING ROOM
for (const roomId in store.rooms) {
  const room = store.rooms[roomId];
  if (room.users && room.users.includes(userId)) {
    const partnerId = room.users.find(function(u) {
      return u !== userId;
    });
    return {
      ok: true,
      status: "matched",
      roomId: roomId,
      users: room.users,
      partnerId: partnerId
    };
  }
}

//
// ACTION HANDLING
//
if (action === "join" || action === "check") {

  // Pašalinti vartotoją iš eilės (jei buvo)
  store.queue = store.queue.filter(function(q) {
    return q.userId !== userId;
  });

  // Ieškoti partnerio
  let partnerIndex = -1;
  for (let i = 0; i < store.queue.length; i++) {
    if (store.queue[i].userId !== userId) {
      partnerIndex = i;
      break;
    }
  }

  if (partnerIndex === -1) {
    // Nėra partnerio - pridėti į eilę
    store.queue.push({ userId: userId, joinedAt: now });

    return {
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

  return {
    ok: true,
    status: "matched",
    roomId: roomId,
    users: [userId, partner.userId],
    partnerId: partner.userId
  };
}

// UNKNOWN ACTION
return {
  ok: false,
  message: "Unknown action: " + action
};
