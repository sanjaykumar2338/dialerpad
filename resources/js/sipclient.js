import { UserAgent } from "sip.js";

const userAgent = new UserAgent({
  uri: "sip:1000@example.com",
  transportOptions: {
    server: "wss://YOUR_PBX_DOMAIN:PORT/ws"
  },
  authorizationUsername: "1000",
  authorizationPassword: "PASSWORD"
});

userAgent.start()
  .then(() => {
    console.log("SIP Registered");
  })
  .catch(error => {
    console.error("SIP Registration Failed", error);
  });
