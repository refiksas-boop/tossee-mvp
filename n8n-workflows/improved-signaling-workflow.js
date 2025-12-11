// ============================================
// IMPROVED N8N SIGNALING WORKFLOW
// ============================================
// This should replace your second "Code in JavaScript1" node

const store = this.getWorkflowStaticData('global');

// INIT
if (!store.rooms) store.rooms = {};

const body = $json.body ?? $json;
const { roomId, type, data, user } = body;

// VALIDATE
if (!roomId) {
  return [{ ok: false, error: "Missing roomId" }];
}

if (!store.rooms[roomId]) {
  return [{ ok: false, error: "Room not found. It may have expired." }];
}

const room = store.rooms[roomId];

// ============================================
// SIGNALING TYPES
// ============================================

switch (type) {
  // WebRTC Offer (from initiator)
  case "offer":
    if (!data) {
      return [{ ok: false, error: "Missing offer data" }];
    }
    room.offers.push(data);
    return [{ ok: true, message: "Offer stored" }];

  // WebRTC Answer (from receiver)
  case "answer":
    if (!data) {
      return [{ ok: false, error: "Missing answer data" }];
    }
    room.answers.push(data);
    return [{ ok: true, message: "Answer stored" }];

  // ICE Candidate
  case "candidate":
    if (!data) {
      return [{ ok: false, error: "Missing candidate data" }];
    }
    if (!room.candidates) room.candidates = [];
    room.candidates.push({
      candidate: data,
      from: user || "unknown",
      timestamp: Date.now()
    });
    return [{ ok: true, message: "Candidate stored" }];

  // Retrieve all signaling data for this room
  case "check":
    return [{
      ok: true,
      roomData: {
        offers: room.offers || [],
        answers: room.answers || [],
        candidates: room.candidates || []
      }
    }];

  // Disconnect from room
  case "disconnect":
    if (user && room.users) {
      // Mark user as disconnected
      room.disconnectedUsers = room.disconnectedUsers || [];
      if (!room.disconnectedUsers.includes(user)) {
        room.disconnectedUsers.push(user);
      }

      // If both users disconnected, clean up room
      if (room.disconnectedUsers.length >= room.users.length) {
        delete store.rooms[roomId];
        return [{ ok: true, message: "Room closed" }];
      }
    }
    return [{ ok: true, message: "User disconnected" }];

  // Invalid type
  default:
    return [{ ok: false, error: "Invalid type. Use: offer, answer, candidate, check, disconnect" }];
}
