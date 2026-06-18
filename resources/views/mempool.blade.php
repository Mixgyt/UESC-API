<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UESC-API - Mempool en Vivo y Bloques</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- CSS Styling (Premium Dark Mode with HSL/oklch Palettes) -->
    <style>
        :root {
            --bg-base: oklch(14% 0.03 240);
            --bg-card: oklch(18% 0.04 240 / 75%);
            --bg-card-hover: oklch(22% 0.05 240 / 80%);
            --border-color: oklch(25% 0.05 240 / 60%);
            --border-hover: oklch(35% 0.07 240 / 80%);
            
            --btc-orange: oklch(69% 0.19 50);
            --btc-orange-glow: oklch(69% 0.19 50 / 15%);
            --btc-orange-hover: oklch(74% 0.21 50);
            
            --block-blue: oklch(60% 0.15 240);
            --block-blue-glow: oklch(60% 0.15 240 / 15%);
            
            --text-primary: oklch(93% 0.01 240);
            --text-secondary: oklch(75% 0.02 240);
            --text-muted: oklch(55% 0.02 240);

            --success: oklch(65% 0.15 150);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, oklch(20% 0.08 50 / 8%) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, oklch(25% 0.08 240 / 8%) 0%, transparent 50%),
                linear-gradient(to right, oklch(100% 0 0 / 1.5%) 1px, transparent 1px),
                linear-gradient(to bottom, oklch(100% 0 0 / 1.5%) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 40px 40px, 40px 40px;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            max-width: 1250px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Header / Navbar */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--text-primary) 30%, var(--btc-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--btc-orange);
            color: var(--bg-base);
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.6rem;
            font-weight: 800;
            -webkit-text-fill-color: var(--bg-base);
            box-shadow: 0 0 20px var(--btc-orange-glow);
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid var(--border-color);
            background-color: transparent;
            color: var(--text-secondary);
        }

        .btn:hover {
            border-color: var(--border-hover);
            color: var(--text-primary);
            background-color: oklch(100% 0 0 / 2%);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-subtext {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        /* Layout */
        .mempool-layout {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 950px) {
            .mempool-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Blocks row style (horizontal slider/list) */
        .blocks-row-container {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .blocks-row-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .blocks-row-title {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .blocks-grid {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding: 0.5rem 0.25rem;
            scrollbar-width: thin;
        }

        /* Confirmed Block Card */
        .confirmed-block-card {
            background: linear-gradient(135deg, oklch(25% 0.05 240 / 75%) 0%, oklch(18% 0.06 250 / 85%) 100%);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.25rem;
            min-width: 175px;
            flex-shrink: 0;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            box-shadow: 0 4px 15px oklch(0% 0 0 / 15%);
        }

        .confirmed-block-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 0.75rem;
            border: 1px solid transparent;
            background: linear-gradient(135deg, oklch(60% 0.15 240 / 40%), transparent) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
            pointer-events: none;
        }

        .confirmed-block-card:hover {
            transform: translateY(-4px) scale(1.02);
            border-color: oklch(60% 0.15 240 / 50%);
            box-shadow: 0 8px 25px oklch(60% 0.15 240 / 18%);
        }

        .confirmed-block-card.highlighted {
            border-color: var(--block-blue) !important;
            box-shadow: 0 0 15px oklch(60% 0.15 240 / 45%) !important;
            transform: translateY(-2px);
        }

        .block-height {
            font-size: 1.15rem;
            font-weight: 700;
            color: oklch(75% 0.1 240);
            font-family: 'JetBrains Mono', monospace;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .block-stat-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .block-stat-val {
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Visualization Box */
        .visualizer-container {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .visualizer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .visualizer-title {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-bar {
            background-color: oklch(10% 0.02 240);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            color: var(--text-primary);
            font-size: 0.9rem;
            outline: none;
            width: 300px;
            max-width: 100%;
            transition: all 0.2s ease;
        }

        .search-bar:focus {
            border-color: var(--btc-orange);
            box-shadow: 0 0 0 3px var(--btc-orange-glow);
        }

        /* Visual Grid of TXs */
        .mempool-grid {
            border: 1px solid var(--border-color);
            background-color: oklch(10% 0.02 240);
            border-radius: 0.5rem;
            min-height: 350px;
            max-height: 500px;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 8px;
            position: relative;
        }

        /* Individual TX Block */
        .tx-block {
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease, border-color 0.2s ease;
            position: relative;
            border: 1px solid transparent;
        }

        .tx-block:hover {
            transform: scale(1.18);
            z-index: 10;
            box-shadow: 0 0 12px var(--block-glow);
            border-color: var(--text-primary);
        }

        .tx-block.highlighted {
            animation: pulse-border 1.5s infinite;
            border: 2px solid #fff !important;
            transform: scale(1.2);
            z-index: 11;
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 0 0px #fff; }
            50% { box-shadow: 0 0 15px #fff; }
            100% { box-shadow: 0 0 0px #fff; }
        }

        /* Legend */
        .legend-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 0;
            font-size: 0.85rem;
            color: var(--text-secondary);
            flex-wrap: wrap;
        }

        .legend-colors {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .legend-color-box {
            width: 14px;
            height: 14px;
            border-radius: 2px;
        }

        /* Info / Tooltip Panel */
        .details-panel {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            position: sticky;
            top: 2.5rem;
        }

        .panel-title {
            font-size: 1.1rem;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .details-placeholder {
            color: var(--text-muted);
            font-size: 0.95rem;
            text-align: center;
            padding: 4rem 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .details-placeholder svg {
            color: var(--border-color);
        }

        .details-content {
            display: none;
            flex-direction: column;
            gap: 1rem;
            animation: fadeIn 0.2s ease;
        }

        .detail-row {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            border-bottom: 1px solid oklch(100% 0 0 / 3%);
            padding-bottom: 0.75rem;
        }

        .detail-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .detail-value {
            font-size: 0.95rem;
            color: var(--text-primary);
            word-break: break-all;
        }

        .detail-value.mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
        }

        /* Clickable TX Link in Block detail */
        .tx-link {
            color: var(--block-blue);
            cursor: pointer;
            transition: color 0.15s ease;
            text-decoration: underline;
        }

        .tx-link:hover {
            color: var(--text-primary);
        }

        /* Sync status indicator */
        .sync-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            background-color: var(--text-muted);
        }

        .indicator.online {
            background-color: var(--success);
            box-shadow: 0 0 10px oklch(65% 0.15 150 / 40%);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Loading & Empty States */
        .loading-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: oklch(14% 0.03 240 / 70%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--text-secondary);
            backdrop-filter: blur(4px);
            z-index: 100;
            border-radius: 0.5rem;
            transition: opacity 0.3s ease;
        }

        .loading-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* ===== RESPONSIVE MOBILE FIXES ===== */

        /* Tablet & small laptops */
        @media (max-width: 950px) {
            .container {
                padding: 1.5rem 1rem;
                gap: 1.5rem;
            }

            .visualizer-title {
                font-size: 1.1rem;
            }

            .blocks-row-title {
                font-size: 1rem;
            }
        }

        /* Mobile */
        @media (max-width: 600px) {
            .container {
                padding: 1.25rem 0.75rem;
                gap: 1.25rem;
            }

            /* Header stacks vertically */
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding-bottom: 1rem;
            }

            .header-links {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 0.5rem;
            }

            .logo {
                font-size: 1.25rem;
            }

            .logo-icon {
                width: 1.8rem;
                height: 1.8rem;
            }

            .btn {
                font-size: 0.8rem;
                padding: 0.45rem 0.8rem;
            }

            .sync-status {
                font-size: 0.78rem;
            }

            /* Stats grid: 2 cols on mobile */
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.3rem;
            }

            .stat-label {
                font-size: 0.7rem;
            }

            .stat-subtext {
                font-size: 0.75rem;
            }

            /* Blocks row */
            .blocks-row-container {
                padding: 1rem;
            }

            .blocks-row-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .blocks-row-title {
                font-size: 1rem;
            }

            .confirmed-block-card {
                min-width: 145px;
                padding: 1rem;
            }

            .block-height {
                font-size: 1rem;
            }

            .block-stat-row {
                font-size: 0.75rem;
            }

            /* Visualizer */
            .visualizer-container {
                padding: 1rem;
            }

            .visualizer-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .visualizer-title {
                font-size: 1rem;
            }

            .search-bar {
                width: 100%;
                font-size: 0.85rem;
            }

            /* Legend bar wraps */
            .legend-bar {
                font-size: 0.75rem;
                gap: 0.5rem;
            }

            .legend-colors {
                flex-wrap: wrap;
                gap: 3px;
            }

            .legend-color-box {
                width: 10px;
                height: 10px;
            }

            /* Mempool grid */
            .mempool-grid {
                min-height: 250px;
                max-height: 350px;
                padding: 1rem;
                gap: 5px;
            }

            /* Details panel: un-stick on mobile */
            .details-panel {
                position: static;
                padding: 1.25rem;
            }

            .panel-title {
                font-size: 1rem;
            }

            .detail-label {
                font-size: 0.7rem;
            }

            .detail-value {
                font-size: 0.85rem;
            }

            .detail-value.mono {
                font-size: 0.78rem;
            }

            .details-placeholder {
                padding: 2rem 0;
            }

            .details-placeholder svg {
                width: 36px;
                height: 36px;
            }
        }

        /* Extra small screens */
        @media (max-width: 380px) {
            .container {
                padding: 1rem 0.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-value {
                font-size: 1.15rem;
            }

            .header-links {
                flex-direction: column;
            }

            .confirmed-block-card {
                min-width: 130px;
            }

            .legend-bar > span:last-child {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <header>
            <div class="logo">
                <span class="logo-icon">₿</span>
                UESC-API
            </div>
            <div class="header-links">
                <div class="sync-status">
                    <span class="indicator online" id="sync-indicator"></span>
                    <span id="sync-text">Sincronizando...</span>
                </div>
                <a href="/" class="btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Volver al Inicio
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Transacciones Pendientes</span>
                <span class="stat-value" id="stat-count">0</span>
                <span class="stat-subtext" id="stat-count-sub">En espera de confirmación</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Tamaño del Mempool</span>
                <span class="stat-value" id="stat-vsize">0 vB</span>
                <span class="stat-subtext" id="stat-vsize-sub">Tamaño virtual total</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Tasa de Comisión Promedio</span>
                <span class="stat-value" id="stat-avg-fee">0 sat/vB</span>
                <span class="stat-subtext" id="stat-fee-range">Rango: 0 - 0 sat/vB</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Comisiones Acumuladas</span>
                <span class="stat-value" id="stat-total-fees">0 sat</span>
                <span class="stat-subtext" id="stat-fees-btc">0 BTC</span>
            </div>
        </section>

        <!-- Layout Layout -->
        <section class="mempool-layout">
            
            <!-- Left Column: Visualizer & Blocks Row -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; min-width: 0;">
                
                <!-- Confirmed Blocks Row -->
                <div class="blocks-row-container">
                    <div class="blocks-row-header">
                        <h3 class="blocks-row-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--block-blue)">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/>
                            </svg>
                            Últimos Bloques Confirmados
                        </h3>
                        <span style="font-size: 0.8rem; color: var(--text-muted)">*Haz clic en un bloque para inspeccionarlo</span>
                    </div>
                    <div class="blocks-grid" id="confirmed-blocks-grid">
                        <!-- Filled dynamically by JS -->
                    </div>
                </div>

                <!-- Visualizer Container -->
                <div class="visualizer-container">
                    <div class="visualizer-header">
                        <h3 class="visualizer-title">
                            <span class="indicator online" style="background-color: var(--btc-orange); box-shadow: 0 0 8px var(--btc-orange-glow); width: 6px; height: 6px;"></span>
                            Transacciones en Mempool
                        </h3>
                        <input type="text" id="tx-search" class="search-bar" placeholder="Buscar por TXID...">
                    </div>

                    <!-- Color scale legend -->
                    <div class="legend-bar">
                        <span>Prioridad/Fee Rate:</span>
                        <div class="legend-colors">
                            <div class="legend-color-box" style="background-color: oklch(70% 0.17 40);"></div>
                            <span>10+ sat/vB</span>
                            <div class="legend-color-box" style="background-color: oklch(72% 0.18 60);"></div>
                            <span>5-10 sat/vB</span>
                            <div class="legend-color-box" style="background-color: oklch(78% 0.15 80);"></div>
                            <span>2-5 sat/vB</span>
                            <div class="legend-color-box" style="background-color: oklch(60% 0.12 240);"></div>
                            <span>&lt; 2 sat/vB</span>
                        </div>
                        <span style="margin-left: auto; font-size: 0.8rem; color: var(--text-muted)">*El tamaño del bloque indica su vsize relativo</span>
                    </div>

                    <div style="position: relative;">
                        <!-- Loading state overlay -->
                        <div id="mempool-loader" class="loading-overlay">
                            <span>Cargando transacciones de mempool...</span>
                        </div>

                        <!-- Visual grid of blocks -->
                        <div class="mempool-grid" id="mempool-blocks-container">
                            <!-- Filled dynamically by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Detail Inspector -->
            <div class="details-panel">
                <h3 class="panel-title" id="inspector-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    Inspector de Detalles
                </h3>

                <!-- Placeholder state -->
                <div class="details-placeholder" id="panel-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/>
                    </svg>
                    <span>Selecciona una transacción o un bloque para ver sus detalles en tiempo real</span>
                </div>

                <!-- Info detail rows for Transaction -->
                <div class="details-content" id="panel-content" style="display: none; flex-direction: column; gap: 1rem;">
                    <div class="detail-row">
                        <span class="detail-label">TXID (Transacción)</span>
                        <span class="detail-value mono" id="detail-txid">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tasa de Comisión (Fee Rate)</span>
                        <span class="detail-value" id="detail-fee-rate">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Comisión Total</span>
                        <span class="detail-value" id="detail-fee">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tamaño Virtual (vSize)</span>
                        <span class="detail-value" id="detail-vsize">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Ingresó al Mempool</span>
                        <span class="detail-value" id="detail-time">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Dependencias directas</span>
                        <span class="detail-value mono" id="detail-depends">-</span>
                    </div>
                </div>

                <!-- Info detail rows for Block -->
                <div class="details-content" id="block-panel-content" style="display: none; flex-direction: column; gap: 1rem;">
                    <div class="detail-row">
                        <span class="detail-label">Altura (Height)</span>
                        <span class="detail-value mono" id="block-detail-height" style="font-size: 1.25rem; font-weight: 700; color: oklch(75% 0.1 240);">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hash del Bloque</span>
                        <span class="detail-value mono" id="block-detail-hash" style="font-size: 0.8rem;">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Transacciones Incluidas</span>
                        <span class="detail-value" id="block-detail-tx-count">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tamaño (Size)</span>
                        <span class="detail-value" id="block-detail-size">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Peso (Weight)</span>
                        <span class="detail-value" id="block-detail-weight">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Dificultad</span>
                        <span class="detail-value mono" id="block-detail-difficulty">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Subvención + Fees</span>
                        <span class="detail-value" id="block-detail-reward">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Minado en</span>
                        <span class="detail-value" id="block-detail-time">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">TXIDs del Bloque</span>
                        <div id="block-detail-txids" style="max-height: 150px; overflow-y: auto; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; background-color: oklch(10% 0.02 240); padding: 0.5rem; border-radius: 0.25rem; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 4px;">
                            <!-- Filled dynamically -->
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <!-- Script: Real-Time Fetching and Visualizing -->
    <script>
        const UPDATE_INTERVAL = 8000; // 8 segundos
        let selectedTxid = null;
        let selectedBlockHash = null;
        let mempoolData = [];
        let blocksData = [];

        // Elementos DOM
        const elLoader = document.getElementById('mempool-loader');
        const elContainer = document.getElementById('mempool-blocks-container');
        const elBlocksGrid = document.getElementById('confirmed-blocks-grid');
        const elSearch = document.getElementById('tx-search');
        
        // Stats elements
        const statCount = document.getElementById('stat-count');
        const statVsize = document.getElementById('stat-vsize');
        const statAvgFee = document.getElementById('stat-avg-fee');
        const statFeeRange = document.getElementById('stat-fee-range');
        const statTotalFees = document.getElementById('stat-total-fees');
        const statFeesBtc = document.getElementById('stat-fees-btc');
        
        // Inspector elements
        const inspectorTitle = document.getElementById('inspector-title');
        const panelPlaceholder = document.getElementById('panel-placeholder');
        
        // TX inspector elements
        const panelContent = document.getElementById('panel-content');
        const dTxid = document.getElementById('detail-txid');
        const dFeeRate = document.getElementById('detail-fee-rate');
        const dFee = document.getElementById('detail-fee');
        const dVsize = document.getElementById('detail-vsize');
        const dTime = document.getElementById('detail-time');
        const dDepends = document.getElementById('detail-depends');

        // Block inspector elements
        const blockPanelContent = document.getElementById('block-panel-content');
        const bHeight = document.getElementById('block-detail-height');
        const bHash = document.getElementById('block-detail-hash');
        const bTxCount = document.getElementById('block-detail-tx-count');
        const bSize = document.getElementById('block-detail-size');
        const bWeight = document.getElementById('block-detail-weight');
        const bDifficulty = document.getElementById('block-detail-difficulty');
        const bReward = document.getElementById('block-detail-reward');
        const bTime = document.getElementById('block-detail-time');
        const bTxids = document.getElementById('block-detail-txids');

        const syncIndicator = document.getElementById('sync-indicator');
        const syncText = document.getElementById('sync-text');

        // Inicializador
        async function init() {
            await updateAll();
            elLoader.classList.add('hidden');
            setInterval(updateAll, UPDATE_INTERVAL);

            // Event listener buscador
            elSearch.addEventListener('input', handleSearch);
        }

        // Obtener datos y actualizar interfaz
        async function updateAll() {
            try {
                setSyncState(true);
                const [mempoolRes, summaryRes, blocksRes] = await Promise.all([
                    fetch('/api/mempool?per_page=100'),
                    fetch('/api/mempool/summary'),
                    fetch('/api/blocks?per_page=6')
                ]);

                if (!mempoolRes.ok || !summaryRes.ok || !blocksRes.ok) throw new Error("HTTP error");

                const mempoolJson = await mempoolRes.json();
                const summaryJson = await summaryRes.json();
                const blocksJson = await blocksRes.json();

                mempoolData = mempoolJson.data || [];
                const summary = summaryJson.data || {};
                blocksData = blocksJson.data || [];

                renderStats(summary);
                renderGrid(mempoolData);
                renderBlocksGrid(blocksData);
                
                // Si la transacción previamente seleccionada sigue en mempool, actualiza su inspector
                if (selectedTxid) {
                    const activeTx = mempoolData.find(tx => tx.txid === selectedTxid);
                    if (activeTx) {
                        showDetails(activeTx);
                    } else {
                        // Podría estar confirmada
                        const possibleConfirmBlock = blocksData.find(b => b.transactions.includes(selectedTxid));
                        if (possibleConfirmBlock) {
                            showBlockDetails(possibleConfirmBlock);
                        } else {
                            clearInspector();
                        }
                    }
                } else if (selectedBlockHash) {
                    const activeBlock = blocksData.find(b => b.hash === selectedBlockHash);
                    if (activeBlock) {
                        showBlockDetails(activeBlock);
                    }
                }
                setTimeout(() => setSyncState(false), 800);
            } catch (err) {
                console.error("Error al actualizar mempool y bloques:", err);
                syncText.innerText = "Error de Conexión";
                syncIndicator.className = "indicator offline";
            }
        }

        function setSyncState(isSyncing) {
            if (isSyncing) {
                syncText.innerText = "Sincronizando...";
                syncIndicator.className = "indicator online";
            } else {
                syncText.innerText = "Mempool Sincronizada";
                syncIndicator.className = "indicator online";
            }
        }

        // Renderizar paneles estadísticos superiores
        function renderStats(summary) {
            statCount.innerText = Number(summary.tx_count || 0).toLocaleString();
            
            const vsizeVal = Number(summary.total_vsize || summary.total_size_vbytes || 0);
            statVsize.innerText = formatBytes(vsizeVal);
            
            const avgFee = parseFloat(summary.fee_avg_sat_vbyte || 0).toFixed(1);
            statAvgFee.innerText = `${avgFee} sat/vB`;

            const minFee = parseFloat(summary.fee_min_sat_vbyte || 0).toFixed(1);
            const maxFee = parseFloat(summary.fee_max_sat_vbyte || 0).toFixed(1);
            statFeeRange.innerText = `Rango: ${minFee} - ${maxFee} sat/vB`;

            const totalFees = Number(summary.total_fees_sat || 0);
            statTotalFees.innerText = `${totalFees.toLocaleString()} sat`;
            statFeesBtc.innerText = `${(totalFees / 100000000).toFixed(8)} BTC`;
        }

        // Renderizar fila horizontal de bloques confirmados
        function renderBlocksGrid(blocksList) {
            if (blocksList.length === 0) {
                elBlocksGrid.innerHTML = '<div style="margin: auto; color: var(--text-muted); font-size: 0.9rem; padding: 1rem 0;">No hay bloques sincronizados todavía</div>';
                return;
            }

            elBlocksGrid.innerHTML = '';

            blocksList.forEach(block => {
                const card = document.createElement('div');
                card.className = 'confirmed-block-card';
                if (selectedBlockHash === block.hash) {
                    card.classList.add('highlighted');
                }

                const blockTime = new Date(block.time);
                const timeStr = isNaN(blockTime.getTime()) ? block.time : getRelativeTime(blockTime);

                card.innerHTML = `
                    <div class="block-height">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="color: var(--block-blue)">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        </svg>
                        #${block.height}
                    </div>
                    <div class="block-stat-row">
                        <span>TXs:</span>
                        <span class="block-stat-val">${block.tx_count}</span>
                    </div>
                    <div class="block-stat-row">
                        <span>Tamaño:</span>
                        <span class="block-stat-val">${formatBytes(block.size)}</span>
                    </div>
                    <div class="block-stat-row">
                        <span>Minado:</span>
                        <span class="block-stat-val" style="font-size:0.75rem;">${timeStr}</span>
                    </div>
                `;

                card.addEventListener('click', () => {
                    // Limpiar selecciones anteriores de la app
                    const activeBlock = elBlocksGrid.querySelector('.confirmed-block-card.highlighted');
                    if (activeBlock) activeBlock.classList.remove('highlighted');
                    
                    const activeTx = elContainer.querySelector('.tx-block.highlighted');
                    if (activeTx) activeTx.classList.remove('highlighted');

                    card.classList.add('highlighted');
                    selectedBlockHash = block.hash;
                    selectedTxid = null; // Limpiar transacción seleccionada
                    showBlockDetails(block);
                });

                elBlocksGrid.appendChild(card);
            });
        }

        // Renderizar cuadrícula interactiva de transacciones
        function renderGrid(txList) {
            if (txList.length === 0) {
                elContainer.innerHTML = '<div style="margin: auto; color: var(--text-muted); font-size: 0.95rem;">El mempool está vacío actualmente</div>';
                return;
            }

            // Encontrar límites de vsize para redimensionamiento relativo
            const vsizes = txList.map(tx => tx.vsize || 0);
            const minVsize = Math.min(...vsizes);
            const maxVsize = Math.max(...vsizes);

            elContainer.innerHTML = '';

            txList.forEach(tx => {
                const block = document.createElement('div');
                block.className = 'tx-block';
                block.id = `tx-${tx.txid}`;

                // Dimensionamiento relativo (entre 12px y 36px)
                let size = 16;
                if (maxVsize > minVsize) {
                    const ratio = ((tx.vsize || 0) - minVsize) / (maxVsize - minVsize);
                    size = 12 + ratio * 24;
                }
                block.style.width = `${size}px`;
                block.style.height = `${size}px`;

                // Coloreado según fee_rate (sat/vByte)
                const rate = tx.fee_rate_sat_vbyte || 1;
                let color, glow;
                if (rate >= 10) {
                    color = 'oklch(70% 0.17 40)';     // Naranja intenso/Rojizo
                    glow = 'oklch(70% 0.17 40 / 40%)';
                } else if (rate >= 5) {
                    color = 'oklch(72% 0.18 60)';     // Naranja amarillento
                    glow = 'oklch(72% 0.18 60 / 40%)';
                } else if (rate >= 2) {
                    color = 'oklch(78% 0.15 80)';     // Amarillo suave
                    glow = 'oklch(78% 0.15 80 / 40%)';
                } else {
                    color = 'oklch(60% 0.12 240)';    // Azul / Violeta
                    glow = 'oklch(60% 0.12 240 / 40%)';
                }

                block.style.backgroundColor = color;
                block.style.setProperty('--block-glow', glow);

                // Conservar estado seleccionado
                if (selectedTxid === tx.txid) {
                    block.classList.add('highlighted');
                }

                // Tooltip básico nativo
                block.title = `TXID: ${tx.txid.substring(0, 10)}...\nFee Rate: ${rate.toFixed(1)} sat/vB\nSize: ${tx.vsize} vB`;

                // Interacciones
                block.addEventListener('click', () => {
                    // Remover clase destacada anterior
                    const activeTx = elContainer.querySelector('.tx-block.highlighted');
                    if (activeTx) activeTx.classList.remove('highlighted');
                    
                    const activeBlock = elBlocksGrid.querySelector('.confirmed-block-card.highlighted');
                    if (activeBlock) activeBlock.classList.remove('highlighted');

                    block.classList.add('highlighted');
                    selectedTxid = tx.txid;
                    selectedBlockHash = null; // Limpiar bloque seleccionado
                    showDetails(tx);
                });

                elContainer.appendChild(block);
            });

            // Si se estaba buscando, aplicar filtro
            handleSearch();
        }

        // Inspeccionar transacción del mempool
        function showDetails(tx) {
            inspectorTitle.innerHTML = `
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--btc-orange)">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Inspector de Transacción
            `;
            panelPlaceholder.style.display = 'none';
            blockPanelContent.style.display = 'none';
            panelContent.style.display = 'flex';

            dTxid.innerText = tx.txid;
            dFeeRate.innerText = `${parseFloat(tx.fee_rate_sat_vbyte || 0).toFixed(2)} sat/vB`;
            dFee.innerText = `${Number(tx.fee_sat || tx.fee || 0).toLocaleString()} sat`;
            dVsize.innerText = `${tx.vsize} vB`;
            
            const date = new Date(tx.time);
            dTime.innerText = isNaN(date.getTime()) ? tx.time : date.toLocaleString();
            
            const deps = tx.depends || [];
            dDepends.innerText = deps.length > 0 ? deps.join(', ') : 'Ninguna';
        }

        // Inspeccionar Bloque Confirmado
        function showBlockDetails(block) {
            inspectorTitle.innerHTML = `
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--block-blue)">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                </svg>
                Inspector de Bloque
            `;
            panelPlaceholder.style.display = 'none';
            panelContent.style.display = 'none';
            blockPanelContent.style.display = 'flex';

            bHeight.innerText = `#${block.height}`;
            bHash.innerText = block.hash;
            bTxCount.innerText = block.tx_count;
            bSize.innerText = formatBytes(block.size);
            bWeight.innerText = `${block.weight} WU`;
            bDifficulty.innerText = block.difficulty;

            const rewardSat = Number(block.miner_reward_sat || block.miner_reward || 0);
            bReward.innerText = `${rewardSat.toLocaleString()} sat (${(rewardSat / 100000000).toFixed(8)} BTC)`;

            const blockTime = new Date(block.time);
            bTime.innerText = isNaN(blockTime.getTime()) ? block.time : blockTime.toLocaleString();

            const txids = block.transactions || [];
            if (txids.length === 0) {
                bTxids.innerHTML = '<span style="color: var(--text-muted)">Sin transacciones asociadas</span>';
            } else {
                bTxids.innerHTML = '';
                txids.forEach(txid => {
                    const row = document.createElement('span');
                    row.className = 'tx-link';
                    row.innerText = txid;
                    row.title = "Ver detalle de transacción";
                    row.addEventListener('click', () => {
                        fetchTxDetailsAndShow(txid);
                    });
                    bTxids.appendChild(row);
                });
            }
        }

        // Consultar transacción confirmada al vuelo y mostrarla
        async function fetchTxDetailsAndShow(txid) {
            try {
                const res = await fetch(`/api/transactions/${txid}`);
                if (!res.ok) throw new Error("TX no encontrada");
                const json = await res.json();
                const tx = json.data;
                
                // Mostrar en el inspector de TXs
                selectedTxid = tx.txid;
                selectedBlockHash = null;
                
                // Deseleccionar bloques de la UI
                const activeBlock = elBlocksGrid.querySelector('.confirmed-block-card.highlighted');
                if (activeBlock) activeBlock.classList.remove('highlighted');

                showDetails({
                    txid: tx.txid,
                    fee_rate_sat_vbyte: tx.fee_rate_sat_vbyte,
                    fee_sat: tx.fee_sat,
                    vsize: tx.vsize,
                    time: tx.confirmed_at,
                    depends: tx.inputs ? tx.inputs.map(i => i.txid) : []
                });
            } catch (err) {
                alert("Error al cargar detalles de la transacción: " + err.message);
            }
        }

        function clearInspector() {
            selectedTxid = null;
            selectedBlockHash = null;
            panelPlaceholder.style.display = 'flex';
            panelContent.style.display = 'none';
            blockPanelContent.style.display = 'none';
        }

        // Buscador reactivo
        function handleSearch() {
            const query = elSearch.value.trim().toLowerCase();
            const blocks = elContainer.querySelectorAll('.tx-block');
            
            blocks.forEach(block => {
                const txid = block.id.replace('tx-', '');
                if (query === '') {
                    block.style.opacity = '1';
                } else if (txid.includes(query)) {
                    block.style.opacity = '1';
                    block.style.transform = 'scale(1.2)';
                } else {
                    block.style.opacity = '0.15';
                    block.style.transform = 'scale(1)';
                }
            });
        }

        // Utilidades de formateo
        function formatBytes(bytes) {
            if (bytes === 0) return '0 vB';
            if (bytes < 1000) return `${bytes} vB`;
            const kb = bytes / 1000;
            if (kb < 1000) return `${kb.toFixed(2)} kvB`;
            return `${(kb / 1000).toFixed(2)} MvB`;
        }

        // Obtener tiempo relativo (e.g. "hace 5 minutos")
        function getRelativeTime(dateTime) {
            const seconds = Math.floor((new Date() - dateTime) / 1000);
            if (seconds < 60) return `hace ${seconds}s`;
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return `hace ${minutes}m`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `hace ${hours}h`;
            return dateTime.toLocaleDateString();
        }

        // Iniciar
        document.addEventListener('DOMContentLoaded', init);
    </script>

</body>
</html>
