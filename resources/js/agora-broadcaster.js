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
        css:   'saturate(1.2) contrast(1.1) brightness(1.1) blur(0.3px)',
    },
    professional: {
        label: 'Pro',
        css:   'saturate(1.15) contrast(1.2) brightness(1.02)',
    },
};

const FILTER_KEYS = Object.keys(FILTERS);

let client, audioTrack, rawVideoTrack, customVideoTrack, feedVideo;
let filterIndex  = 0;
let activeFilter = 'saturate(1) contrast(1) brightness(1)';
let drawActive   = false;

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

function cycleFilter() {
    filterIndex  = (filterIndex + 1) % FILTER_KEYS.length;
    const key    = FILTER_KEYS[filterIndex];
    const preset = FILTERS[key];
    activeFilter = preset.css;

    console.log('[filter] cycled to:', key, '→', activeFilter);

    document.getElementById('local-video').style.filter = activeFilter;

    if (filterLabel) filterLabel.textContent = preset.label;
    const isOn = key !== 'normal';
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

    filterIndex  = 0;
    activeFilter = 'saturate(1) contrast(1) brightness(1)';
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
