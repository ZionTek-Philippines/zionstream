import AgoraRTC from 'agora-rtc-sdk-ng';

const APP_ID  = import.meta.env.VITE_AGORA_APP_ID;
const config  = window.AgoraStreamConfig ?? {};

if (!APP_ID || !config.channelName) {
    console.warn('Agora: missing APP_ID or channelName — viewer not started.');
} else {
    startViewer(config.channelName);
}

async function startViewer(channelName) {
    const client = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
    await client.setClientRole('audience');

    let token;
    try {
        const res = await fetch(`/agora/token?channel=${encodeURIComponent(channelName)}&uid=0`);
        ({ token } = await res.json());
    } catch (e) {
        showStatus('Could not reach token server.');
        return;
    }

    try {
        await client.join(APP_ID, channelName, token, null);
    } catch (e) {
        showStatus('Failed to join stream.');
        return;
    }

    client.on('user-published', async (user, mediaType) => {
        await client.subscribe(user, mediaType);

        if (mediaType === 'video') {
            hideLoading();
            user.videoTrack.play(document.getElementById('agora-video'));
        }
        if (mediaType === 'audio') {
            user.audioTrack.play();
        }
    });

    client.on('user-unpublished', (_user, mediaType) => {
        if (mediaType === 'video') {
            showStatus('Stream paused…');
        }
    });

    client.on('connection-state-change', (state) => {
        if (state === 'DISCONNECTED') showStatus('Disconnected from stream.');
    });
}

function hideLoading() {
    document.getElementById('agora-loading')?.remove();
}

function showStatus(message) {
    const el = document.getElementById('agora-loading');
    if (el) {
        el.innerHTML = `<p class="text-white/60 text-sm text-center px-8">${message}</p>`;
    }
}
