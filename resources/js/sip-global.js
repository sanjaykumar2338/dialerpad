export function getSIP() {
    const sip = window.SIP;
    if (!sip) {
        console.error('SIP.js not loaded. Ensure sip.min.js is included before app scripts.');
        return null;
    }
    return sip;
}
