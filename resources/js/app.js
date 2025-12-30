import { UserAgent, Registerer, Inviter } from "sip.js";

console.log("SIP.js loaded", UserAgent);
import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
const sipUserAgent = new UserAgent({
  uri: UserAgent.makeURI("sip:103@afritell.com"),
  transportOptions: {
    server: "wss://afritell.com:4433/ws"
  },
  authorizationUsername: "103",
  authorizationPassword: "Mn#erdS5678",
  sessionDescriptionHandlerFactoryOptions: {
    constraints: {
      audio: true,
      video: false
    },
    peerConnectionOptions: {
      rtcConfiguration: {
        iceServers: [
          {
            urls: "turn:turn.afritell.com:3478",
            username: "webrtc",
            credential: "j6A_zZCdFkWy78m?"
          }
        ]
      }
    }
  }
});

console.log("SIP UserAgent created", sipUserAgent);
import "./sipClient";
import "./sip";
import "./sip-client";
