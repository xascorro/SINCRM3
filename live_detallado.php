<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Live Detailed Ranking - Sincro</title>
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
            --min-color: #ef4444;
            --max-color: #3b82f6;
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
        }

        .container { max-width: 1200px; margin: 0 auto; }

        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }

        h1 { font-weight: 800; font-size: 2rem; margin-bottom: 5px; background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .last-update { font-size: 0.8rem; opacity: 0.7; }

        .phase-section { margin-bottom: 50px; }
        .phase-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: var(--card-bg);
            border-radius: 12px;
            display: inline-block;
        }

        .routine-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        .routine-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .routine-info { display: flex; align-items: center; gap: 15px; }
        .pos { font-weight: 800; font-size: 1.5rem; color: var(--accent-purple); min-width: 45px; }
        .club { font-weight: 700; font-size: 1.1rem; }
        .participants { font-size: 0.85rem; opacity: 0.8; }
        .total-score { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 1.6rem; color: var(--accent-purple); }

        .panels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }

        .panel-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            border: 1px solid var(--glass-border);
        }

        .panel-name { font-weight: 700; font-size: 0.9rem; margin-bottom: 10px; opacity: 0.9; }
        
        .judges-row {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .judge-note { font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            padding: 4px 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            border: 1px solid transparent;
        }

        .judge-note.min { color: var(--min-color); text-decoration: line-through; opacity: 0.5; border: none; font-weight: 400; }
        .judge-note.max { color: var(--max-color); text-decoration: line-through; opacity: 0.5; border: none; font-weight: 400; }

        .panel-footer {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            font-weight: 600;
            border-top: 1px solid var(--glass-border);
            padding-top: 8px;
        }

        .panel-media { opacity: 0.7; }
        .panel-puntos { color: var(--accent-purple); }

        @media (max-width: 600px) {
            .routine-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .total-score { align-self: flex-end; }
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeIn 0.4s ease forwards; }
    </style>
</head>
<body>
    <div class='container'>
        <header>
            <h1 id='comp-name'>Detalle de Puntuaciones</h1>
            <div class='last-update' id='update-time'>Actualizando...</div>
        </header>

        <main id='ranking-content'>
            <!-- Content loaded via JS -->
        </main>
    </div>

    <script>
        async function updateRanking() {
            try {
                const response = await fetch('api_live_detailed.php' + window.location.search);
                const data = await response.json();

                if (data.error) {
                    document.getElementById('ranking-content').innerHTML = `<div class='routine-card' style='text-align:center'>${data.error}</div>`;
                    return;
                }

                document.getElementById('comp-name').innerText = data.competicion;
                document.getElementById('update-time').innerText = 'Última actualización: ' + new Date().toLocaleTimeString();

                let html = '';
                data.fases.forEach((fase) => {
                    html += `<section class='phase-section'>
                                <div class='phase-title'>${fase.nombre}</div>`;
                    
                    fase.rutinas.forEach(r => {
                        html += `
                            <div class='routine-card animate-in'>
                                <div class='routine-header'>
                                    <div class='routine-info'>
                                        <div class='pos'>${r.pos}º</div>
                                        <div>
                                            <div class='club'>${r.club}</div>
                                            <div class='participants'>${r.participantes}</div>
                                        </div>
                                    </div>
                                    <div class='total-score'>${r.nota.toFixed(4)}</div>
                                </div>
                                <div class='panels-grid'>`;

                        r.paneles.forEach(p => {
                            html += `
                                <div class='panel-box'>
                                    <div class='panel-name'>${p.nombre} (x${(p.factor*100).toFixed(0)}%)</div>
                                    <div class='judges-row'>`;
                            
                            p.jueces.forEach(j => {
                                let cls = '';
                                if(j.min) cls = 'min';
                                if(j.max) cls = 'max';
                                html += `<div class='judge-note ${cls}'>${j.nota.toFixed(1)}</div>`;
                            });

                            html += `</div>
                                    <div class='panel-footer'>
                                        <div class='panel-media'>Media: ${p.media.toFixed(4)}</div>
                                        <div class='panel-puntos'>Panel: ${p.total_panel.toFixed(4)}</div>
                                    </div>
                                </div>`;
                        });

                        html += `</div></div>`;
                    });

                    html += `</section>`;
                });

                document.getElementById('ranking-content').innerHTML = html;
            } catch (err) {
                console.error('Error:', err);
            }
        }

        updateRanking();
        setInterval(updateRanking, 60000);
    </script>
</body>
</html>