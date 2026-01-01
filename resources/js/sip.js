const { UserAgent, Registerer, Inviter } = window.SIP;
/* =========================
   GLOBAL STATE (SINGLETON)
========================= */

let userAgent = null;
let registerer = null;
let currentSession = null;

let isRegistered = false;

/* =========================
   UI HELPERS
========================= */

function updateUiRegistration(registered) {
  const status = document.getElementById("sip-status");
  const callBtn = document.getElementById("call-btn");

  if (!status || !callBtn) return;

  if (registered) {
    status.innerText = "REGISTERED";
    status.style.color = "green";
    callBtn.disabled = false;
  } else {
    status.innerText = "NOT REGISTERED";
    status.style.color = "red";
    callBtn.disabled = true;
  }
}

/* =========================
   SIP INITIALIZATION
   (CALLED ONLY AFTER
   BACKEND SENDS CREDENTIALS)
========================= */

export async function initSip({
  extension,
  password,
  domain,
  wss
}) {
  if (userAgent) {
    console.warn("SIP already initialized — skipping");
    return;
  }

  const sipConfig = {
    uri: UserAgent.makeURI(`sip:${extension}@${domain}`),
    authorizationUsername: extension,
    authorizationPassword: password,
    transportOptions: {
      server: wss
    }
  };

  userAgent = new UserAgent(sipConfig);
  registerer = new Registerer(userAgent);

  registerer.stateChange.addListener((state) => {
    console.log("REGISTER STATE:", state);

    if (state === "Registered") {
      isRegistered = true;
      updateUiRegistration(true);
    }

    if (state === "Unregistered" || state === "Terminated") {
      isRegistered = false;
      updateUiRegistration(false);
    }
  });

  userAgent.delegate = {
    onInvite(invitation) {
      console.log("Incoming call", invitation);
    }
  };

  await userAgent.start();
  await registerer.register();
}

/* =========================
   PLACE CALL (GATED)
========================= */

export function placeCall(number) {
  if (!isRegistered) {
    console.warn("CALL BLOCKED — NOT REGISTERED");
    return;
  }

  if (currentSession) {
    cleanupSession();
  }

  const normalized = normalizeNumber(number);
  const target = UserAgent.makeURI(
    `sip:${normalized}@afritell.com`
  );

  currentSession = new Inviter(userAgent, target);

  currentSession.stateChange.addListener((state) => {
    console.log("CALL STATE:", state);

    if (state === "Terminated") {
      cleanupSession();
    }
  });

  currentSession.invite().catch((err) => {
    console.error("INVITE FAILED", err);
    cleanupSession();
  });
}

/* =========================
   CLEANUP (NO EXCEPTIONS)
========================= */

function cleanupSession() {
  try { currentSession?.bye(); } catch (e) {}
  try { currentSession?.dispose(); } catch (e) {}
  currentSession = null;
}

window.addEventListener("beforeunload", cleanupSession);

/* =========================
   UTILITIES
========================= */

function normalizeNumber(num) {
  return num.replace(/\D/g, "");
}
