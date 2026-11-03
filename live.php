<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Live Ranking - Sincro</title>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=JetBrains+Mono:wght@500&display=swap' rel='stylesheet'>
    <style>
        :root {
            --bg-color: #f4f6f9;
            --card-bg: rgba(255, 255, 255, 0.7);
            --text-color: #1a1a1a;
            --accent-purple: #6b46c1;
            --accent-gradient: linear-gradient(135deg, #6b46c1 0%, #d53f8c 100%);
            --glass-border: rgba(255, 255, 255, 0.4);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #0f172a;
                --card-bg: rgba(30, 41, 59, 0.7);
                --text-color: #f1f5f9;
                --glass-border: rgba(255, 255, 255, 0.1);
            }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            padding: 20px;
            overflow-x: hidden;
        }

        .background-blob {
            position: fixed;
            top: -10%;
            right: -10%;
            width: 40vw;
            height: 40vw;
            background: var(--accent-gradient);
            filter: blur(100px);
            opacity: 0.15;
            z-index: -1;
            border-radius: 50%;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }

        h1 { font-weight: 800; font-size: 2.5rem; margin-bottom: 10px; background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .last-update { font-size: 0.9rem; opacity: 0.7; }

        .phase-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .phase-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            border-left: 5px solid var(--accent-purple);
            padding-left: 15px;
        }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.6; }
        td { padding: 16px 12px; border-bottom: 1px solid var(--glass-border); }
        
        .pos-cell { width: 50px; font-weight: 800; color: var(--accent-purple); font-size: 1.2rem; }
        .score-cell { 
            font-family: 'JetBrains Mono', monospace; 
            font-weight: 600; 
            text-align: right; 
            color: var(--accent-purple);
            font-size: 1.2rem;
        }
        .club-cell { font-weight: 600; }
        .participants { font-size: 0.85rem; opacity: 0.8; margin-top: 4px; }

        tr:last-child td { border-bottom: none; }
        tr:nth-child(1) .pos-cell { color: #f59e0b; } /* Gold */
        tr:nth-child(2) .pos-cell { color: #94a3b8; } /* Silver */
        tr:nth-child(3) .pos-cell { color: #b45309; } /* Bronze */

        @media (max-width: 600px) {
            h1 { font-size: 1.8rem; }
            .phase-card { padding: 15px; }
            td, th { padding: 10px 5px; }
            .score-cell { font-size: 1rem; }
        }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeIn 0.5s ease forwards; }
    </style>
</head>
<body>
    <div class='background-blob'></div>
    <div class='container'>
        <header>
            <h1 id='comp-name'>Live Ranking</h1>
            <div class='last-update' id='update-time'>Actualizando...</div>
        </header>

        <main id='ranking-content'>
            <!-- Content loaded via JS -->
        </main>
    </div>

    <script>
        async function updateRanking() {
            try {
                const response = await fetch('api_live_ranking.php' + window.location.search);
                const data = await response.json();

                if (data.error) {
                    document.getElementById('ranking-content').innerHTML = `<div class='phase-card' style='text-align:center'>${data.error}</div>`;
                    return;
                }

                document.getElementById('comp-name').innerText = data.competicion;
                document.getElementById('update-time').innerText = 'Última actualización: ' + new Date().toLocaleTimeString();

                let html = '';
                data.fases.forEach((fase, idx) => {
                    html += `
                        <div class='phase-card animate-in' style='animation-delay: ${idx * 0.1}s'>
                            <div class='phase-title'>${fase.nombre}</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Pos</th>
                                        <th>Club / Nadadoras</th>
                                        <th style='text-align:right'>Puntos</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    
                    fase.rutinas.forEach(r => {
                        html += `
                            <tr>
                                <td class='pos-cell'>${r.pos}º</td>
                                <td>
                                    <div class='club-cell'>${r.club}</div>
                                    <div class='participants'>${r.participantes}</div>
                                </td>
                                <td class='score-cell'>${r.nota.toFixed(4)}</td>
                            </tr>`;
                    });

                    html += `</tbody></table></div>`;
                });

                document.getElementById('ranking-content').innerHTML = html;
            } catch (err) {
                console.error('Error al actualizar ranking:', err);
                document.getElementById('update-time').innerText = 'Error al actualizar. Reintentando...';
            }
        }

        // Initial load
        updateRanking();

        // Update every 60 seconds
        setInterval(updateRanking, 60000);
    </script>
</body>
</html>
