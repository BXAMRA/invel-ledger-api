<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invel LEDGER - System Control</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --bg-color: #050505;
            --surface-color: rgba(15, 15, 20, 0.7);
            --border-color: rgba(255, 255, 255, 0.1);
            --text-main: #f0f0f0;
            --text-muted: #888888;
            --accent-glow: 0 0 40px rgba(0, 238, 255, 0.3);
            --primary: #00eeff;
            --success: #00ff66;
            --danger: #ff0055;
            --font-family: 'Space Grotesk', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: var(--font-family);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Gradients */
        .bg-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
            opacity: 0.3;
            animation: pulse-glow 8s infinite alternate;
        }
        .bg-glow.primary {
            background: radial-gradient(circle, var(--primary), transparent 60%);
            top: -200px;
            left: -200px;
        }
        .bg-glow.secondary {
            background: radial-gradient(circle, #8a2be2, transparent 60%);
            bottom: -200px;
            right: -200px;
            animation-delay: -4s;
        }

        @keyframes pulse-glow {
            0% { transform: scale(1); opacity: 0.15; }
            100% { transform: scale(1.2); opacity: 0.4; }
        }

        /* Glassmorphism Container */
        .glass-panel {
            background: var(--surface-color);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }

        /* Scanline Overlay */
        .glass-panel::before {
            content: " ";
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(
                to bottom,
                rgba(255,255,255,0),
                rgba(255,255,255,0) 50%,
                rgba(0,0,0,0.1) 50%,
                rgba(0,0,0,0.1)
            );
            background-size: 100% 4px;
            border-radius: 16px;
            pointer-events: none;
            opacity: 0.3;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 5px;
            background: linear-gradient(90deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Status Indicator */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-nominal .status-dot {
            background-color: var(--success);
            box-shadow: 0 0 10px var(--success);
            animation: pulse-dot 2s infinite;
        }

        .status-pending .status-dot {
            background-color: var(--danger);
            box-shadow: 0 0 10px var(--danger);
            animation: pulse-dot-fast 1s infinite;
        }

        @keyframes pulse-dot {
            0% { box-shadow: 0 0 0 0 rgba(0, 255, 102, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 255, 102, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 255, 102, 0); }
        }

        @keyframes pulse-dot-fast {
            0% { box-shadow: 0 0 0 0 rgba(255, 0, 85, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 0, 85, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 0, 85, 0); }
        }

        /* Typography & Layout */
        .terminal-text {
            font-family: 'Space Grotesk', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
            color: #d0d0d0;
        }

        /* Buttons */
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border: 1px solid var(--primary);
            background: rgba(0, 238, 255, 0.1);
            color: var(--primary);
            font-family: var(--font-family);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .btn:hover:not(:disabled) {
            background: var(--primary);
            color: var(--bg-color);
            box-shadow: var(--accent-glow);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            border-color: var(--border-color);
            color: var(--text-muted);
            background: transparent;
        }

        /* File Upload */
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 120px;
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: rgba(0,0,0,0.2);
            cursor: pointer;
            margin-bottom: 15px;
        }

        .file-upload-wrapper:hover {
            border-color: var(--primary);
            background: rgba(0, 238, 255, 0.05);
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 10px;
        }

        .icon {
            width: 24px;
            height: 24px;
            stroke: var(--primary);
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .hidden {
            display: none !important;
        }

        /* Console Output */
        .console {
            background: #000;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px;
            font-size: 0.75rem;
            font-family: monospace;
            color: var(--success);
            height: 100px;
            overflow-y: auto;
            margin-bottom: 15px;
            text-align: left;
        }
        .console p { margin-bottom: 4px; }
        .console .error { color: var(--danger); }
        .console .info { color: #aaa; }


        /* Footer Links */
        .footer-links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 0.8rem;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: var(--primary);
            text-shadow: var(--accent-glow);
        }

        .footer-links span {
            color: var(--border-color);
        }

        .logo-container {
            width: 160px;
            height: 160px;
            margin: 0 auto 15px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .app-logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.3);
            display: block;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="bg-glow primary"></div>
    <div class="bg-glow secondary"></div>

    <div class="glass-panel">
        <div class="header">
          <div class="logo-container">
            <img src="{{ asset('app-logo.png') }}" alt="Invel Ledger Logo" class="app-logo-img">
            </div>
            <h1 class="logo">Business Suite for Developers</h1>
        </div>

        @if($migrationsRun)
            <!-- Nominal State -->
            <div style="text-align: center;">
                <div class="status-badge status-nominal">
                    <div class="status-dot"></div>
                    System Nominal
                </div>
                <div class="terminal-text" style="color: var(--success);">
                    ALL SYSTEMS UP AND RUNNING.
                </div>
                <div class="terminal-text" style="font-size: 0.8rem; color: var(--text-muted);">
                    The core API is serving requests seamlessly.
                </div>
            </div>
        @else
            <!-- Initialization State -->
            <div id="init-view" style="text-align: center;">
                <div class="status-badge status-pending">
                    <div class="status-dot"></div>
                    Initialization Required
                </div>

                <div class="terminal-text">
                    Database schema not detected. Core architecture must be compiled before operations can begin.
                </div>

                <div class="console hidden" id="console"></div>

                <div id="step-1">
                    <button class="btn" id="btn-migrate" onclick="runMigrations()">
                        <span id="btn-migrate-text">Initialize Architecture</span>
                    </button>
                </div>

                <div id="step-2" class="hidden">
                    <div class="terminal-text" style="font-size: 0.8rem;">
                        Core initialized. You may now upload a configuration backup (.json) to restore data, or proceed to standard setup.
                    </div>

                    <div class="file-upload-wrapper">
                        <input type="file" id="backup-file" accept=".json,application/json,text/plain" onchange="uploadBackup()">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <div class="file-upload-text">Drop backup JSON here or click to browse</div>
                    </div>

                    <button class="btn" onclick="location.reload()" style="background: transparent; border-color: var(--border-color); color: var(--text-main);">
                        Skip Restoration & Reboot
                    </button>
                </div>
            </div>
        @endif

        <div class="footer-links">
            <a href="https://bxamra.dev" target="_blank" rel="noopener noreferrer">bxamra.dev</a>
            <span>•</span>
            <a href="https://github.com/bxamra/invel-ledger" target="_blank" rel="noopener noreferrer">GitHub Repository</a>
            <span>•</span>
            <a href="https://github.com/bxamra/invel-ledger/releases" target="_blank" rel="noopener noreferrer">Download Desktop Client</a>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const consoleEl = document.getElementById('console');

        function log(msg, type = 'info') {
            consoleEl.classList.remove('hidden');
            const p = document.createElement('p');
            p.className = type;
            p.innerText = `> ${msg}`;
            consoleEl.appendChild(p);
            consoleEl.scrollTop = consoleEl.scrollHeight;
        }

        async function runMigrations() {
            const btn = document.getElementById('btn-migrate');
            const btnText = document.getElementById('btn-migrate-text');

            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Compiling Schema...';
            log('Initiating database migrations...', 'info');

            try {
                const res = await fetch('/setup/migrate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();

                if (res.ok) {
                    log('Migrations completed successfully.', 'success');
                    document.getElementById('step-1').classList.add('hidden');
                    document.getElementById('step-2').classList.remove('hidden');
                } else {
                    log(`Error: ${data.message || 'Migration failed'}`, 'error');
                    btn.disabled = false;
                    btnText.innerText = 'Retry Initialization';
                }
            } catch (err) {
                log('Network error occurred.', 'error');
                btn.disabled = false;
                btnText.innerText = 'Retry Initialization';
            }
        }

        async function uploadBackup() {
            const input = document.getElementById('backup-file');
            if (!input.files || input.files.length === 0) return;

            const file = input.files[0];
            const formData = new FormData();
            formData.append('file', file);

            log(`Uploading payload: ${file.name}...`, 'info');

            try {
                const res = await fetch('/setup/import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();

                if (res.ok) {
                    log('Data restoration complete. Rebooting systems...', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    log(`Restoration failed: ${data.message || 'Invalid format'}`, 'error');
                }
            } catch (err) {
                log('Network error during upload.', 'error');
            }

            input.value = '';
        }
    </script>
</body>
</html>
