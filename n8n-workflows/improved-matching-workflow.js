// ============================================
// IMPROVED N8N MATCHING WORKFLOW
// ============================================
// This should replace your first "Code in JavaScript" node

const data = this.getWorkflowStaticData('global');

// INIT STORAGE
if (!data.rooms) data.rooms = {};
if (!data.queue) data.queue = [];
if (!data.lastCleanup) data.lastCleanup = Date.now();

// CLEANUP OLD WAITING USERS (every 5 minutes)
const now = Date.now();
if (now - data.lastCleanup > 5 * 60 * 1000) {
  // Remove users waiting more than 2 minutes
  data.queue = data.queue.filter(item => {
    return (now - item.timestamp) < 2 * 60 * 1000;
  });
  data.lastCleanup = now;
}

// PARSE REQUEST
const body = $json.body ?? $json;
const user = body.user;
const action = body.action || "join"; // "join" or "check"

if (!user) {
  return { ok: false, status: "error", message: "Missing user" };
}

// ============================================
// ACTION: CHECK FOR MATCH
// ============================================
if (action === "check") {
  // Find if user already has a room
  for (const [roomId, room] of Object.entries(data.rooms)) {
    if (room.users.includes(user)) {
      return {
        ok: true,
        status: "matched",
        roomId,
        users: room.users,
        peer: room.users.find(u => u !== user)
      };
    }
  }

  // Check if still in queue
  const queueItem = data.queue.find(item => item.user === user);
  if (queueItem) {
    return {
      ok: true,
      status: "waiting",
      position: data.queue.indexOf(queueItem) + 1
    };
  }

  // Not in queue, not in room - need to rejoin
  return {
    ok: true,
    status: "not_found",
    message: "User not in queue. Send action=join to enter."
  };
}

// ============================================
// ACTION: JOIN QUEUE
// ============================================

// Remove user from any existing queue position (prevent duplicates)
data.queue = data.queue.filter(item => item.user !== user);

// Check if there's someone waiting
if (data.queue.length > 0) {
  // MATCH WITH FIRST PERSON IN QUEUE
  const other = data.queue.shift(); // Remove from queue
  const roomId = "room_" + Math.random().toString(36).substring(2, 10);

  data.rooms[roomId] = {
    users: [user, other.user],
    createdAt: Date.now(),
    offers: [],
    answers: [],
    candidates: []
  };

  return {
    ok: true,
    status: "matched",
    roomId,
    users: [user, other.user],
    peer: other.user
  };
}

// NO ONE WAITING - ADD TO QUEUE
data.queue.push({
  user: user,
  timestamp: now
});

return {
  ok: true,
  status: "waiting",
  position: data.queue.length,
  message: "Added to queue. Poll with action=check every 2-3 seconds."
};
