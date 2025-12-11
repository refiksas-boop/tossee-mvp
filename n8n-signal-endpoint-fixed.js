// N8N Code Node: /webhook/signal
// PATAISYTA - Grąžina teisingą formatą N8N

globalThis.store = globalThis.store || {};
const store = globalThis.store;

if (!store.rooms) store.rooms = {};

// GET BODY - N8N formatas
const body = $input.item.json.body || $input.item.json;
const { roomId, type, data, user, userId } = body;

const uid = user || userId;

// VALIDATION
if (!type) {
  return [{
    json: {
      ok: false,
      error: "Missing type"
    }
  }];
}

if (!roomId || !store.rooms[roomId]) {
  if (type === "check") {
    return [{
      json: {
        ok: true,
        roomData: {
          offers: [],
          answers: [],
          candidates: []
        }
      }
    }];
  }

  return [{
    json: {
      ok: false,
      error: "Room not found"
    }
  }];
}

const room = store.rooms[roomId];

// INIT SIGNAL ARRAYS
room.offers = room.offers || [];
room.answers = room.answers || [];
room.candidates = room.candidates || [];

// HANDLE SIGNAL TYPE
switch (type) {
  case "offer":
    room.offers = [{
      ...data,
      timestamp: Date.now(),
      from: uid
    }];
    console.log(`[SIGNAL] Offer saved in room ${roomId}`);
    return [{ json: { ok: true } }];

  case "answer":
    room.answers = [{
      ...data,
      timestamp: Date.now(),
      from: uid
    }];
    console.log(`[SIGNAL] Answer saved in room ${roomId}`);
    return [{ json: { ok: true } }];

  case "candidate":
    room.candidates.push({
      ...data,
      timestamp: Date.now(),
      from: uid
    });
    console.log(`[SIGNAL] ICE candidate added to room ${roomId}`);
    return [{ json: { ok: true } }];

  case "check":
    return [{
      json: {
        ok: true,
        roomData: {
          offers: room.offers,
          answers: room.answers,
          candidates: room.candidates
        }
      }
    }];

  case "disconnect":
    console.log(`[SIGNAL] User ${uid} disconnected from room ${roomId}`);
    delete store.rooms[roomId];
    return [{ json: { ok: true } }];

  default:
    return [{
      json: {
        ok: false,
        error: `Unknown signal type: ${type}`
      }
    }];
}
