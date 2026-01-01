import { getSIP } from './sip-global';

let currentSession = null;

window.makeCall = async function (number) {
  const SIP = getSIP();
  if (!SIP) {
    console.error("SIP.js not loaded. Cannot make call.");
    return;
  }

  if (!window.userAgent) {
    alert("SIP not ready");
    return;
  }

  const target = `sip:${number}@afritell.com`;

  const inviter = new SIP.Inviter(window.userAgent, target, {
    sessionDescriptionHandlerOptions: {
      constraints: { audio: true, video: false }
    }
  });

  currentSession = inviter;

  inviter.stateChange.addListener((state) => {
    console.log("CALL STATE:", state);
  });

  await inviter.invite();
};

window.hangupCall = async function () {
  const SIP = getSIP();
  if (!SIP) {
    console.error("SIP.js not loaded. Cannot hang up.");
    return;
  }

  if (currentSession) {
    await currentSession.bye();
    currentSession = null;
  }
};
