import {
  UserAgent,
  Registerer,
  Inviter,
  SessionState
} from "sip.js";

/* =========================
   SIP CREDENTIALS (TEST)
========================= */

const sipConfig = {
  uri: UserAgent.makeURI("sip:103@afritell.com"),

  transportOptions: {
    server: "wss://afritell.com:4433/ws"
  },

  authorizationUsername: "103",
  authorizationPassword: "Mn#erdS5678",

  sessionDescriptionHandlerFactoryOptions: {
    peerConnectionConfiguration: {
      iceServers: [
        {
          urls: "turn:turn.afritell.com:3478",
          username: "webrtc",
          credential: "j6A_zZCdFkWy78m?"
        }
      ]
    }
  }
};

/* =========================
   USER AGENT
========================= */

const userAgent = new UserAgent(sipConfig);

/* =========================
   REGISTER
========================= */

const registerer = new Registerer(userAgent);

async function startSip() {
  await userAgent.start();
  await registerer.register();
  console.log("SIP REGISTERED");
}

startSip();

/* =========================
   INCOMING CALL LOG
========================= */

userAgent.delegate = {
  onInvite(invitation) {
    console.log("Incoming call", invitation);
  }
};
