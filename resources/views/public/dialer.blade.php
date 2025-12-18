@extends('layouts.public')

@section('content')
<style>
    html, body {
        touch-action: manipulation;
        overscroll-behavior: none;
    }

    .dialer,
    .dialer * {
        touch-action: manipulation;
    }

    .dialer-key {
        font-size: 18px;
        min-width: 64px;
        min-height: 64px;
        border-radius: 50%;
    }

    .dialer-wrapper {
        height: 100vh;
        max-height: 100vh;
        overflow: hidden;
    }
</style>

<div class="dialer-wrapper dialer min-h-screen flex items-center justify-center bg-gradient-to-b from-slate-900 to-slate-950 text-white">
    <div class="w-full max-w-sm bg-slate-900/80 rounded-3xl p-6 shadow-xl border border-slate-700">
        <div class="mb-4 text-center">
            <div class="text-xs uppercase tracking-widest text-slate-400 mb-1">Call Card</div>
            <div class="text-lg font-semibold">{{ $card->name }}</div>
            <div class="text-xs text-slate-400 mt-1">
                Remaining: <span id="remainingMinutes">{{ $card->remaining_minutes }}</span> min
            </div>
        </div>

        <div class="mb-4 text-center">
            <div class="text-xs text-slate-500">Number</div>
            <div class="text-2xl font-semibold tracking-wide">
                {{ $card->prefix }}<span id="dialedNumberDisplay"></span>
            </div>
            <div id="dialingPreview" class="text-xs text-slate-500 mt-1"></div>
        </div>

        <div class="mb-4 flex justify-center">
            <div class="w-24 h-24 rounded-full border border-slate-700 flex items-center justify-center">
                <span id="callTimer" class="text-xl font-mono">00:00</span>
            </div>
        </div>

        <input type="hidden" id="dialedNumber" value="">
        <input type="hidden" id="sessionUuid" value="">
        <input type="hidden" id="cardUuid" value="{{ $card->uuid }}">

        {{-- Keypad --}}
        <div id="keypad" class="grid grid-cols-3 gap-3 mb-4 text-xl">
            @foreach (['1','2','3','4','5','6','7','8','9','*','0','#'] as $key)
                <button type="button"
                        class="dialer-key py-3 rounded-full bg-slate-800 hover:bg-slate-700 transition"
                        tabindex="-1"
                        onclick="appendDigit('{{ $key }}')">
                    {{ $key }}
                </button>
            @endforeach
        </div>

        <div class="flex justify-end mb-4">
            <button type="button"
                    id="backspaceBtn"
                    class="px-4 py-2 rounded-full bg-slate-800 hover:bg-slate-700 transition text-sm"
                    onclick="backspace()">
                ⌫
            </button>
        </div>

        {{-- Controls --}}
        <div class="flex items-center justify-center gap-4">
            <button id="muteBtn"
                    type="button"
                    class="w-16 h-10 rounded-full bg-slate-800 text-xs"
                    onclick="toggleMute()">
                Mute
            </button>

            <button id="callBtn"
                    type="button"
                    class="w-16 h-16 rounded-full bg-emerald-500 hover:bg-emerald-400 text-sm font-semibold"
                    onclick="toggleCall()">
                Call
            </button>

            <button id="speakerBtn"
                    type="button"
                    class="w-24 h-10 rounded-full bg-slate-800 text-xs"
                    onclick="toggleSpeaker()">
                Spk
            </button>
        </div>

        <div id="statusMessage" class="mt-4 text-center text-xs text-slate-400"></div>
    </div>
</div>

<script>
    let inCall = false;
    let timerInterval = null;
    let elapsedSeconds = 0;
    let ringingTimeout = null;
    let micStream = null;
    const state = {
        muted: false,
        speaker: false,
    };
    const cardPrefix = @json($card->prefix);
    const dialPrefixDefault = @json(config('pbx.dial_prefix_default', '223'));
    const dialPrefixGateway = @json(config('pbx.dial_prefix_gateway', ''));

    function renderNumber() {
        const input = document.getElementById('dialedNumber');
        const display = document.getElementById('dialedNumberDisplay');
        display.textContent = input.value;
        if (!inCall) {
            setDialingPreview('');
        }
    }

    function appendDigit(digit) {
        if (inCall) return;
        const input  = document.getElementById('dialedNumber');
        input.value += digit;
        renderNumber();
    }

    function backspace() {
        if (inCall) return;
        const input = document.getElementById('dialedNumber');
        if (!input.value) return;
        input.value = input.value.slice(0, -1);
        renderNumber();
    }

    function setKeypadDisabled(state) {
        document.querySelectorAll('#keypad button').forEach((btn) => {
            btn.disabled = state;
            if (state) {
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });

        const backspaceBtn = document.getElementById('backspaceBtn');
        if (backspaceBtn) {
            backspaceBtn.disabled = state;
            if (state) {
                backspaceBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                backspaceBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    function updateControlButtons() {
        const muteBtn = document.getElementById('muteBtn');
        const speakerBtn = document.getElementById('speakerBtn');
        const disabled = !inCall;

        if (muteBtn) {
            muteBtn.textContent = state.muted ? 'Sourdine' : 'Mute';
            muteBtn.disabled = disabled;
            muteBtn.classList.toggle('opacity-50', disabled);
            muteBtn.classList.toggle('cursor-not-allowed', disabled);
        }

        if (speakerBtn) {
            speakerBtn.textContent = state.speaker ? 'Speaker On' : 'Spk';
            speakerBtn.disabled = disabled;
            speakerBtn.classList.toggle('opacity-50', disabled);
            speakerBtn.classList.toggle('cursor-not-allowed', disabled);
        }
    }

    function toggleMute() {
        if (!inCall) return;
        state.muted = !state.muted;
        window.dispatchEvent(new CustomEvent('dialer:mute', { detail: { muted: state.muted } }));
        updateControlButtons();
    }

    function toggleSpeaker() {
        if (!inCall) return;
        state.speaker = !state.speaker;
        window.dispatchEvent(new CustomEvent('dialer:speaker', { detail: { speaker: state.speaker } }));
        updateControlButtons();
    }

    function sanitizeDigits(value) {
        return String(value || '').replace(/\D+/g, '');
    }

    function resolveDialPrefix() {
        const cardDigits = sanitizeDigits(cardPrefix);
        if (cardDigits) {
            return cardDigits;
        }

        return sanitizeDigits(dialPrefixDefault) || '223';
    }

    function normalizeDialNumber(value) {
        const digits = sanitizeDigits(value);
        if (!digits) {
            return '';
        }

        const defaultPrefix = sanitizeDigits(dialPrefixDefault) || '223';
        const gatewayPrefix = sanitizeDigits(dialPrefixGateway);
        const gatewayCombo = gatewayPrefix ? `${gatewayPrefix}${defaultPrefix}` : '';

        if (gatewayCombo && digits.startsWith(gatewayCombo)) {
            return digits;
        }

        const enforcedPrefix = resolveDialPrefix();
        if (digits.startsWith(enforcedPrefix)) {
            return digits;
        }

        if (gatewayCombo && enforcedPrefix === gatewayCombo && digits.startsWith(defaultPrefix)) {
            return `${gatewayPrefix}${digits}`;
        }

        return `${enforcedPrefix}${digits.replace(/^0+/, '')}`;
    }

    function setDialingPreview(value) {
        const preview = document.getElementById('dialingPreview');
        if (preview) {
            preview.textContent = value ? `Dialing: ${value}` : '';
        }
    }

    async function ensureMicrophoneAccess() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            return true;
        }

        if (micStream) {
            return true;
        }

        try {
            micStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            return true;
        } catch (error) {
            return false;
        }
    }

    function stopMicrophoneAccess() {
        if (!micStream) {
            return;
        }

        micStream.getTracks().forEach((track) => track.stop());
        micStream = null;
    }

    function formatTime(sec) {
        const m = String(Math.floor(sec / 60)).padStart(2, '0');
        const s = String(sec % 60).padStart(2, '0');
        return `${m}:${s}`;
    }

    function startTimer() {
        const timerEl = document.getElementById('callTimer');
        elapsedSeconds = 0;
        timerEl.textContent = '00:00';
        timerInterval = setInterval(() => {
            elapsedSeconds++;
            timerEl.textContent = formatTime(elapsedSeconds);
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = null;
    }

    function clearRinging() {
        if (ringingTimeout) clearTimeout(ringingTimeout);
        ringingTimeout = null;
    }

    async function toggleCall() {
        if (!inCall) {
            await startCallRequest();
        } else {
            await endCallRequest();
        }
    }

    async function startCallRequest() {
        const number = document.getElementById('dialedNumber').value.trim();
        const cardUuid = document.getElementById('cardUuid').value;
        const statusEl = document.getElementById('statusMessage');
        const callBtn = document.getElementById('callBtn');

        const normalizedNumber = normalizeDialNumber(number);

        if (!normalizedNumber) {
            statusEl.textContent = 'Please enter a number to call.';
            return;
        }

        setDialingPreview(normalizedNumber);
        statusEl.textContent = 'Checking microphone...';

        const micOk = await ensureMicrophoneAccess();
        if (!micOk) {
            statusEl.textContent = 'Microphone access is required to place a call.';
            setDialingPreview('');
            return;
        }

        statusEl.textContent = 'Starting call...';
        setKeypadDisabled(true);

        try {
            const res = await fetch(`/c/${cardUuid}/start-call`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ dialed_number: normalizedNumber })
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Unable to start call.');
            }

            document.getElementById('sessionUuid').value = data.session_uuid;
            inCall = true;
            callBtn.textContent = 'End';
            callBtn.classList.remove('bg-emerald-500','hover:bg-emerald-400');
            callBtn.classList.add('bg-red-500','hover:bg-red-400');
            updateControlButtons();
            statusEl.textContent = 'Ringing...';
            clearRinging();
            ringingTimeout = setTimeout(() => {
                if (inCall) {
                    statusEl.textContent = 'Call in progress...';
                }
            }, 2000);
            startTimer();
        } catch (error) {
            statusEl.textContent = error.message || 'Unable to start call.';
            setKeypadDisabled(false);
            stopMicrophoneAccess();
        }
    }

    async function endCallRequest() {
        const sessionUuid = document.getElementById('sessionUuid').value;
        const cardUuid = document.getElementById('cardUuid').value;
        const statusEl = document.getElementById('statusMessage');
        const callBtn = document.getElementById('callBtn');

        if (!sessionUuid) {
            statusEl.textContent = 'No active call to end.';
            return;
        }

        statusEl.textContent = 'Ending call...';

        try {
            const res = await fetch(`/c/${cardUuid}/end-call`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    session_uuid: sessionUuid,
                    duration_seconds: elapsedSeconds
                })
            });

            const data = await res.json();

            stopTimer();
            clearRinging();
            inCall = false;
            setKeypadDisabled(false);
            callBtn.textContent = 'Call';
            callBtn.classList.remove('bg-red-500','hover:bg-red-400');
            callBtn.classList.add('bg-emerald-500','hover:bg-emerald-400');
            updateControlButtons();
            setDialingPreview('');
            stopMicrophoneAccess();

            if (res.ok && data.success) {
                document.getElementById('remainingMinutes').textContent = data.remaining_min;
                if (data.card_status === 'expired' || data.remaining_min <= 0) {
                    statusEl.textContent = 'Card expired. No more calls allowed.';
                } else {
                    statusEl.textContent = 'Call ended.';
                }
            } else {
                throw new Error(data.message || 'Error ending call.');
            }
        } catch (error) {
            statusEl.textContent = error.message || 'Error ending call.';
        }
    }

    updateControlButtons();
</script>
@endsection
