import AgoraRTC from 'agora-rtc-sdk-ng';

const APP_ID = import.meta.env.VITE_AGORA_APP_ID;
const cfg    = window.AgoraBroadcastConfig ?? {};

const FILTERS = {
    off: {
        label:  'Off',
        css:    'none',
        beauty: null,
    },
    vibrant: {
        label:  'Vibrant',
        css:    'saturate(1.9) contrast(1.1)',
        beauty: { smoothnessLevel: 10, sharpnessLevel: 70, lighteningLevel: 20, lighteningContrastLevel: 1, rednessLevel: 0 },
    },
    warm: {
        label:  'Warm',
        css:    'saturate(1.5) sepia(0.2)',
        beauty: { smoothnessLevel: 20, sharpnessLevel: 50, lighteningLevel: 30, lighteningContrastLevel: 1, rednessLevel: 20 },
    },
    glamour: {
        label:  'Glamour',
        css:    'saturate(1.6) brightness(1.1)',
        beauty: { smoothnessLevel: 30, sharpnessLevel: 80, lighteningLevel: 40, lighteningContrastLevel: 2, rednessLevel: 10 },
    },
    cool: {
        label:  'Cool',
        css:    'saturate(1.4) hue-rotate(18deg)',
        beauty: { smoothnessLevel: 10, sharpnessLevel: 60, lighteningLevel: 15, lighteningContrastLevel: 0, rednessLevel: 0 },
    },
};

const FILTER_KEYS = Object.keys(FILTERS);

let client, audioTrack, rawVideoTrack, customVideoTrack, feedVideo;
let filterIndex  = 0;
let activeFilter = 'none';
let drawActive   = false;

const goLiveBtn     = document.getElementById('go-live-btn');
const endBtn        = document.getElementById('end-btn');
const muteBtn       = document.getElementById('mute-btn');
const filterWrapper = document.getElementById('filter-wrapper');
const filterBtn     = document.getElementById('filter-btn');
const filterLabel   = document.getElementById('filter-label');
const liveBadge     = document.getElementById('live-badge');
const statusMsg     = document.getElementById('status-msg');

goLiveBtn?.addEventListener('click', startBroadcast);
endBtn?.addEventListener('click', endBroadcast);
muteBtn?.addEventListener('click', toggleMute);
filterBtn?.addEventListener('click', cycleFilter);

function cycleFilter() {
    filterIndex  = (filterIndex + 1) % FILTER_KEYS.length;
    const key    = FILTER_KEYS[filterIndex];
    const preset = FILTERS[key];
    activeFilter = preset.css;

    // Local preview: CSS on the div
    document.getElementById('local-video').style.filter = activeFilter;

    // Agora native beauty layer
    if (rawVideoTrack) {
        if (preset.beauty) {
            rawVideoTrack.setBeautyEffect(true, preset.beauty);
        } else {
            rawVideoTrack.setBeautyEffect(false);
        }
    }

    if (filterLabel) filterLabel.textContent = preset.label;
    const isOn = key !== 'off';
    filterBtn?.classList.toggle('text-primary', isOn);
    filterBtn?.classList.toggle('bg-primary/20', isOn);
    filterBtn?.classList.toggle('text-white/60', !isOn);
    filterBtn?.classList.toggle('bg-white/10', !isOn);
}


async function startBroadcast() {
    setStatus('Connecting…');

    client = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
    await client.setClientRole('host');

    let token;
    try {
        const res = await fetch(
            `/agora/token?channel=${encodeURIComponent(cfg.channelName)}&uid=${cfg.uid}&role=host`
        );
        ({ token } = await res.json());
    } catch {
        return setStatus('Token error — check .env credentials.');
    }

    try {
        await client.join(APP_ID, cfg.channelName, token, cfg.uid);
    } catch (e) {
        return setStatus('Failed to join: ' + e.message);
    }

    try {
        [audioTrack, rawVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
    } catch (e) {
        return setStatus('Camera/mic error: ' + e.message);
    }

    // ── Canvas processing pipeline ─────────────────────────────────────────
    // Drive the canvas from a hidden video element reading the raw camera track
    feedVideo = document.createElement('video');
    feedVideo.autoplay    = true;
    feedVideo.muted       = true;
    feedVideo.playsInline = true;
    feedVideo.srcObject   = new MediaStream([rawVideoTrack.getMediaStreamTrack()]);

    await new Promise((resolve) => {
        feedVideo.onloadedmetadata = () => feedVideo.play().then(resolve);
    });

    const canvas  = document.createElement('canvas');
    canvas.width  = feedVideo.videoWidth  || 1280;
    canvas.height = feedVideo.videoHeight || 720;
    const ctx = canvas.getContext('2d');

    drawActive = true;
    (function drawLoop() {
        if (!drawActive) return;
        if (feedVideo.videoWidth > 0 && canvas.width !== feedVideo.videoWidth) {
            canvas.width  = feedVideo.videoWidth;
            canvas.height = feedVideo.videoHeight;
        }
        ctx.filter = activeFilter;
        ctx.drawImage(feedVideo, 0, 0, canvas.width, canvas.height);
        requestAnimationFrame(drawLoop);
    })();

    // Build a custom Agora track from the canvas stream — this is what viewers receive
    const processedTrack = canvas.captureStream(30).getVideoTracks()[0];
    customVideoTrack = AgoraRTC.createCustomVideoTrack({
        mediaStreamTrack: processedTrack,
        frameRate: 30,
    });

    // Local preview: raw camera via Agora SDK + CSS filter on the container div
    rawVideoTrack.play('local-video');
    document.getElementById('local-video').style.filter = activeFilter;

    await client.publish([audioTrack, customVideoTrack]);
    // ── End pipeline ───────────────────────────────────────────────────────

    goLiveBtn.classList.add('hidden');
    endBtn.classList.remove('hidden');
    muteBtn.classList.remove('hidden');
    filterWrapper?.classList.remove('hidden');
    liveBadge?.classList.remove('hidden');
    setStatus('You are LIVE');

    await fetch(`/app/stream/${cfg.streamId}/go-live`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).catch(() => {});
}

async function endBroadcast() {
    drawActive = false;
    rawVideoTrack?.close();
    customVideoTrack?.close();
    audioTrack?.close();
    if (feedVideo) { feedVideo.srcObject = null; feedVideo.remove(); }
    await client?.leave();

    const localVideo = document.getElementById('local-video');
    localVideo.innerHTML = '';
    localVideo.style.filter = 'none';

    endBtn.classList.add('hidden');
    muteBtn.classList.add('hidden');
    filterWrapper?.classList.add('hidden');
    goLiveBtn.classList.remove('hidden');
    liveBadge?.classList.add('hidden');

    filterIndex  = 0;
    activeFilter = 'none';
    if (filterLabel) filterLabel.textContent = 'Off';

    setStatus('Stream ended.');

    await fetch(`/app/stream/${cfg.streamId}/end`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).catch(() => {});
}

function toggleMute() {
    if (!audioTrack) return;
    const muted = audioTrack.muted;
    audioTrack.setMuted(!muted);
    muteBtn.querySelector('.material-symbols-outlined').textContent = muted ? 'mic' : 'mic_off';
    muteBtn.classList.toggle('bg-error/20', !muted);
}

function setStatus(msg) {
    if (statusMsg) statusMsg.textContent = msg;
}
