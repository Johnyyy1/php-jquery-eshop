<?php
session_start();
require_once '../database/db_connect.php';

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
    <title>O nás | FutureShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
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
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&display=swap');
        
        /* Base responsive styles */
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

        /* Responsive layout for features */
        .feature-grid {
            display: grid;
            gap: 2rem;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        /* Timeline responsive adjustments */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        @media (max-width: 639px) {
            .timeline {
                padding-left: 1.5rem;
            }

            .timeline-item::before {
                left: -24px;
            }

            .timeline-dot {
                left: -30px;
            }
        }

        /* Base styles */
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 242, 254, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(72, 55, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 50% 50%, rgba(236, 56, 188, 0.05) 0%, transparent 50%);
            background-attachment: fixed;
        }

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

        #canvas-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -33px;
            top: 0;
            width: 2px;
            height: 100%;
            background: linear-gradient(to bottom, #00f2fe, #4837ff);
        }

        .timeline-dot {
            position: absolute;
            left: -39px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00f2fe, #4837ff);
            border: 2px solid #080810;
        }

        .feature-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .feature-card:hover {
            transform: translateY(-8px);
        }

        @keyframes gradient-shift {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .animate-float {
            animation: floating 3s ease-in-out infinite;
        }

        /* Mobile menu styles */
        @media (max-width: 639px) {
            .nav-desktop {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

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
        }

        /* Responsive typography */
        .text-responsive {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
        }

        .heading-responsive {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
        }

        /* Touch device optimizations */
        @media (hover: none) {
            .cyber-btn:active {
                transform: translateY(2px);
            }
        }

        /* Responsive spacing */
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
                <a href="../index.php" class="flex items-center group">
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
                
                <nav>
                    <ul class="flex space-x-6 items-center">
                        <li><a href="../index.php" class="hover:text-neo-blue transition-colors">Domů</a></li>
                        <li><a href="contact.php" class="hover:text-neo-blue transition-colors">Kontakt</a></li>
                        <li>
                            <a href="kosik.php" class="relative cyber-btn font-medium flex items-center px-5 py-2 rounded-full border-glow hover:bg-neo-purple hover:bg-opacity-10 transition-all">
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
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 pt-32 pb-12 flex-grow">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 text-gradient logo-text">O NÁS</h2>
                <p class="text-xl text-gray-300">Objevte příběh budoucnosti nakupování</p>
            </div>

            <div class="glass-effect rounded-xl p-8 mb-12">
                <div class="prose prose-invert max-w-none">
                    <p class="text-lg leading-relaxed mb-6">
                        FutureShop je více než jen e-shop – jsme vaším průvodcem světem zítřka. Naše platforma vznikla s jediným cílem: 
                        přinášet nejmodernější technologie a inovativní produkty přímo k vám, s důrazem na kvalitu, design a udržitelnost.
                    </p>
                    <p class="text-lg leading-relaxed mb-6">
                        Věříme, že budoucnost nakupování by měla být stejně vzrušující jako produkty, které prodáváme. Proto neustále 
                        inovujeme náš přístup k e-commerce a snažíme se vytvářet jedinečné nákupní zážitky.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <div class="feature-card glass-effect rounded-xl p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-6 rounded-full neo-gradient flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">Inovace</h3>
                    <p class="text-gray-300">Neustále hledáme nové způsoby, jak zlepšit váš nákupní zážitek</p>
                </div>

                <div class="feature-card glass-effect rounded-xl p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-6 rounded-full neo-gradient flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 15C15.866 15 19 11.866 19 8C19 4.13401 15.866 1 12 1C8.13401 1 5 4.13401 5 8C5 11.866 8.13401 15 12 15Z" stroke-width="2"/>
                            <path d="M12 15V23" stroke-width="2"/>
                            <path d="M7 20H17" stroke-width="2"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">Kvalita</h3>
                    <p class="text-gray-300">Pečlivě vybíráme každý produkt v našem katalogu</p>
                </div>

                <div class="feature-card glass-effect rounded-xl p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-6 rounded-full neo-gradient flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke-width="2"/>
                            <path d="M12 16V12" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 8H12.01" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">Podpora</h3>
                    <p class="text-gray-300">Náš tým je tu pro vás 24/7</p>
                </div>
            </div>

            <div class="glass-effect rounded-xl p-8">
                <h3 class="text-2xl font-bold mb-8 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">Naše historie</h3>
                
                <div class="relative pl-12">
                    <div class="timeline-item pb-8">
                        <div class="timeline-dot"></div>
                        <h4 class="text-xl font-bold mb-2">2023</h4>
                        <p class="text-gray-300">Založení společnosti s vizí přinést budoucnost do každé domácnosti</p>
                    </div>

                    <div class="timeline-item pb-8">
                        <div class="timeline-dot"></div>
                        <h4 class="text-xl font-bold mb-2">2024</h4>
                        <p class="text-gray-300">Rozšíření portfolia o exkluzivní produkty a technologické novinky</p>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <h4 class="text-xl font-bold mb-2">2025</h4>
                        <p class="text-gray-300">Implementace nejmodernějších technologií pro lepší nákupní zážitek</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="glass-effect mt-auto py-6">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400">&copy; 2025 FutureShop | Všechna práva vyhrazena</p>
        </div>
    </footer>

    <script>
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
</body>
</html>