// ============================================
// MATCHING WORKFLOW - WITH OPTIONS SUPPORT
// Handles CORS preflight OPTIONS requests
// ============================================

// GET REQUEST METHOD
const method = $input.item.json.headers['x-request-method'] ||
               $input.item.json.method ||
               'POST';

// HANDLE OPTIONS (CORS PREFLIGHT)
if (method === 'OPTIONS') {
  return {
    ok: true,
    message: "CORS preflight OK"
  };
}

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
// ACTION: CHECK (tik patikrinti, NEKEISTI queue!)
//
if (action === "check") {
  // Patikrinti ar user jau yra queue
  const inQueue = store.queue.find(function(q) {
    return q.userId === userId;
  });

  if (inQueue) {
    return {
      ok: true,
      status: "waiting",
      queueSize: store.queue.length
    };
  } else {
    // User nėra queue - turėtų būti "join"
    return {
      ok: true,
      status: "not_in_queue",
      message: "Use action=join to enter queue"
    };
  }
}

//
// ACTION: JOIN (prisijungti ir ieškoti partnerio)
//
if (action === "join") {

  // Pašalinti vartotoją iš eilės (jei buvo - prevent duplicates)
  store.queue = store.queue.filter(function(q) {
    return q.userId !== userId;
  });

  // Ieškoti partnerio (KITAS nei šis vartotojas)
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
      queueSize: store.queue.length
    };
  }

  // FOUND PARTNER! Sukurti kambarį
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
  message: "Unknown action: " + action + " (use 'join' or 'check')"
};
