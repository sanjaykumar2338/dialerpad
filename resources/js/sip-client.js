let currentSession = null;

window.makeCall = async function (number) {
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
  if (currentSession) {
    await currentSession.bye();
    currentSession = null;
  }
};
