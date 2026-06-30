import AgoraRTC from 'agora-rtc-sdk-ng';

const APP_ID = import.meta.env.VITE_AGORA_APP_ID;
const cfg    = window.AgoraBroadcastConfig ?? {};

const FILTERS = {
    normal: {
        label: 'Normal',
        css:   'saturate(1) contrast(1) brightness(1)',
    },
    vibrant: {
        label: 'Vibrant',
        css:   'saturate(1.35) contrast(1.15) brightness(1.05)',
    },
    beauty: {
        label: 'Beauty',
        css:   'saturate(1.2) contrast(1.12) brightness(1.18) blur(0.3px)',
    },
    professional: {
        label: 'Pro',
        css:   'saturate(1.15) contrast(1.2) brightness(1.02)',
    },
};

const FILTER_KEYS = Object.keys(FILTERS);

let client, audioTrack, rawVideoTrack, customVideoTrack, feedVideo;
let filterIndex       = 0;
let activeFilterKey   = 'normal';
let activeFilter      = FILTERS.normal.css;
let activeGlowOpacity = 0;
let drawActive        = false;
let wakeLockSentinel  = null;
let brightnessOverlay = null;

console.log('[broadcaster] ✅ JS loaded — filters:', Object.keys(FILTERS));

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

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && activeFilterKey === 'beauty') {
        void requestScreenWakeLock();
    }
});

function ensureBrightnessOverlay() {
    if (brightnessOverlay) {
        return brightnessOverlay;
    }

    brightnessOverlay = document.createElement('div');
    brightnessOverlay.id = 'beauty-brightness-overlay';
    brightnessOverlay.style.position = 'fixed';
    brightnessOverlay.style.inset = '0';
    brightnessOverlay.style.pointerEvents = 'none';
    brightnessOverlay.style.zIndex = '5';
    brightnessOverlay.style.background = '#ffffff';
    brightnessOverlay.style.opacity = '0';
    brightnessOverlay.style.transition = 'opacity 180ms ease';
    document.body.appendChild(brightnessOverlay);

    return brightnessOverlay;
}

async function requestScreenWakeLock() {
    if (!('wakeLock' in navigator) || wakeLockSentinel) {
        return;
    }

    try {
        wakeLockSentinel = await navigator.wakeLock.request('screen');
        wakeLockSentinel.addEventListener('release', () => {
            wakeLockSentinel = null;
        });
    } catch (error) {
        console.warn('[beauty] wake lock unavailable:', error);
    }
}

async function releaseScreenWakeLock() {
    if (!wakeLockSentinel) {
        return;
    }

    try {
        await wakeLockSentinel.release();
    } catch (error) {
        console.warn('[beauty] wake lock release failed:', error);
    } finally {
        wakeLockSentinel = null;
    }
}

function applyBeautyModeEffects(filterKey) {
    const overlay = ensureBrightnessOverlay();
    const isBeautyMode = filterKey === 'beauty';

    activeGlowOpacity = isBeautyMode ? 0.08 : 0;
    overlay.style.opacity = isBeautyMode ? '0.08' : '0';

    if (isBeautyMode) {
        void requestScreenWakeLock();
        return;
    }

    void releaseScreenWakeLock();
}

function cycleFilter() {
    filterIndex       = (filterIndex + 1) % FILTER_KEYS.length;
    activeFilterKey   = FILTER_KEYS[filterIndex];
    const preset      = FILTERS[activeFilterKey];
    activeFilter      = preset.css;

    console.log('[filter] cycled to:', activeFilterKey, '→', activeFilter);

    document.getElementById('local-video').style.filter = activeFilter;
    applyBeautyModeEffects(activeFilterKey);

    if (filterLabel) filterLabel.textContent = preset.label;
    const isOn = activeFilterKey !== 'normal';
    filterBtn?.classList.toggle('text-primary', isOn);
    filterBtn?.classList.toggle('bg-primary/20', isOn);
    filterBtn?.classList.toggle('text-white/60', !isOn);
    filterBtn?.classList.toggle('bg-white/10', !isOn);
}

async function startBroadcast() {
    setStatus('Connecting…');
    console.log('[broadcaster] startBroadcast called');

    client = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
    await client.setClientRole('host');

    let token;
    try {
        const res = await fetch(
            `/agora/token?channel=${encodeURIComponent(cfg.channelName)}&uid=${cfg.uid}&role=host`
        );
        ({ token } = await res.json());
        console.log('[broadcaster] token received');
    } catch {
        return setStatus('Token error — check .env credentials.');
    }

    try {
        await client.join(APP_ID, cfg.channelName, token, cfg.uid);
        console.log('[broadcaster] joined channel:', cfg.channelName);
    } catch (e) {
        return setStatus('Failed to join: ' + e.message);
    }

    try {
        [audioTrack, rawVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
        console.log('[broadcaster] camera + mic tracks created');
    } catch (e) {
        return setStatus('Camera/mic error: ' + e.message);
    }

    // ── Canvas pipeline — applies CSS filter to what viewers receive ──────────
    feedVideo             = document.createElement('video');
    feedVideo.autoplay    = true;
    feedVideo.muted       = true;
    feedVideo.playsInline = true;
    feedVideo.srcObject   = new MediaStream([rawVideoTrack.getMediaStreamTrack()]);

    await new Promise((resolve) => {
        feedVideo.onloadedmetadata = () => feedVideo.play().then(resolve);
    });
    console.log('[canvas] feedVideo playing:', feedVideo.videoWidth, 'x', feedVideo.videoHeight);

    const canvas  = document.createElement('canvas');
    canvas.width  = feedVideo.videoWidth  || 1280;
    canvas.height = feedVideo.videoHeight || 720;
    const ctx     = canvas.getContext('2d');
    console.log('[canvas] created:', canvas.width, 'x', canvas.height);

    drawActive = true;
    let frameCount = 0;
    (function drawLoop() {
        if (!drawActive) return;
        if (feedVideo.videoWidth > 0 && canvas.width !== feedVideo.videoWidth) {
            canvas.width  = feedVideo.videoWidth;
            canvas.height = feedVideo.videoHeight;
            console.log('[canvas] resized to:', canvas.width, 'x', canvas.height);
        }
        ctx.filter = activeFilter;
        ctx.drawImage(feedVideo, 0, 0, canvas.width, canvas.height);
        if (activeGlowOpacity > 0) {
            ctx.filter = 'none';
            ctx.fillStyle = `rgba(255, 255, 255, ${activeGlowOpacity})`;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
        frameCount++;
        if (frameCount % 60 === 0) {
            console.log('[canvas] frame', frameCount, '— filter:', ctx.filter);
        }
        requestAnimationFrame(drawLoop);
    })();

    const processedTrack = canvas.captureStream(30).getVideoTracks()[0];
    customVideoTrack = AgoraRTC.createCustomVideoTrack({
        mediaStreamTrack: processedTrack,
        frameRate: 30,
        bitrateMin: 600,
        bitrateMax: 2000,
    });
    console.log('[canvas] custom track readyState:', processedTrack.readyState);

    rawVideoTrack.play('local-video');
    document.getElementById('local-video').style.filter = activeFilter;
    applyBeautyModeEffects(activeFilterKey);

    await client.publish([audioTrack, customVideoTrack]);
    console.log('[agora] published successfully');
    // ── End canvas pipeline ───────────────────────────────────────────────────

    document.getElementById('go-live-area')?.classList.add('hidden');
    document.getElementById('live-area')?.classList.remove('hidden');
    statusMsg?.classList.add('hidden');
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
    await releaseScreenWakeLock();
    rawVideoTrack?.close();
    customVideoTrack?.close();
    audioTrack?.close();
    if (feedVideo) { feedVideo.srcObject = null; feedVideo.remove(); }
    await client?.leave();

    const localVideo = document.getElementById('local-video');
    localVideo.innerHTML     = '';
    localVideo.style.filter  = 'none';

    document.getElementById('live-area')?.classList.add('hidden');
    document.getElementById('go-live-area')?.classList.remove('hidden');
    statusMsg?.classList.remove('hidden');
    endBtn.classList.add('hidden');
    muteBtn.classList.add('hidden');
    filterWrapper?.classList.add('hidden');
    liveBadge?.classList.add('hidden');

    filterIndex     = 0;
    activeFilterKey = 'normal';
    activeFilter    = FILTERS.normal.css;
    applyBeautyModeEffects(activeFilterKey);
    if (filterLabel) filterLabel.textContent = 'Normal';

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
