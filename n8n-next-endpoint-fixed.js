// N8N Code Node: /webhook/next
// PATAISYTA - Grąžina teisingą formatą N8N

// GLOBAL STORE
global.store = global.store || {};
const store = global.store;

store.queue ||= [];
store.rooms ||= {};

const now = Date.now();

// GET BODY - N8N formatas
const body = $input.item.json.body || $input.item.json;
const action = body.action || "match";
const userId = body.user;

// VALIDATION
if (!userId) {
  return [{
    json: {
      ok: false,
      message: "Missing user"
    }
  }];
}

// CLEANUP - Pašalina senus queue entries (>60s)
const oldQueueSize = store.queue.length;
store.queue = store.queue.filter(u => now - u.joinedAt < 60000);

if (oldQueueSize !== store.queue.length) {
  console.log(`[CLEANUP] Removed ${oldQueueSize - store.queue.length} old queue entries`);
}

// CHECK ACTION - Tikrina ar vartotojas jau matched
if (action === "check") {
  for (const [id, room] of Object.entries(store.rooms)) {
    if (room.users.includes(userId)) {
      return [{
        json: {
          ok: true,
          status: "matched",
          roomId: id,
          users: room.users
        }
      }];
    }
  }

  return [{
    json: {
      ok: true,
      status: "waiting"
    }
  }];
}

// MATCH ACTION - Bando surasti partnerį

// 1. Tikrina ar jau yra matched
for (const [id, room] of Object.entries(store.rooms)) {
  if (room.users.includes(userId)) {
    console.log(`[MATCH] User ${userId} already in room ${id}`);
    return [{
      json: {
        ok: true,
        status: "matched",
        roomId: id,
        users: room.users
      }
    }];
  }
}

// 2. Pašalina save iš queue (avoid duplicates)
store.queue = store.queue.filter(u => u.userId !== userId);

// 3. Ieško kito vartotojo
const partnerIndex = store.queue.findIndex(u => u.userId !== userId);

if (partnerIndex === -1) {
  // Nėra kito vartotojo - prideda save į queue
  store.queue.push({
    userId,
    joinedAt: now
  });

  console.log(`[MATCH] User ${userId} added to queue. Queue size: ${store.queue.length}`);

  return [{
    json: {
      ok: true,
      status: "waiting",
      queueSize: store.queue.length
    }
  }];
}

// 4. MATCH FOUND!
const partner = store.queue.splice(partnerIndex, 1)[0];
const roomId = "room_" + Math.random().toString(36).substring(2, 10);

store.rooms[roomId] = {
  users: [userId, partner.userId],
  offers: [],
  answers: [],
  candidates: [],
  createdAt: now
};

console.log(`[MATCH] Created room ${roomId} for ${userId} and ${partner.userId}`);

return [{
  json: {
    ok: true,
    status: "matched",
    roomId,
    users: [userId, partner.userId]
  }
}];
