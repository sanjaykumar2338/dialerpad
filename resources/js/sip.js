/* =========================================================
   SIP.JS WEBRTC CLIENT – FINAL LOCKED VERSION
   Loaded via <script> → window.SIP
========================================================= */

if (!window.SIP) {
  console.error("SIP.js not loaded on window");
}

/* =========================
   SIP CLASSES
========================= */
const { UserAgent, Registerer, Inviter } = window.SIP;

/* =========================
   CONSTANTS (LOCKED)
========================= */
const SIP_DOMAIN = "afritell.com";
const WSS_URL = "wss://afritell.com:8089/ws";

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
   (CALL ONLY AFTER BACKEND
   RETURNS EXTENSION + PASSWORD)
========================= */
export async function initSip({ extension, password }) {
  if (userAgent) {
    console.warn("SIP already initialized — skipping");
    return;
  }

  // 🔒 LOCKED SIP IDENTITY
  const sipUri = `sip:${extension}@${SIP_DOMAIN}`;

  // 🔎 MANDATORY DEBUG LOG (PBX CONFIRMATION)
  console.log("SIP CONFIG CHECK", {
    uri: sipUri,
    authorizationUsername: extension,
    transportServer: WSS_URL
  });

  const sipConfig = {
    uri: UserAgent.makeURI(sipUri),
    authorizationUsername: String(extension),
    authorizationPassword: password,
    transportOptions: {
      server: WSS_URL
    },
    sessionDescriptionHandlerFactoryOptions: {
      constraints: {
        audio: true,
        video: false
      }
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
   PLACE CALL (STRICTLY GATED)
========================= */
export function placeCall(number) {
  if (!isRegistered || !userAgent) {
    console.warn("CALL BLOCKED — SIP NOT REGISTERED");
    return;
  }

  if (currentSession) {
    cleanupSession();
  }

  const normalized = normalizeNumber(number);
  const targetUri = UserAgent.makeURI(`sip:${normalized}@${SIP_DOMAIN}`);

  console.log("PLACING CALL TO:", targetUri.toString());

  currentSession = new Inviter(userAgent, targetUri);

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
