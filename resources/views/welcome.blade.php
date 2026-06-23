<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UESC-API - Gateway</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- CSS Styling (Premium Dark Mode with HSL Palettes) -->
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
            
            --text-primary: oklch(93% 0.01 240);
            --text-secondary: oklch(75% 0.02 240);
            --text-muted: oklch(55% 0.02 240);

            --method-get: oklch(65% 0.15 150);
            --method-get-bg: oklch(65% 0.15 150 / 12%);
            --method-post: oklch(62% 0.16 230);
            --method-post-bg: oklch(62% 0.16 230 / 12%);
            --method-delete: oklch(60% 0.18 15);
            --method-delete-bg: oklch(60% 0.18 15 / 12%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
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
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
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

        .header-links {
            display: flex;
            gap: 1rem;
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
        }

        .btn-primary {
            background-color: var(--btc-orange);
            color: var(--bg-base);
            border: 1px solid var(--btc-orange);
            box-shadow: 0 4px 15px oklch(69% 0.19 50 / 20%);
        }

        .btn-primary:hover {
            background-color: var(--btc-orange-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px oklch(69% 0.19 50 / 30%);
        }

        .btn-outline {
            border: 1px solid var(--border-color);
            background-color: transparent;
            color: var(--text-secondary);
        }

        .btn-outline:hover {
            border-color: var(--border-hover);
            color: var(--text-primary);
            background-color: oklch(100% 0 0 / 2%);
        }

        /* Hero / Overview Grid */
        .grid-overview {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 900px) {
            .grid-overview {
                grid-template-columns: 1fr;
            }
        }

        .hero-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2.5rem;
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, var(--btc-orange-glow) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-tag {
            align-self: flex-start;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--btc-orange);
            font-weight: 700;
            background-color: var(--btc-orange-glow);
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            margin-bottom: 1.25rem;
            border: 1px solid oklch(69% 0.19 50 / 25%);
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 1rem;
        }

        .hero-desc {
            color: var(--text-secondary);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            max-width: 600px;
        }

        /* Live Status Widget */
        .status-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            backdrop-filter: blur(8px);
        }

        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .status-title {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            background-color: var(--text-muted);
            box-shadow: 0 0 8px transparent;
        }

        .indicator.online {
            background-color: oklch(65% 0.15 150);
            box-shadow: 0 0 10px oklch(65% 0.15 150 / 40%);
            animation: pulse 2s infinite;
        }

        .indicator.offline {
            background-color: oklch(60% 0.18 15);
            box-shadow: 0 0 10px oklch(60% 0.18 15 / 40%);
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }

        .status-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .status-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-value {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .status-value.mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.1rem;
        }

        /* Documentation & Endpoint List */
        .section-title {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title::after {
            content: '';
            flex-grow: 1;
            height: 1px;
            background-color: var(--border-color);
        }

        /* Interactive Filter Bar */
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex-grow: 1;
            min-width: 250px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: var(--btc-orange);
            box-shadow: 0 0 0 3px var(--btc-orange-glow);
        }

        .filter-btn {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 0.6rem 1.1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            border-color: var(--border-hover);
            color: var(--text-primary);
        }

        .filter-btn.active {
            background-color: var(--btc-orange-glow);
            border-color: var(--btc-orange);
            color: var(--btc-orange);
        }

        /* Endpoints Accordion */
        .endpoints-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .endpoint-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .endpoint-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 4px 20px oklch(0% 0 0 / 20%);
        }

        .endpoint-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            cursor: pointer;
            user-select: none;
            gap: 1.5rem;
        }

        .endpoint-identity {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-grow: 1;
            min-width: 0;
        }

        .method-badge {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            border-radius: 0.35rem;
            width: 75px;
            text-align: center;
            flex-shrink: 0;
        }

        .method-badge.get {
            background-color: var(--method-get-bg);
            color: var(--method-get);
            border: 1px solid oklch(65% 0.15 150 / 25%);
        }

        .method-badge.post {
            background-color: var(--method-post-bg);
            color: var(--method-post);
            border: 1px solid oklch(62% 0.16 230 / 25%);
        }

        .method-badge.delete {
            background-color: var(--method-delete-bg);
            color: var(--method-delete);
            border: 1px solid oklch(60% 0.18 15 / 25%);
        }

        .endpoint-path {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-primary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .endpoint-path span.param {
            color: var(--btc-orange);
        }

        .endpoint-summary {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-left: auto;
            text-align: right;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 450px;
        }

        @media (max-width: 768px) {
            .endpoint-trigger {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 1rem;
            }
            .endpoint-summary {
                margin-left: 0;
                text-align: left;
                max-width: 100%;
                white-space: normal;
            }
            .endpoint-identity {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            .endpoint-path {
                white-space: normal;
                word-break: break-all;
                font-size: 0.85rem;
            }
        }

        .endpoint-icon {
            color: var(--text-muted);
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }

        .endpoint-card.expanded .endpoint-icon {
            transform: rotate(180deg);
        }

        .endpoint-content {
            display: none;
            border-top: 1px solid var(--border-color);
            background-color: oklch(0% 0 0 / 12%);
            padding: 1.5rem;
            animation: slideDown 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .endpoint-card.expanded .endpoint-content {
            display: block;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 850px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .details-col {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .details-section-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .details-text {
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--text-secondary);
        }

        .param-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .param-table th, .param-table td {
            text-align: left;
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .param-table th {
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .param-name {
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-primary);
            font-weight: 500;
        }

        .param-type {
            font-family: 'JetBrains Mono', monospace;
            color: var(--btc-orange);
            font-size: 0.8rem;
        }

        .param-badge {
            background-color: oklch(100% 0 0 / 5%);
            border: 1px solid var(--border-color);
            padding: 0.1rem 0.35rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
        }

        .param-badge.required {
            background-color: var(--method-delete-bg);
            color: var(--method-delete);
            border-color: oklch(60% 0.18 15 / 25%);
        }

        /* Code Previews */
        .code-col {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .code-box {
            background-color: oklch(10% 0.02 240);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            overflow: hidden;
            position: relative;
        }

        .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: oklch(12% 0.03 240);
            border-bottom: 1px solid var(--border-color);
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .code-title {
            font-family: 'JetBrains Mono', monospace;
        }

        .copy-btn, .try-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: all 0.15s ease;
        }

        .copy-btn:hover, .try-btn:hover {
            color: var(--text-primary);
            background-color: oklch(100% 0 0 / 4%);
        }

        .code-box pre {
            margin: 0;
            padding: 1.25rem;
            overflow-x: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            line-height: 1.5;
            color: var(--text-secondary);
        }

        /* Try Out Container */
        .try-console {
            margin-top: 1rem;
            background-color: oklch(8% 0.01 240);
            border: 1px solid var(--btc-orange-glow);
            border-radius: 0.5rem;
            padding: 1rem;
            display: none;
        }

        .try-console.active {
            display: block;
        }

        .console-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .console-output {
            max-height: 250px;
            overflow-y: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: oklch(65% 0.15 150);
            background-color: oklch(6% 0.01 240);
            padding: 0.75rem;
            border-radius: 0.35rem;
            border: 1px solid var(--border-color);
            white-space: pre-wrap;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: auto;
        }

        /* Animations */
        @keyframes pulse {
            0% {
                opacity: 0.6;
                box-shadow: 0 0 8px oklch(65% 0.15 150 / 30%);
            }
            50% {
                opacity: 1;
                box-shadow: 0 0 14px oklch(65% 0.15 150 / 60%);
            }
            100% {
                opacity: 0.6;
                box-shadow: 0 0 8px oklch(65% 0.15 150 / 30%);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== RESPONSIVE MOBILE FIXES ===== */

        /* Tablet & small laptops */
        @media (max-width: 900px) {
            .container {
                padding: 1.5rem 1rem;
                gap: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-card {
                padding: 2rem;
            }

            .status-card {
                padding: 1.5rem;
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
                gap: 1rem;
                padding-bottom: 1rem;
            }

            .header-links {
                flex-wrap: wrap;
                gap: 0.5rem;
                width: 100%;
            }

            .header-links .btn {
                font-size: 0.8rem;
                padding: 0.5rem 0.8rem;
            }

            .logo {
                font-size: 1.25rem;
            }

            .logo-icon {
                width: 1.8rem;
                height: 1.8rem;
            }

            /* Hero section */
            .hero-card {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 1.5rem;
                letter-spacing: -0.02em;
            }

            .hero-desc {
                font-size: 0.9rem;
            }

            .hero-tag {
                font-size: 0.65rem;
            }

            /* Status card */
            .status-card {
                padding: 1.25rem;
            }

            .status-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }

            .status-value {
                font-size: 1.1rem;
            }

            .status-value.mono {
                font-size: 0.9rem;
            }

            /* Section title */
            .section-title {
                font-size: 1.25rem;
            }

            /* Filter bar wraps properly */
            .filter-bar {
                gap: 0.5rem;
            }

            .search-input {
                min-width: 100%;
                font-size: 0.85rem;
            }

            .filter-btn {
                padding: 0.45rem 0.75rem;
                font-size: 0.8rem;
            }

            /* Endpoint content */
            .content-grid {
                grid-template-columns: 1fr;
            }

            .endpoint-content {
                padding: 1rem;
            }

            /* Param table scrollable */
            .param-table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .param-table th,
            .param-table td {
                white-space: nowrap;
                padding: 0.5rem 0.6rem;
                font-size: 0.78rem;
            }

            /* Code boxes */
            .code-box pre {
                padding: 0.75rem;
                font-size: 0.75rem;
            }

            .code-header {
                padding: 0.4rem 0.75rem;
                font-size: 0.75rem;
            }

            /* Method badge */
            .method-badge {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
                width: 60px;
            }

            /* Try console */
            .console-output {
                font-size: 0.7rem;
                max-height: 200px;
            }

            /* Footer */
            footer {
                padding: 1.5rem 0.5rem;
                font-size: 0.75rem;
            }

            /* Buttons */
            .btn {
                font-size: 0.8rem;
                padding: 0.5rem 0.9rem;
            }
        }

        /* Extra small screens */
        @media (max-width: 380px) {
            .container {
                padding: 1rem 0.5rem;
            }

            .hero-title {
                font-size: 1.3rem;
            }

            .hero-desc {
                font-size: 0.85rem;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }

            .header-links {
                flex-direction: column;
            }

            .header-links .btn {
                width: 100%;
                justify-content: center;
            }

            .filter-btn {
                flex: 1 1 auto;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Navbar -->
        <header>
            <div class="logo">
                <span class="logo-icon">₿</span>
                UESC-API
            </div>
            <div class="header-links">
                <a href="/mempool" class="btn btn-outline" style="border-color: oklch(69% 0.19 50 / 30%); color: var(--btc-orange); display: flex; align-items: center; gap: 0.35rem;">
                    <span class="indicator online" style="width: 6px; height: 6px;"></span>
                    Mempool en Vivo
                </a>
                <a href="#endpoints" class="btn btn-outline">Explorador</a>
                <a href="/api/node/info" target="_blank" class="btn btn-primary">
                    Probar API
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 3H3V4H7.3L2.6 8.7L3.3 9.4L8 4.7V9H9V3Z" fill="currentColor"/>
                    </svg>
                </a>
            </div>
        </header>

        <!-- Hero Card & Live Status -->
        <section class="grid-overview">
            <div class="hero-card">
                <span class="hero-tag">Bitcoin Regtest API Gateway</span>
                <h2 class="hero-title">Interfaz Limpia para Nodos de Desarrollo</h2>
                <p class="hero-desc">
                    Esta API REST actúa como puente entre un nodo <strong>Bitcoin Core (regtest)</strong> local y aplicaciones móviles o sistemas externos. Mantiene los valores en satoshis y expone bloques, transacciones y mempool en tiempo real de forma segura y optimizada.
                </p>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="#endpoints" class="btn btn-primary">Ver endpoints</a>
                    <a href="/mempool" class="btn btn-outline" style="border-color: var(--btc-orange); color: var(--btc-orange); display: flex; align-items: center; gap: 0.35rem;">
                        <span class="indicator online" style="width: 6px; height: 6px;"></span>
                        Mempool en Vivo
                    </a>
                    <a href="https://github.com/mixgyt/UESC-api" target="_blank" class="btn btn-outline">Repositorio</a>
                </div>
            </div>

            <!-- Live Stats Card -->
            <div class="status-card">
                <div class="status-header">
                    <span class="status-title">
                        <span class="indicator" id="node-indicator"></span>
                        Estado del Nodo
                    </span>
                    <span class="status-label" style="font-size:0.75rem;" id="node-sync-time">Conectando...</span>
                </div>
                <div class="status-grid">
                    <div class="status-item">
                        <span class="status-label">Red</span>
                        <span class="status-value" id="node-chain">-</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Altura (Blocks)</span>
                        <span class="status-value font-medium" id="node-blocks">-</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Dificultad</span>
                        <span class="status-value mono" id="node-difficulty">-</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Conexión RPC</span>
                        <span class="status-value" id="node-connection">-</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Endpoints Explorer -->
        <section id="endpoints">
            <h3 class="section-title">Explorador de Endpoints</h3>

            <div class="filter-bar">
                <input type="text" id="endpoint-search" class="search-input" placeholder="Buscar por ruta o descripción...">
                <button class="filter-btn active" data-filter="all">Todos</button>
                <button class="filter-btn" data-filter="auth">Auth</button>
                <button class="filter-btn" data-filter="node">Nodo</button>
                <button class="filter-btn" data-filter="blocks">Bloques</button>
                <button class="filter-btn" data-filter="tx">Transacciones</button>
                <button class="filter-btn" data-filter="mempool">Mempool</button>
                <button class="filter-btn" data-filter="devices">Dispositivos</button>
            </div>

            <div class="endpoints-list" id="endpoints-container">
                
                <!-- POST /api/auth/token -->
                <div class="endpoint-card" data-category="auth">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge post">POST</span>
                            <span class="endpoint-path">/api/auth/token</span>
                        </div>
                        <span class="endpoint-summary">Genera un token de acceso Sanctum.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Autentica al usuario usando correo electrónico y contraseña para devolver un token de texto plano.</p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Parámetros del Body (JSON)</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Campo</th>
                                                <th>Tipo</th>
                                                <th>Requerido</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="param-name">email</td>
                                                <td class="param-type">string</td>
                                                <td><span class="param-badge required">Sí</span></td>
                                                <td>Correo del usuario registrado.</td>
                                            </tr>
                                            <tr>
                                                <td class="param-name">password</td>
                                                <td class="param-type">string</td>
                                                <td><span class="param-badge required">Sí</span></td>
                                                <td>Clave del usuario.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl -X POST {{ url('/api/auth/token') }} \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@regtest.local","password":"secret"}'</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "token": "1|examplePlainTextToken"
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POST /api/devices/register -->
                <div class="endpoint-card" data-category="devices">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge post">POST</span>
                            <span class="endpoint-path">/api/devices/register</span>
                        </div>
                        <span class="endpoint-summary">Registra un token FCM de dispositivo con una dirección BTC.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Asocia el token de Firebase Cloud Messaging (FCM) del dispositivo con una dirección de Bitcoin para recibir notificaciones push en tiempo real cuando se confirme una transacción hacia dicha dirección.</p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Parámetros del Body (JSON)</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Campo</th>
                                                <th>Tipo</th>
                                                <th>Requerido</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="param-name">token</td>
                                                <td class="param-type">string</td>
                                                <td><span class="param-badge required">Sí</span></td>
                                                <td>Token de registro FCM del dispositivo.</td>
                                            </tr>
                                            <tr>
                                                <td class="param-name">address</td>
                                                <td class="param-type">string</td>
                                                <td><span class="param-badge required">Sí</span></td>
                                                <td>Dirección BTC que se desea monitorear.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl -X POST {{ url('/api/devices/register') }} \
  -H "Content-Type: application/json" \
  -d '{"token":"fcm_token_example_123","address":"bcrt1qrecipientaddress456"}'</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (201 Created)</span>
                                    </div>
                                    <pre><code>{
  "message": "Device token registered successfully.",
  "data": {
    "id": 1,
    "token": "fcm_token_example_123",
    "address": "bcrt1qrecipientaddress456",
    "created_at": "2026-06-22T22:45:00.000000Z",
    "updated_at": "2026-06-22T22:45:00.000000Z"
  }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POST /api/devices/unregister -->
                <div class="endpoint-card" data-category="devices">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge post">POST</span>
                            <span class="endpoint-path">/api/devices/unregister</span>
                        </div>
                        <span class="endpoint-summary">Elimina el registro de un token FCM asociado a una dirección BTC.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Remueve la asociación entre un token FCM y la dirección de Bitcoin monitoreada, deteniendo el envío de notificaciones push de transacciones.</p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Parámetros del Body (JSON)</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Campo</th>
                                                <th>Tipo</th>
                                                <th>Requerido</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="param-name">token</td>
                                                <td class="param-type">string</td>
                                                <td><span class="param-badge required">Sí</span></td>
                                                <td>Token FCM registrado.</td>
                                            </tr>
                                            <tr>
                                                <td class="param-name">address</td>
                                                <td class="param-type">string</td>
                                                <td><span class="param-badge required">Sí</span></td>
                                                <td>Dirección BTC registrada.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl -X POST {{ url('/api/devices/unregister') }} \
  -H "Content-Type: application/json" \
  -d '{"token":"fcm_token_example_123","address":"bcrt1qrecipientaddress456"}'</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "message": "Device token unregistered successfully."
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DELETE /api/auth/token -->
                <div class="endpoint-card" data-category="auth">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge delete">DELETE</span>
                            <span class="endpoint-path">/api/auth/token</span>
                        </div>
                        <span class="endpoint-summary">Revoca el token de acceso activo.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Elimina permanentemente el token Sanctum actual, invalidándolo para futuras llamadas.</p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Autorización Requerida</h4>
                                    <p class="details-text" style="color: var(--method-delete);">
                                        <strong>Bearer Token:</strong> Requiere enviar el header <code>Authorization: Bearer {token}</code>
                                    </p>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl -X DELETE {{ url('/api/auth/token') }} \
  -H "Authorization: Bearer 1|examplePlainTextToken"</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (204 No Content)</span>
                                    </div>
                                    <pre><code>(Cuerpo vacío)</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POST /api/node/mine -->
                <div class="endpoint-card" data-category="node">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge post">POST</span>
                            <span class="endpoint-path">/api/node/mine</span>
                        </div>
                        <span class="endpoint-summary">Mina 1 bloque en el nodo regtest.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Genera exactamente 1 bloque de prueba de forma manual enviando la recompensa coinbase a una dirección específica. Su uso está regulado por un cooldown global configurable (<code>MINING_COOLDOWN_SECONDS</code>).</p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Autorización Requerida</h4>
                                    <p class="details-text" style="color: var(--method-delete);">
                                        <strong>Bearer Token:</strong> Requiere enviar el header <code>Authorization: Bearer {token}</code>
                                    </p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Parámetros del Body (JSON)</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Campo</th>
                                                <th>Tipo</th>
                                                <th>Requerido</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="param-name">address</td>
                                                <td class="param-type">string</td>
                                                <td><span class="param-badge required">Sí</span></td>
                                                <td>Dirección BTC destino del subsidio de bloque.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl -X POST {{ url('/api/node/mine') }} \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"address":"bcrt1qexampleaddress"}'</code></pre>
                                </div>

                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "message": "Blocks mined successfully.",
  "block_hashes": [
    "00000000aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"
  ]
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/node/info -->

                <div class="endpoint-card" data-category="node">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/node/info</span>
                        </div>
                        <span class="endpoint-summary">Obtiene información general del nodo.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Retorna el estado de la red (regtest), número de bloques, versión del software del nodo y dificultad.</p>
                                </div>
                                <div>
                                    <button class="btn btn-primary try-btn" onclick="tryGetEndpoint(this, '/api/node/info')">Probar en Vivo</button>
                                    <div class="try-console">
                                        <div class="console-header">Resultado en tiempo real:</div>
                                        <div class="console-output">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl {{ url('/api/node/info') }}</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": {
    "chain": "regtest",
    "blocks": 142,
    "headers": 142,
    "difficulty": 4.656542373906925e-10,
    "network_active": true,
    "version": 250000
  }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/blocks -->
                <div class="endpoint-card" data-category="blocks">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/blocks</span>
                        </div>
                        <span class="endpoint-summary">Lista paginada de bloques.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Devuelve un listado paginado de los bloques de la base de datos sincronizada, ordenados descendentemente por su altura (height).</p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Parámetros Query</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Parámetro</th>
                                                <th>Tipo</th>
                                                <th>Requerido</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="param-name">page</td>
                                                <td class="param-type">integer</td>
                                                <td><span class="param-badge">No</span></td>
                                                <td>Página actual. Def: 1.</td>
                                            </tr>
                                            <tr>
                                                <td class="param-name">per_page</td>
                                                <td class="param-type">integer</td>
                                                <td><span class="param-badge">No</span></td>
                                                <td>Elementos por página (máx. 100).</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <button class="btn btn-primary try-btn" onclick="tryGetEndpoint(this, '/api/blocks?per_page=2')">Probar en Vivo</button>
                                    <div class="try-console">
                                        <div class="console-header">Resultado en tiempo real:</div>
                                        <div class="console-output">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl "{{ url('/api/blocks?page=1&per_page=10') }}"</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": [
    {
      "hash": "00000000aaaa...",
      "height": 142,
      "time": "2026-06-12T20:10:00+00:00",
      "tx_count": 2,
      "size": 905,
      "weight": 3620,
      "difficulty": 4.6565e-10,
      "miner_reward_sat": 5000000000,
      "transactions": ["txid1", "txid2"]
    }
  ]
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/blocks/latest -->
                <div class="endpoint-card" data-category="blocks">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/blocks/latest</span>
                        </div>
                        <span class="endpoint-summary">Obtiene el bloque más reciente.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Retorna la información completa del bloque de mayor altura guardado en la base de datos.</p>
                                </div>
                                <div>
                                    <button class="btn btn-primary try-btn" onclick="tryGetEndpoint(this, '/api/blocks/latest')">Probar en Vivo</button>
                                    <div class="try-console">
                                        <div class="console-header">Resultado en tiempo real:</div>
                                        <div class="console-output">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl {{ url('/api/blocks/latest') }}</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": {
    "hash": "00000000aaa...",
    "height": 142,
    "time": "2026-06-12T20:10:00+00:00",
    "tx_count": 2,
    "size": 905,
    "weight": 3620,
    "difficulty": 4.6565e-10,
    "miner_reward_sat": 5000000000,
    "transactions": ["txid1", "txid2"]
  }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/blocks/{hashOrHeight} -->
                <div class="endpoint-card" data-category="blocks">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/blocks/<span class="param">{hashOrHeight}</span></span>
                        </div>
                        <span class="endpoint-summary">Busca un bloque por hash o altura.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Detecta dinámicamente si el parámetro es un hash completo de 64 caracteres o un número de altura (height), buscando el bloque correspondiente.</p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Parámetro de Ruta</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Parámetro</th>
                                                <th>Tipo</th>
                                                <th>Requerido</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="param-name">hashOrHeight</td>
                                                <td class="param-type">string | int</td>
                                                <td><span class="param-badge required">Sí</span></td>
                                                <td>Hash de 64 caracteres o entero de la altura del bloque.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl {{ url('/api/blocks/142') }}</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": {
    "hash": "00000000aaa...",
    "height": 142,
    "time": "2026-06-12T20:10:00+00:00",
    "tx_count": 2,
    "difficulty": 4.6565e-10,
    "miner_reward_sat": 5000000000,
    "transactions": ["txid1"]
  }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/blocks/{hashOrHeight}/transactions -->
                <div class="endpoint-card" data-category="blocks">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/blocks/<span class="param">{hashOrHeight}</span>/transactions</span>
                        </div>
                        <span class="endpoint-summary">Transacciones de un bloque específico.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Lista todas las transacciones confirmadas dentro del bloque solicitado. Útil para análisis de bloques.</p>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl {{ url('/api/blocks/142/transactions') }}</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": [
    {
      "txid": "txid1",
      "status": "confirmed",
      "fee_sat": 1000,
      "fee_rate_sat_vbyte": 2.5,
      "size": 400,
      "vsize": 400,
      "confirmed_at": "2026-06-12T20:10:00+00:00",
      "block_height": 142,
      "inputs": [],
      "outputs": []
    }
  ]
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/transactions -->
                <div class="endpoint-card" data-category="tx">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/transactions</span>
                        </div>
                        <span class="endpoint-summary">Listado de transacciones confirmadas.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Lista paginada de transacciones históricas confirmadas en bloques, ordenadas por fecha de confirmación decreciente.</p>
                                </div>
                                <div>
                                    <button class="btn btn-primary try-btn" onclick="tryGetEndpoint(this, '/api/transactions?per_page=2')">Probar en Vivo</button>
                                    <div class="try-console">
                                        <div class="console-header">Resultado en tiempo real:</div>
                                        <div class="console-output">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl {{ url('/api/transactions?page=1') }}</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": [
    {
      "txid": "txid1",
      "status": "confirmed",
      "fee_sat": 1000,
      "fee_rate_sat_vbyte": 2.5,
      "confirmed_at": "2026-06-12T20:10:00+00:00",
      "block_height": 142,
      "inputs": [
        {
          "txid": "prevtxid",
          "vout": 0,
          "value_sat": 5000000000
        }
      ],
      "outputs": [
        {
          "address": "bcrt1qexampleaddress",
          "value_sat": 4999998000
        }
      ]
    }
  ]
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/transactions/{txid} -->
                <div class="endpoint-card" data-category="tx">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/transactions/<span class="param">{txid}</span></span>
                        </div>
                        <span class="endpoint-summary">Detalle de una transacción por ID.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Muestra los detalles completos de una transacción confirmada. Si no existe en la base de datos local, consulta automáticamente al nodo vía RPC.</p>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl {{ url('/api/transactions/txid1') }}</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": {
    "txid": "txid1",
    "status": "confirmed",
    "fee_sat": 1000,
    "fee_rate_sat_vbyte": 2.5,
    "inputs": [...],
    "outputs": [...]
  }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/mempool -->
                <div class="endpoint-card" data-category="mempool">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/mempool</span>
                        </div>
                        <span class="endpoint-summary">Transacciones pendientes en el mempool.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Retorna la lista de transacciones que están en el mempool esperando confirmación. Permite filtrar y ordenar.</p>
                                </div>
                                <div>
                                    <h4 class="details-section-title">Parámetros Query</h4>
                                    <table class="param-table">
                                        <thead>
                                            <tr>
                                                <th>Parámetro</th>
                                                <th>Tipo</th>
                                                <th>Valores</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="param-name">sort</td>
                                                <td class="param-type">string</td>
                                                <td><code>fee_rate</code>, <code>time</code></td>
                                                <td>Columna por la cual ordenar.</td>
                                            </tr>
                                            <tr>
                                                <td class="param-name">order</td>
                                                <td class="param-type">string</td>
                                                <td><code>asc</code>, <code>desc</code></td>
                                                <td>Sentido de la ordenación.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <button class="btn btn-primary try-btn" onclick="tryGetEndpoint(this, '/api/mempool')">Probar en Vivo</button>
                                    <div class="try-console">
                                        <div class="console-header">Resultado en tiempo real:</div>
                                        <div class="console-output">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl "{{ url('/api/mempool?sort=fee_rate&order=desc') }}"</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": [
    {
      "txid": "deadbeef0000...",
      "fee_sat": 8600,
      "vsize": 512,
      "fee_rate_sat_vbyte": 16.79,
      "depends": [],
      "time": "2026-06-12T20:12:00+00:00"
    }
  ]
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GET /api/mempool/summary -->
                <div class="endpoint-card" data-category="mempool">
                    <div class="endpoint-trigger">
                        <div class="endpoint-identity">
                            <span class="method-badge get">GET</span>
                            <span class="endpoint-path">/api/mempool/summary</span>
                        </div>
                        <span class="endpoint-summary">Métricas y estadísticas agregadas del mempool.</span>
                        <span class="endpoint-icon">▼</span>
                    </div>
                    <div class="endpoint-content">
                        <div class="content-grid">
                            <div class="details-col">
                                <div>
                                    <h4 class="details-section-title">Descripción</h4>
                                    <p class="details-text">Calcula datos en tiempo real (tamaño total de vbytes, comisiones promedio, mínimas y máximas) acumulados de las transacciones sin confirmar.</p>
                                </div>
                                <div>
                                    <button class="btn btn-primary try-btn" onclick="tryGetEndpoint(this, '/api/mempool/summary')">Probar en Vivo</button>
                                    <div class="try-console">
                                        <div class="console-header">Resultado en tiempo real:</div>
                                        <div class="console-output">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="code-col">
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Ejemplo de cURL</span>
                                        <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
                                    </div>
                                    <pre><code>curl {{ url('/api/mempool/summary') }}</code></pre>
                                </div>
                                <div class="code-box">
                                    <div class="code-header">
                                        <span>Respuesta (200 OK)</span>
                                    </div>
                                    <pre><code>{
  "data": {
    "tx_count": 5,
    "total_vsize": 2048,
    "fee_min_sat_vbyte": 1,
    "fee_max_sat_vbyte": 10,
    "fee_avg_sat_vbyte": 4.2,
    "total_fees_sat": 8600
  }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Footer -->
        <footer>
            UESC-API v{{ app()->version() }} &bull; Bitcoin Regtest Portal &bull; Conectado vía JSON-RPC
        </footer>

    </div>

    <!-- JS Logic for Interactivity and Live Stats -->
    <script>
        // Collapsible API Card logic
        document.querySelectorAll('.endpoint-trigger').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const card = trigger.parentElement;
                const isExpanded = card.classList.contains('expanded');
                
                // Close all cards first for accordion clean view
                document.querySelectorAll('.endpoint-card').forEach(c => c.classList.remove('expanded'));
                
                // Toggle clicked card
                if (!isExpanded) {
                    card.classList.add('expanded');
                }
            });
        });

        // Search & Filter endpoints
        const searchInput = document.getElementById('endpoint-search');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const cards = document.querySelectorAll('.endpoint-card');

        function filterEndpoints() {
            const query = searchInput.value.toLowerCase();
            const activeFilter = document.querySelector('.filter-btn.active').dataset.filter;

            cards.forEach(card => {
                const category = card.dataset.category;
                const text = card.textContent.toLowerCase();
                const matchesSearch = text.includes(query);
                const matchesCategory = activeFilter === 'all' || category === activeFilter;

                if (matchesSearch && matchesCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterEndpoints);

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterEndpoints();
            });
        });

        // Copy Code to Clipboard
        function copyCode(btn) {
            const pre = btn.parentElement.nextElementSibling;
            navigator.clipboard.writeText(pre.textContent.trim()).then(() => {
                const originalText = btn.textContent;
                btn.textContent = '¡Copiado!';
                setTimeout(() => {
                    btn.textContent = originalText;
                }, 1500);
            });
        }

        // Live Node Stats Checker (runs on load)
        async function fetchNodeStats() {
            const indicator = document.getElementById('node-indicator');
            const syncTime = document.getElementById('node-sync-time');
            const nodeChain = document.getElementById('node-chain');
            const nodeBlocks = document.getElementById('node-blocks');
            const nodeDifficulty = document.getElementById('node-difficulty');
            const nodeConnection = document.getElementById('node-connection');

            try {
                const res = await fetch('/api/node/info');
                if (!res.ok) throw new Error('Status Error');
                const json = await res.json();
                const data = json.data;

                indicator.className = 'indicator online';
                syncTime.textContent = 'En Línea';
                nodeChain.textContent = data.chain;
                nodeBlocks.textContent = data.blocks;
                nodeDifficulty.textContent = Number(data.difficulty).toExponential(4);
                nodeConnection.textContent = 'Activo';
            } catch (err) {
                indicator.className = 'indicator offline';
                syncTime.textContent = 'Error RPC';
                nodeChain.textContent = 'regtest';
                nodeBlocks.textContent = 'Desconectado';
                nodeDifficulty.textContent = 'N/A';
                nodeConnection.textContent = 'Inactivo';
            }
        }

        // Try it out live testing helper
        async function tryGetEndpoint(btn, endpointPath) {
            const consoleBox = btn.nextElementSibling;
            const outputElement = consoleBox.querySelector('.console-output');
            
            consoleBox.classList.add('active');
            outputElement.textContent = 'Llamando a la API en vivo...';

            try {
                const res = await fetch(endpointPath);
                const json = await res.json();
                outputElement.textContent = JSON.stringify(json, null, 2);
            } catch (err) {
                outputElement.textContent = 'Error al conectar con la API:\n' + err.message;
                outputElement.style.color = 'oklch(60% 0.18 15)';
            }
        }

        // Initialize on load
        window.addEventListener('load', fetchNodeStats);
    </script>
</body>
</html>
