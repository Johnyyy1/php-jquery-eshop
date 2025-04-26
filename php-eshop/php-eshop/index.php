<?php
session_start();
require_once './database/db_connect.php';
require_once './functions/functions.php';

$sql_kategorie = "SELECT id, nazev FROM kategorie LIMIT 6"; 
$result_kategorie = $conn->query($sql_kategorie);
$kategorie = $result_kategorie->fetch_all(MYSQLI_ASSOC);

function spocitejKosik() {
    $pocet = 0;
    if (isset($_SESSION['kosik']) && is_array($_SESSION['kosik'])) {
        foreach ($_SESSION['kosik'] as $mnozstvi) {
            $pocet += $mnozstvi;
        }
    }
    return $pocet;
}
$kosik_pocet = spocitejKosik();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutureShop • Technologie zítřka</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'neo-black': '#080810',
                        'neo-blue': '#00f2fe',
                        'neo-purple': '#4837ff',
                        'neo-pink': '#ec38bc',
                        'neo-cyan': '#09FBD3',
                        'space-gray': '#1a1a2e'
                    },
                    fontFamily: {
                        'future': ['Space Grotesk', 'Audiowide', 'sans-serif']
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'floating 8s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate'
                    },
                    keyframes: {
                        floating: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 5px rgba(0, 242, 254, 0.7)' },
                            '100%': { boxShadow: '0 0 20px rgba(0, 242, 254, 0.9), 0 0 30px rgba(72, 55, 255, 0.5)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&display=swap');
        
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 242, 254, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(72, 55, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 50% 50%, rgba(236, 56, 188, 0.05) 0%, transparent 50%);
            background-attachment: fixed;
        }

        /* Responsive base styles */
        .container {
            width: 100%;
            padding-right: 1rem;
            padding-left: 1rem;
            margin-right: auto;
            margin-left: auto;
        }

        @media (min-width: 640px) {
            .container {
                max-width: 640px;
                padding-right: 2rem;
                padding-left: 2rem;
            }
        }

        @media (min-width: 768px) {
            .container {
                max-width: 768px;
            }
        }

        @media (min-width: 1024px) {
            .container {
                max-width: 1024px;
            }
        }

        @media (min-width: 1280px) {
            .container {
                max-width: 1280px;
            }
        }

        /* Responsive typography */
        .text-4xl {
            font-size: clamp(2rem, 5vw, 2.5rem);
        }

        .text-3xl {
            font-size: clamp(1.75rem, 4vw, 2rem);
        }

        .text-2xl {
            font-size: clamp(1.5rem, 3vw, 1.75rem);
        }

        .text-xl {
            font-size: clamp(1.25rem, 2.5vw, 1.5rem);
        }

        /* Rest of existing styles */
        .logo-text {
            font-family: 'Audiowide', cursive;
        }
        
        .glass-effect {
            background: rgba(10, 10, 26, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .neo-gradient {
            background: linear-gradient(135deg, #00f2fe 0%, #4837ff 100%);
        }
        
        .text-gradient {
            background: linear-gradient(90deg, #00f2fe, #4837ff, #ec38bc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            background-size: 300% 100%;
            animation: gradient-shift 8s ease infinite;
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }
        
        .card-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 30px rgba(0, 242, 254, 0.2);
        }
        
        .card-hover:hover .card-overlay {
            opacity: 0.5;
        }
        
        .card-hover:hover .card-cta {
            opacity: 1;
            transform: translateY(0);
        }
        
        .card-cta {
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.4s ease;
        }
        
        .border-glow {
            position: relative;
        }
        
        .border-glow::after {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            border-radius: inherit;
            background: linear-gradient(45deg, #00f2fe, #4837ff, #ec38bc, #00f2fe);
            background-size: 400% 400%;
            opacity: 0;
            z-index: -1;
            transition: all 0.6s ease;
            animation: border-animate 3s ease infinite;
        }
        
        .border-glow:hover::after {
            opacity: 1;
        }
        
        @keyframes border-animate {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }
        
        #canvas-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }
        
        .cyber-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 242, 254, 0.3);
        }
        
        .cyber-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }
        
        .cyber-btn:hover::before {
            left: 100%;
        }
        
        .animate-float {
            animation: floating 8s ease-in-out infinite;
        }
        
        .animate-glow {
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(10, 10, 26, 0.5);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #00f2fe 0%, #4837ff 100%);
            border-radius: 3px;
        }
        
        /* Navigation menu animation */
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background: linear-gradient(90deg, #00f2fe, #4837ff);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .nav-indicator {
            position: relative;
            z-index: 1;
        }
        
        .category-card {
            position: relative;
            overflow: hidden;
        }
        
        .category-image {
            transition: transform 1.5s ease;
        }
        
        .category-card:hover .category-image {
            transform: scale(1.08);
        }
        
        .holographic-effect {
            position: relative;
            overflow: hidden;
        }
        
        .holographic-effect::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0) 50%);
            transform: rotate(30deg);
            animation: holographic 6s linear infinite;
        }
        
        @keyframes holographic {
            0% { transform: rotate(30deg) translateX(-100%); }
            100% { transform: rotate(30deg) translateX(100%); }
        }

        /* Responsive grid layouts */
        .grid {
            display: grid;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .grid {
                gap: 1.5rem;
            }
        }

        @media (min-width: 768px) {
            .grid {
                gap: 2rem;
            }
        }

        /* Mobile menu improvements */
        .mobile-menu {
            position: fixed;
            top: 4rem;
            left: 0;
            right: 0;
            background: rgba(10, 10, 26, 0.95);
            backdrop-filter: blur(12px);
            padding: 1rem;
            transform: translateY(-100%);
            transition: transform 0.3s ease;
            z-index: 49;
        }

        .mobile-menu.active {
            transform: translateY(0);
        }

        /* Responsive cards and content */
        .product-card, .category-card {
            width: 100%;
        }

        @media (max-width: 639px) {
            .hero-content {
                padding: 2rem 1rem;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .nav-desktop {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        @media (min-width: 640px) {
            .mobile-menu-btn {
                display: none;
            }

            .nav-desktop {
                display: flex;
            }
        }

        /* Responsive images */
        img {
            max-width: 100%;
            height: auto;
        }

        /* Improved button styles for touch devices */
        @media (hover: none) {
            .cyber-btn::before {
                display: none;
            }

            .cyber-btn:active {
                transform: translateY(2px);
            }
        }

        /* Responsive form elements */
        input, select, textarea {
            font-size: 16px; /* Prevents zoom on iOS */
        }

        /* Flexible spacing utilities */
        .p-responsive {
            padding: clamp(1rem, 3vw, 2rem);
        }

        .m-responsive {
            margin: clamp(1rem, 3vw, 2rem);
        }
    </style>
</head>
<body class="bg-neo-black text-white min-h-screen flex flex-col">
    <canvas id="canvas-bg"></canvas>
    
    <header class="glass-effect fixed w-full z-50 border-b border-opacity-10 border-neo-blue">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="/php/EshopDU/index.php" class="flex items-center group">
                    <div class="w-10 h-10 mr-3 relative rounded-full overflow-hidden border border-neo-blue border-opacity-30 animate-pulse-slow">
                        <div class="absolute inset-0 neo-gradient opacity-70"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-3xl font-bold text-gradient logo-text tracking-wider group-hover:opacity-90 transition-opacity">
                        FUTURE<span class="text-neo-blue">SHOP</span>
                    </h1>
                </a>
                
                <nav class="hidden md:block">
                    <ul class="flex space-x-8 items-center">
                        <li>
                            <a href="/php/php-eshop/index.php" class="nav-link font-medium hover:text-neo-blue transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Domů
                            </a>
                        </li>
                        <li class="nav-indicator">
                            <a href="#categories" class="nav-link font-medium hover:text-neo-blue transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Kategorie
                            </a>
                        </li>
                        <li class="nav-indicator">
                            <a href="partials/about.php" class="nav-link font-medium hover:text-neo-blue transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 16V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                O nás
                            </a>
                        </li>
                        <li class="nav-indicator">
                            <a href="partials/contact.php" class="nav-link font-medium hover:text-neo-blue transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 5.48999V20.49" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M7.75 8.48999H5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8.5 11.49H5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Kontakt
                            </a>
                        </li>
                        <li>
                            <a href="partials/kosik.php" class="relative cyber-btn font-medium flex items-center px-5 py-2 rounded-full border-glow hover:bg-neo-purple hover:bg-opacity-10 transition-all">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.01 18.6L19.08 6.32C19.02 5.55 18.37 4.95 17.6 4.95H16.49V4.75C16.49 2.68 14.81 1 12.74 1C10.67 1 8.99 2.68 8.99 4.75V4.95H7.88C7.11 4.95 6.46 5.55 6.4 6.32L5.47 18.6C5.4 19.65 6.25 20.55 7.31 20.55H18.19C19.24 20.55 20.09 19.65 20.01 18.6Z" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Košík
                                <?php if ($kosik_pocet > 0): ?>
                                <span class="absolute -top-2 -right-2 flex items-center justify-center w-6 h-6 bg-neo-purple text-white text-xs rounded-full">
                                    <?php echo $kosik_pocet; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <div class="md:hidden">
                    <button id="menu-toggle" class="cyber-btn p-2 rounded-lg hover:bg-neo-purple hover:bg-opacity-10 transition-colors">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 12H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden mt-4 pb-2">
                <nav class="glass-effect rounded-xl p-4">
                    <ul class="space-y-4">
                        <li>
                            <a href="/php/EshopDU/index.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-neo-purple hover:bg-opacity-10 transition-colors">
                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Domů
                            </a>
                        </li>
                        <li>
                            <a href="#categories" class="flex items-center py-2 px-4 rounded-lg hover:bg-neo-purple hover:bg-opacity-10 transition-colors">
                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Kategorie
                            </a>
                        </li>
                        <li>
                            <a href="partials/about.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-neo-purple hover:bg-opacity-10 transition-colors">
                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 16V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                O nás
                            </a>
                        </li>
                        <li>
                            <a href="partials/contact.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-neo-purple hover:bg-opacity-10 transition-colors">
                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 5.48999V20.49" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M7.75 8.48999H5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8.5 11.49H5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Kontakt
                            </a>
                        </li>
                        <li>
                            <a href="partials/kosik.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-neo-purple hover:bg-opacity-10 transition-colors">
                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.01 18.6L19.08 6.32C19.02 5.55 18.37 4.95 17.6 4.95H16.49V4.75C16.49 2.68 14.81 1 12.74 1C10.67 1 8.99 2.68 8.99 4.75V4.95H7.88C7.11 4.95 6.46 5.55 6.4 6.32L5.47 18.6C5.4 19.65 6.25 20.55 7.31 20.55H18.19C19.24 20.55 20.09 19.65 20.01 18.6Z" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Košík
                                <?php if ($kosik_pocet > 0): ?>
                                <span class="ml-2 flex items-center justify-center w-6 h-6 bg-neo-purple text-white text-xs rounded-full">
                                    <?php echo $kosik_pocet; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 pt-32 pb-20 flex-grow">
    <section class="mb-24">
            <div class="text-center mb-16 relative">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-gradient-to-r from-neo-blue to-neo-purple rounded-full"></div>
                <h2 class="text-5xl font-bold mb-4 inline-block text-gradient logo-text tracking-wide">
                    OBJEVTE BUDOUCNOST
                </h2>
                <p class="text-xl text-gray-300 max-w-2xl mx-auto">Přeneste se do nové dimenze nakupování s technologiemi, které definují svět zítřka</p>
                
                <div class="mt-8 flex justify-center space-x-6">
                    <a href="#categories" class="cyber-btn px-8 py-3 rounded-full neo-gradient text-white font-medium hover:shadow-lg hover:shadow-neo-blue/20 transition-all">
                        Prozkoumat kategorie
                    </a>
                    <a href="#" class="cyber-btn px-8 py-3 rounded-full bg-transparent border border-neo-blue text-neo-blue font-medium hover:bg-neo-blue/10 transition-all">
                        Nejnovější produkty
                    </a>
                </div>
            </div>
            
            <!-- Hero section with animated elements -->
            <div class="relative rounded-2xl overflow-hidden glass-effect border border-white/5 h-96 md:h-[30rem] mb-20">
                <div class="absolute inset-0 bg-gradient-to-r from-neo-black/70 via-transparent to-neo-black/70 z-10"></div>
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full h-full absolute">
                        <div id="hero-animation" class="w-full h-full"></div>
                    </div>
                </div>
                
                <div class="absolute inset-0 z-20 flex flex-col justify-center px-6 md:px-16">
                    <div class="animate-float">
                        <h2 class="text-4xl md:text-6xl font-bold mb-4 text-white max-w-2xl">
                            Budoucnost je <span class="text-neo-blue">nyní</span>
                        </h2>
                        <p class="text-lg md:text-xl text-gray-200 max-w-xl mb-8">
                            Objevte revoluční produkty, které mění způsob, jakým žijeme, pracujeme a bavíme se.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="#" class="cyber-btn px-6 py-3 rounded-full neo-gradient text-white font-medium hover:opacity-90 transition-opacity flex items-center">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14.43 18.82C14.24 18.82 14.05 18.75 13.9 18.6C13.61 18.31 13.61 17.83 13.9 17.54L19.44 12L13.9 6.46C13.61 6.17 13.61 5.69 13.9 5.4C14.19 5.11 14.67 5.11 14.96 5.4L21.03 11.47C21.32 11.76 21.32 12.24 21.03 12.53L14.96 18.6C14.81 18.75 14.62 18.82 14.43 18.82Z" fill="currentColor"/>
                                    <path d="M20.33 12.75H3.5C3.09 12.75 2.75 12.41 2.75 12C2.75 11.59 3.09 11.25 3.5 11.25H20.33C20.74 11.25 21.08 11.59 21.08 12C21.08 12.41 20.74 12.75 20.33 12.75Z" fill="currentColor"/>
                                </svg>
                                Zobrazit doporučené
                            </a>
                            <a href="#" class="cyber-btn px-6 py-3 rounded-full bg-white/5 backdrop-blur-sm border border-white/20 text-white font-medium hover:bg-white/10 transition-all flex items-center">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 22.75H9C3.57 22.75 1.25 20.43 1.25 15V9C1.25 3.57 3.57 1.25 9 1.25H15C20.43 1.25 22.75 3.57 22.75 9V15C22.75 20.43 20.43 22.75 15 22.75ZM9 2.75C4.39 2.75 2.75 4.39 2.75 9V15C2.75 19.61 4.39 21.25 9 21.25H15C19.61 21.25 21.25 19.61 21.25 15V9C21.25 4.39 19.61 2.75 15 2.75H9Z" fill="currentColor"/>
                                    <path d="M12 9.25C10.48 9.25 9.25 10.48 9.25 12C9.25 13.52 10.48 14.75 12 14.75C13.52 14.75 14.75 13.52 14.75 12C14.75 10.48 13.52 9.25 12 9.25Z" fill="currentColor"/>
                                    <path d="M17 7.5C16.59 7.5 16.25 7.16 16.25 6.75C16.25 6.34 16.59 6 17 6C17.41 6 17.75 6.34 17.75 6.75C17.75 7.16 17.41 7.5 17 7.5Z" fill="currentColor"/>
                                </svg>
                                Sledujte nás
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Feature highlights -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-20">
                <div class="glass-effect rounded-2xl p-8 border-glow hover:transform hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 rounded-full neo-gradient flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 7.75C11.59 7.75 11.25 7.41 11.25 7V2C11.25 1.59 11.59 1.25 12 1.25C12.41 1.25 12.75 1.59 12.75 2V7C12.75 7.41 12.41 7.75 12 7.75Z" fill="currentColor"/>
                            <path d="M12 22.75C11.59 22.75 11.25 22.41 11.25 22V17C11.25 16.59 11.59 16.25 12 16.25C12.41 16.25 12.75 16.59 12.75 17V22C12.75 22.41 12.41 22.75 12 22.75Z" fill="currentColor"/>
                            <path d="M22 12.75H17C16.59 12.75 16.25 12.41 16.25 12C16.25 11.59 16.59 11.25 17 11.25H22C22.41 11.25 22.75 11.59 22.75 12C22.75 12.41 22.41 12.75 22 12.75Z" fill="currentColor"/>
                            <path d="M7 12.75H2C1.59 12.75 1.25 12.41 1.25 12C1.25 11.59 1.59 11.25 2 11.25H7C7.41 11.25 7.75 11.59 7.75 12C7.75 12.41 7.41 12.75 7 12.75Z" fill="currentColor"/>
                            <path d="M19.07 19.82C18.86 19.82 18.66 19.75 18.51 19.6C18.2 19.29 18.2 18.81 18.51 18.5L20.67 16.34C20.98 16.03 21.46 16.03 21.77 16.34C22.08 16.65 22.08 17.13 21.77 17.44L19.61 19.6C19.48 19.75 19.27 19.82 19.07 19.82Z" fill="currentColor"/>
                            <path d="M4.93 19.82C4.73 19.82 4.52 19.75 4.37 19.6L2.21 17.44C1.9 17.13 1.9 16.65 2.21 16.34C2.52 16.03 3 16.03 3.31 16.34L5.47 18.5C5.78 18.81 5.78 19.29 5.47 19.6C5.34 19.75 5.13 19.82 4.93 19.82Z" fill="currentColor"/>
                            <path d="M19.07 7.75C18.86 7.75 18.66 7.68 18.51 7.53L16.35 5.37C16.04 5.06 16.04 4.58 16.35 4.27C16.66 3.96 17.14 3.96 17.45 4.27L19.61 6.43C19.92 6.74 19.92 7.22 19.61 7.53C19.48 7.68 19.27 7.75 19.07 7.75Z" fill="currentColor"/>
                            <path d="M4.93 7.75C4.73 7.75 4.52 7.68 4.37 7.53C4.06 7.22 4.06 6.74 4.37 6.43L6.53 4.27C6.84 3.96 7.32 3.96 7.63 4.27C7.94 4.58 7.94 5.06 7.63 5.37L5.47 7.53C5.34 7.68 5.13 7.75 4.93 7.75Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Inovativní produkty</h3>
                    <p class="text-gray-300">Pečlivě vybrané produkty, které posouvají hranice možného a přinášejí budoucnost do přítomnosti.</p>
                </div>
                
                <div class="glass-effect rounded-2xl p-8 border-glow hover:transform hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 rounded-full neo-gradient flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22.75C11.37 22.75 10.73 22.55 10.23 22.16L4.58 18.08C3.58 17.33 2.98 16.19 2.98 14.93V9.07C2.98 7.8 3.58 6.66 4.58 5.92L10.23 1.84C11.24 1.04 12.77 1.04 13.78 1.84L19.43 5.92C20.43 6.67 21.03 7.81 21.03 9.07V14.93C21.03 16.2 20.43 17.34 19.43 18.08L13.78 22.16C13.28 22.55 12.64 22.75 12 22.75ZM12 2.75C11.69 2.75 11.37 2.84 11.13 3.02L5.48 7.1C4.93 7.52 4.61 8.14 4.61 8.82V14.68C4.61 15.36 4.93 15.98 5.48 16.4L11.13 20.48C11.62 20.85 12.39 20.85 12.88 20.48L18.53 16.4C19.08 15.98 19.4 15.36 19.4 14.68V8.82C19.4 8.14 19.08 7.52 18.53 7.1L12.88 3.02C12.64 2.84 12.32 2.75 12 2.75Z" fill="currentColor"/>
                            <path d="M12 12.75C9.38 12.75 7.25 10.62 7.25 8C7.25 5.38 9.38 3.25 12 3.25C14.62 3.25 16.75 5.38 16.75 8C16.75 10.62 14.62 12.75 12 12.75ZM12 4.75C10.21 4.75 8.75 6.21 8.75 8C8.75 9.79 10.21 11.25 12 11.25C13.79 11.25 15.25 9.79 15.25 8C15.25 6.21 13.79 4.75 12 4.75Z" fill="currentColor"/>
                            <path d="M8 17.75C7.59 17.75 7.25 17.41 7.25 17C7.25 15.08 9.41 13.5 12 13.5C14.59 13.5 16.75 15.08 16.75 17C16.75 17.41 16.41 17.75 16 17.75C15.59 17.75 15.25 17.41 15.25 17C15.25 16.09 13.9 15 12 15C10.1 15 8.75 16.09 8.75 17C8.75 17.41 8.41 17.75 8 17.75Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Prémiová zákaznická péče</h3>
                    <p class="text-gray-300">Náš tým specialistů je vždy připraven poskytnout odborné poradenství a podporu na nejvyšší úrovni.</p>
                </div>
                
                <div class="glass-effect rounded-2xl p-8 border-glow hover:transform hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 rounded-full neo-gradient flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C7.04 2 3 6.04 3 11C3 15.44 6.19 19.12 10.45 19.88V20.5H13.55V19.88C17.81 19.12 21 15.44 21 11C21 6.04 16.96 2 12 2ZM12 4C15.86 4 19 7.14 19 11C19 14.86 15.86 18 12 18C8.14 18 5 14.86 5 11C5 7.14 8.14 4 12 4ZM10.45 20.5V21H13.55V20.5H10.45ZM10.45 21V21.5H13.55V21H10.45ZM8 7.5L6.5 9L12 14.5L17.5 9L16 7.5L12 11.5L8 7.5Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Rychlé a bezpečné doručení</h3>
                    <p class="text-gray-300">Garantujeme expresní doručení všech objednávek s maximální péčí o bezpečnost vašich produktů.</p>
                </div>
            </div>
        </section>

        <section id="categories" class="mb-24">
            <div class="flex flex-col md:flex-row items-center justify-between mb-12">
                <div>
                    <h2 class="text-4xl font-bold mb-4 text-gradient logo-text">KATEGORIE PRODUKTŮ</h2>
                    <p class="text-gray-300 max-w-xl">Objevte širokou nabídku produktů v našich specializovaných kategoriích</p>
                </div>
                <div class="mt-6 md:mt-0">
                    <a href="#" class="cyber-btn px-6 py-3 rounded-lg bg-neo-purple bg-opacity-10 border border-neo-purple border-opacity-40 text-neo-blue font-medium hover:bg-opacity-20 transition-all flex items-center">
                        <span>Zobrazit všechny</span>
                        <svg class="w-5 h-5 ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.43 18.82C14.24 18.82 14.05 18.75 13.9 18.6C13.61 18.31 13.61 17.83 13.9 17.54L19.44 12L13.9 6.46C13.61 6.17 13.61 5.69 13.9 5.4C14.19 5.11 14.67 5.11 14.96 5.4L21.03 11.47C21.32 11.76 21.32 12.24 21.03 12.53L14.96 18.6C14.81 18.75 14.62 18.82 14.43 18.82Z" fill="currentColor"/>
                            <path d="M20.33 12.75H3.5C3.09 12.75 2.75 12.41 2.75 12C2.75 11.59 3.09 11.25 3.5 11.25H20.33C20.74 11.25 21.08 11.59 21.08 12C21.08 12.41 20.74 12.75 20.33 12.75Z" fill="currentColor"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($kategorie as $kategorie_item): ?>
                <div class="category-card glass-effect rounded-2xl overflow-hidden card-hover">
                    <div class="relative h-80">
                        <img src="images/<?php echo $kategorie_item['id']; ?>.jpg" 
                             alt="<?php echo htmlspecialchars($kategorie_item['nazev']); ?>" 
                             class="category-image w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-neo-black to-transparent opacity-80"></div>
                        
                        <!-- Holographic overlay effect -->
                        <div class="absolute inset-0 holographic-effect"></div>
                        
                        <div class="absolute inset-0 flex flex-col justify-end p-8">
                            <div class="mb-2">
                                <?php 
                                $kategorie_icons = [
                                    1 => '<svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 15V9C22 4 20 2 15 2H9C4 2 2 4 2 9V15C2 20 4 22 9 22H15C20 22 22 20 22 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.52 7.11H21.48" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.52 2.11V6.97" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.48 2.11V6.52" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                                    2 => '<svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.7499 22.5H13.2699C14.2299 22.5 14.8499 21.82 14.6699 20.99L14.2599 19.1H9.75991L9.34991 20.99C9.16991 21.77 9.84991 22.5 10.7499 22.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14.26 19.1001C11.9 18.8101 9.49004 18.8101 9.16004 19.1001" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.8199 10C17.2299 10 18.3599 8.73 18.3599 7.15C18.3599 5.57 17.2299 4.3 15.8199 4.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.17993 10C6.76993 10 5.63992 8.73 5.63992 7.15C5.63992 5.57 6.76993 4.3 8.17993 4.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 1.5V3.7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 11L12 19L21 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                                    3 => '<svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.70001 9.26001L12 12.33L17.26 9.28001" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 17.7701V12.3201" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.76 6.29001L7.56 8.07001C6.84 8.47001 6.23999 9.48001 6.23999 10.31V13.7C6.23999 14.53 6.83 15.54 7.56 15.94L10.76 17.72C11.44 18.1 12.56 18.1 13.25 17.72L16.45 15.94C17.17 15.54 17.77 14.53 17.77 13.7V10.3C17.77 9.47001 17.18 8.46001 16.45 8.06001L13.25 6.28001C12.56 5.90001 11.44 5.90001 10.76 6.29001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 15C22 18.87 18.87 22 15 22L16.05 20.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 9C2 5.13 5.13 2 9 2L7.95001 3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                                    4 => '<svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 19C15.31 19 18 16.31 18 13V8C18 4.69 15.31 2 12 2C8.69 2 6 4.69 6 8V13C6 16.31 8.69 19 12 19Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 23C10.9 23 9.8 22.88 8.8 22.65L9.57 20.22C10.15 20.41 10.77 20.51 11.38 20.51C12 20.51 12.61 20.41 13.19 20.22L13.96 22.65C12.96 22.88 11.9 23 12 23Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19.14 19.83L17.35 18.04C18.21 16.96 18.72 15.6 18.72 14.12H20.9C20.9 16.21 20.25 18.19 19.14 19.83Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.86 19.83C3.75 18.19 3.1 16.21 3.1 14.12H5.28C5.28 15.6 5.79 16.96 6.65 18.04L4.86 19.83Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                                    5 => '<svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.47998 3.84998V5.84998" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.52 3.84998V5.84998" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.73004 14.11H14.73" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.73004 17.94H12.47" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.52 18.2C16.6046 18.2 17.48 17.3246 17.48 16.24C17.48 15.1554 16.6046 14.28 15.52 14.28C14.4354 14.28 13.56 15.1554 13.56 16.24C13.56 17.3246 14.4354 18.2 15.52 18.2Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.49 19.7C18.33 19.53 18.13 19.27 17.97 19.07C17.47 18.45 16.73 18.09 15.91 18.09C14.66 18.09 13.6 18.78 13.1 19.79C13.02 19.93 12.95 20.08 12.91 20.25" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.17004 9.65002H20.83" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                                    6 => '<svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="1.5"/><path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                                ];
                                echo isset($kategorie_icons[$kategorie_item['id']]) ? $kategorie_icons[$kategorie_item['id']] : '';
                                ?>
                            </div>
                            <h3 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($kategorie_item['nazev']); ?></h3>
                            <p class="text-gray-300 mb-4">Objevte nejnovější produkty v této kategorii</p>
                            <a href="partials/produkty.php?kategorie_id=<?php echo $kategorie_item['id']; ?>" class="cyber-btn inline-flex items-center px-6 py-2 rounded-full bg-neo-blue bg-opacity-10 border border-neo-blue border-opacity-40 text-neo-blue hover:bg-opacity-20 transition-all">
                                <span>Prozkoumat</span>
                                <svg class="w-5 h-5 ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14.43 18.82C14.24 18.82 14.05 18.75 13.9 18.6C13.61 18.31 13.61 17.83 13.9 17.54L19.44 12L13.9 6.46C13.61 6.17 13.61 5.69 13.9 5.4C14.19 5.11 14.67 5.11 14.96 5.4L21.03 11.47C21.32 11.76 21.32 12.24 21.03 12.53L14.96 18.6C14.81 18.75 14.62 18.82 14.43 18.82Z" fill="currentColor"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <script>
            // Mobile menu toggle
            const menuToggle = document.getElementById('menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            
            menuToggle.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            // Three.js animation
            const canvas = document.getElementById('canvas-bg');
            const renderer = new THREE.WebGLRenderer({
                canvas,
                alpha: true
            });

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.z = 5;

            const particles = new THREE.BufferGeometry();
            const particleCount = 1000;
            const posArray = new Float32Array(particleCount * 3);

            for(let i = 0; i < particleCount * 3; i++) {
                posArray[i] = (Math.random() - 0.5) * 10;
            }

            particles.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
            const material = new THREE.PointsMaterial({
                size: 0.005,
                color: 0x00f2fe,
                transparent: true,
                opacity: 0.5
            });

            const particlesMesh = new THREE.Points(particles, material);
            scene.add(particlesMesh);

            function animate() {
                requestAnimationFrame(animate);
                particlesMesh.rotation.y += 0.001;
                renderer.render(scene, camera);
            }

            function resize() {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            }

            window.addEventListener('resize', resize);
            resize();
            animate();
        </script>
    </main>
</body>
</html>