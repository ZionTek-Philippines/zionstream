# Remote Development Workflow

Work on ZionStream from a tablet (or any device) while your Mac stays home running the dev servers. Uses Tailscale for secure VPN tunneling and Claude Code CLI over SSH.

---

## Architecture

```
Tablet (anywhere)
    │
    │  Tailscale VPN (encrypted, no port forwarding needed)
    │
    ▼
Mac (home, always on)
    ├── SSH server (port 22)
    ├── php artisan serve  (or Valet/Herd)
    ├── php artisan reverb:start
    ├── php artisan queue:work
    └── npm run dev
```

---

## Mac Setup (do once)

### 1. Install Tailscale on Mac
```bash
brew install tailscale
sudo tailscaled &
sudo tailscale up
```
Or download from https://tailscale.com/download — sign in with your account.

### 2. Enable SSH on Mac
System Settings → General → Sharing → **Remote Login** → turn ON.

Allow access for your user account.

### 3. Note your Tailscale IP
```bash
tailscale ip -4
# e.g. 100.x.x.x
```

### 4. Keep Mac awake
System Settings → Battery → **Prevent automatic sleeping when display is off** → ON.

Or use `caffeinate -i` in a terminal to keep it awake while you work.

### 5. Install tmux (session persistence)
```bash
brew install tmux
```
tmux keeps your terminal sessions alive even if SSH disconnects.

---

## Tablet Setup (do once)

### iOS
- **Tailscale** app from the App Store — sign in with the same account as Mac
- **SSH client**: [Blink Shell](https://blink.sh) (best) or [Termius](https://termius.com)

### Android
- **Tailscale** app from Google Play
- **SSH client**: Termius or JuiceSSH

---

## Connecting from Tablet

```bash
# In your SSH client
ssh your-mac-username@100.x.x.x
# e.g. ssh regideon@100.64.0.1

# Navigate to project
cd /Users/regideon/_AppsWeb/_ZionTekLabsAI/zionstream
```

---

## Starting a Remote Session

### Option A — tmux (recommended, survives disconnection)

On Mac, before you leave:
```bash
tmux new-session -s zionstream
# Start your dev servers inside tmux
composer run dev
# Detach: Ctrl+B then D
```

From tablet, reconnect to the session:
```bash
ssh regideon@100.x.x.x
tmux attach -t zionstream
```

### Option B — Start servers fresh from tablet
```bash
ssh regideon@100.x.x.x
cd /Users/regideon/_AppsWeb/_ZionTekLabsAI/zionstream
composer run dev
```

---

## Using Claude Code Remotely

Once SSH'd into your Mac:

```bash
cd /Users/regideon/_AppsWeb/_ZionTekLabsAI/zionstream
claude
```

This opens Claude Code CLI with full access to the codebase. Give instructions exactly as you would in the desktop app — Claude reads `CLAUDE.md` automatically so it has full project context.

### Tips for tablet Claude sessions
- Keep instructions focused on one feature at a time
- Say "wait for my approval before running migrations"
- Reference docs: "see docs/02-project-overview.md for context"
- Claude always reads CLAUDE.md on startup — project context is always loaded

---

## Accessing the App from Tablet

Once connected via Tailscale, you can open the app in the tablet browser:

```
http://100.x.x.x:8000          # Laravel app
http://100.x.x.x:8000/zpanel   # Filament admin panel
http://100.x.x.x:5173          # Vite dev server
```

Replace `100.x.x.x` with your Mac's Tailscale IP.

> Tailscale handles all routing — no router port forwarding, no public IP needed.

---

## Quick Reference

| Task | Command |
|---|---|
| Start all servers | `composer run dev` |
| Start Reverb only | `php artisan reverb:start` |
| Start queue only | `php artisan queue:work` |
| Run migrations | `php artisan migrate` |
| Run tests | `php artisan test --compact` |
| Format PHP | `vendor/bin/pint --dirty --format agent` |
| Build assets | `npm run build` |
| Open Claude | `claude` |

---

## Troubleshooting

**SSH connection refused**
- Check Mac's Remote Login is enabled (System Settings → Sharing)
- Verify Tailscale is running on both devices: `tailscale status`

**tmux session lost**
- Sessions survive SSH disconnect but not Mac reboot
- After reboot: start a new tmux session

**App not accessible from tablet browser**
- Confirm `php artisan serve` is running with `--host=0.0.0.0`:
  ```bash
  php artisan serve --host=0.0.0.0 --port=8000
  ```
- Or use Laravel Herd/Valet which binds to all interfaces by default
