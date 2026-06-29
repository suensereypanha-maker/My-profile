<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COSMOS // DEPLOYMENT CONTROL PANEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;700&display=swap');
        body { font-family: 'Fira Code', monospace; background-color: #030406; color: #e5e7eb; }
        .neon-shadow { box-shadow: 0 0 15px rgba(0, 255, 102, 0.2); }
        .neon-border { border-color: rgba(0, 255, 102, 0.4); }
        .neon-text { color: #00FF66; text-shadow: 0 0 5px rgba(0, 255, 102, 0.5); }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden">
    <!-- Grid BG -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_rgba(0,255,102,0.03)_0%,_transparent_65%)] pointer-events-none"></div>
    <div class="absolute top-0 left-0 w-full h-full bg-[linear-gradient(to_right,rgba(0,255,102,0.01)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,255,102,0.01)_1px,transparent_1px)] bg-[size:40px_40px] pointer-events-none"></div>

    <div class="w-full max-w-2xl bg-nebulaDark bg-[#0a100d] border border-galaxyGreen/20 rounded-2xl p-6 relative z-10 neon-shadow">
        
        <!-- Header -->
        <div class="border-b border-galaxyGreen/15 pb-4 mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold tracking-wider text-white flex items-center gap-2">
                    <i class="fa-solid fa-satellite-dish text-galaxyGreen animate-pulse"></i> COSMOS DEPLOYMENT CENTER
                </h1>
                <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest">Local Git Sync Terminal</p>
            </div>
            <span class="text-[9px] font-mono tracking-widest text-galaxyGreen/70 px-2 py-0.5 bg-galaxyGreen/10 rounded border border-galaxyGreen/20">PORT {{ request()->server('SERVER_PORT') }}</span>
        </div>

        <!-- Token Input -->
        <div class="mb-6">
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">GitHub Personal Access Token (PAT)</label>
            <div class="relative">
                <i class="fa-solid fa-key absolute left-3 top-3.5 text-galaxyGreen/50"></i>
                <input type="password" id="github-token" placeholder="paste your github_pat_..." class="w-full bg-black/40 border border-galaxyGreen/20 focus:border-galaxyGreen rounded-xl py-3 pl-10 pr-4 text-xs font-mono text-white placeholder-gray-600 focus:outline-none transition-all">
            </div>
            <p class="text-[10px] text-gray-500 mt-2">
                * Needed to authenticate the push command. You can get one from <a href="https://github.com/settings/tokens" target="_blank" class="text-galaxyGreen hover:underline">GitHub Developer Settings</a> (requires `repo` scope).
            </p>
        </div>

        <!-- Actions -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <button onclick="runAction('export')" class="py-3 px-4 border border-galaxyGreen/30 hover:border-galaxyGreen bg-galaxyGreen/5 hover:bg-galaxyGreen/15 text-white hover:text-galaxyGreen text-xs font-bold rounded-xl transition-all flex flex-col items-center justify-center gap-2">
                <i class="fa-solid fa-file-export text-lg"></i>
                <span>1. Compile HTML</span>
            </button>
            <button onclick="runAction('commit')" class="py-3 px-4 border border-galaxyGreen/30 hover:border-galaxyGreen bg-galaxyGreen/5 hover:bg-galaxyGreen/15 text-white hover:text-galaxyGreen text-xs font-bold rounded-xl transition-all flex flex-col items-center justify-center gap-2">
                <i class="fa-solid fa-code-commit text-lg"></i>
                <span>2. Commit changes</span>
            </button>
            <button onclick="runAction('push')" class="py-3 px-4 border border-premiumGold/30 hover:border-premiumGold bg-premiumGold/5 hover:bg-premiumGold/15 text-white hover:text-premiumGold text-xs font-bold rounded-xl transition-all flex flex-col items-center justify-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                <span>3. Push to GitHub</span>
            </button>
        </div>

        <!-- Console -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Console Output</span>
                <span id="status-indicator" class="text-[10px] font-bold text-gray-500 uppercase flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-gray-600"></span> IDLE
                </span>
            </div>
            <div id="console" class="w-full h-48 bg-black/90 border border-galaxyGreen/10 rounded-xl p-4 font-mono text-[11px] overflow-y-auto text-gray-400 leading-relaxed whitespace-pre-wrap">Welcome to Cosmos Deployer. Choose an action above to begin.</div>
        </div>

    </div>

    <script>
        function log(message, isError = false) {
            const consoleDiv = document.getElementById('console');
            const colorClass = isError ? 'text-red-400' : 'text-galaxyGreen';
            consoleDiv.innerHTML += `\n<span class="${colorClass}">[cosmos]</span> ${message}`;
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        }

        async function runAction(action) {
            const token = document.getElementById('github-token').value;
            if (action === 'push' && !token) {
                alert('Please enter your GitHub Personal Access Token to push!');
                return;
            }

            const statusIndicator = document.getElementById('status-indicator');
            statusIndicator.innerHTML = '<span class="h-2 w-2 rounded-full bg-yellow-500 animate-pulse"></span> RUNNING';
            log(`Running action: ${action}...`);

            try {
                const response = await fetch('/push-panel/execute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ action, token })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    log(`Action completed successfully!`);
                    if (data.output) log(data.output);
                    statusIndicator.innerHTML = '<span class="h-2 w-2 rounded-full bg-green-500"></span> SUCCESS';
                } else {
                    log(`Action failed!`, true);
                    if (data.error) log(data.error, true);
                    statusIndicator.innerHTML = '<span class="h-2 w-2 rounded-full bg-red-500"></span> FAILED';
                }
            } catch (err) {
                log(`Network error: ${err.message}`, true);
                statusIndicator.innerHTML = '<span class="h-2 w-2 rounded-full bg-red-500"></span> ERROR';
            }
        }
    </script>
</body>
</html>
