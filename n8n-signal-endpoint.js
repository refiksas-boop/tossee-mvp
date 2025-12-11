// N8N Webhook: /webhook/signal
// Tvarko WebRTC signaling (offers, answers, ICE candidates)

globalThis.store = globalThis.store || {};
const store = globalThis.store;

if (!store.rooms) store.rooms = {};

const body = $json.body ?? $json;
const { roomId, type, data, user, userId } = body;

const uid = user || userId;

// VALIDATION
if (!type) {
  return [
    {
      json: {
        ok: false,
        error: "Missing type"
      }
    }
  ];
}

if (!roomId || !store.rooms[roomId]) {
  // Jei type yra check - gali būti kad room dar nesukurtas
  if (type === "check") {
    return [
      {
        json: {
          ok: true,
          roomData: {
            offers: [],
            answers: [],
            candidates: []
          }
        }
      }
    ];
  }

  return [
    {
      json: {
        ok: false,
        error: "Room not found"
      }
    }
  ];
}

const room = store.rooms[roomId];

// INIT SIGNAL ARRAYS (if missing)
room.offers = room.offers || [];
room.answers = room.answers || [];
room.candidates = room.candidates || [];

// HANDLE SIGNAL TYPE
switch (type) {
  case "offer":
    // PATAISYTA: Išsaugo offer su timestamp
    room.offers = [{
      ...data,
      timestamp: Date.now(),
      from: uid
    }];
    console.log(`[SIGNAL] Offer saved in room ${roomId}`);
    return [{ json: { ok: true } }];

  case "answer":
    // PATAISYTA: Išsaugo answer su timestamp
    room.answers = [{
      ...data,
      timestamp: Date.now(),
      from: uid
    }];
    console.log(`[SIGNAL] Answer saved in room ${roomId}`);
    return [{ json: { ok: true } }];

  case "candidate":
    // PATAISYTA: Prideda ICE candidate su info kas atsiuntė
    room.candidates.push({
      ...data,
      timestamp: Date.now(),
      from: uid
    });
    console.log(`[SIGNAL] ICE candidate added to room ${roomId}`);
    return [{ json: { ok: true } }];

  case "check":
    // Grąžina visus signalus
    return [
      {
        json: {
          ok: true,
          roomData: {
            offers: room.offers,
            answers: room.answers,
            candidates: room.candidates
          }
        }
      }
    ];

  case "disconnect":
    // PATAISYTA: Pašalina room kai vartotojas disconnectin
    console.log(`[SIGNAL] User ${uid} disconnected from room ${roomId}`);
    delete store.rooms[roomId];
    return [{ json: { ok: true } }];

  default:
    return [
      {
        json: {
          ok: false,
          error: `Unknown signal type: ${type}`
        }
      }
    ];
}
