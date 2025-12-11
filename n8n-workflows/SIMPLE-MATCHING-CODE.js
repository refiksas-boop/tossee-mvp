// ============================================
// TIKTAI ŠĮ KODĄ KOPIJUOK Į N8N "CODE" NODE!
// JUST COPY THIS CODE TO N8N "CODE" NODE!
// ============================================

// PERSISTENT STORAGE (Išsaugojimas)
const data = this.getWorkflowStaticData('global');

// INIT (Inicializacija)
if (!data.rooms) data.rooms = {};
if (!data.queue) data.queue = [];

// BODY FIX (Gauti duomenis)
const body = $json.body ?? $json;
const user = body.user;
const action = body.action || "join";

// Patikrinti ar yra vartotojas
if (!user) {
  return { ok: false, status: "error", message: "Missing user" };
}

// ============================================
// JEI action = "check" (tikrinti ar sujungta)
// ============================================
if (action === "check") {
  // Ieškoti ar jau turi kambarį
  for (const [roomId, room] of Object.entries(data.rooms)) {
    if (room.users && room.users.includes(user)) {
      return {
        ok: true,
        status: "matched",
        roomId: roomId,
        users: room.users,
        peer: room.users.find(u => u !== user)
      };
    }
  }

  // Patikrinti ar dar eilėje
  const inQueue = data.queue.find(item => item === user || item.user === user);
  if (inQueue) {
    return {
      ok: true,
      status: "waiting",
      position: data.queue.indexOf(inQueue) + 1
    };
  }

  // Nėra nei eilėje, nei kambaryje
  return {
    ok: true,
    status: "not_found",
    message: "User not in queue"
  };
}

// ============================================
// JEI action = "join" (prisijungti)
// ============================================

// Pašalinti dublikatus iš eilės
data.queue = data.queue.filter(item => {
  if (typeof item === 'string') return item !== user;
  return item.user !== user;
});

// JEI EILĖ TUŠČIA → pridėti į eilę
if (data.queue.length === 0) {
  data.queue.push(user);
  return {
    ok: true,
    status: "waiting",
    position: 1,
    message: "Waiting for stranger..."
  };
}

// JEI YRA KAS NORS EILĖJE → SUJUNGTI!
const other = data.queue.shift(); // Paimti pirmą iš eilės
const otherUser = typeof other === 'string' ? other : other.user;

const roomId = "room_" + Math.random().toString(36).substring(2, 10);

data.rooms[roomId] = {
  users: [user, otherUser],
  createdAt: Date.now(),
  offers: [],
  answers: [],
  candidates: []
};

return {
  ok: true,
  status: "matched",
  roomId: roomId,
  users: [user, otherUser],
  peer: otherUser
};
