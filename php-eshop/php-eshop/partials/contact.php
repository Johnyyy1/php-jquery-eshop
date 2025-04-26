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

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jmeno = isset($_POST['jmeno']) ? htmlspecialchars($_POST['jmeno']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $zprava = isset($_POST['zprava']) ? htmlspecialchars($_POST['zprava']) : '';
    
    if (empty($jmeno) || empty($email) || empty($zprava)) {
        $message = 'Prosím vyplňte všechna pole.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Zadejte platnou e-mailovou adresu.';
        $messageType = 'error';
    } else {
        // Here you would typically send the email or save to database
        // For demo purposes, we'll just show a success message
        $message = 'Děkujeme za vaši zprávu! Budeme vás kontaktovat co nejdříve.';
        $messageType = 'success';
        
        // Reset form
        $jmeno = $email = $zprava = '';
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt | FutureShop</title>
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

        /* Contact layout responsive styles */
        .contact-layout {
            display: grid;
            gap: 2rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .contact-layout {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Contact info cards responsive */
        .contact-info-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 640px) {
            .contact-info-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        /* Contact form responsive */
        .contact-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        @media (min-width: 640px) {
            .form-row {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
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

        .contact-form input,
        .contact-form textarea {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 16px;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #00f2fe;
            box-shadow: 0 0 0 2px rgba(0, 242, 254, 0.2);
            outline: none;
        }

        .contact-info-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            padding: clamp(1rem, 3vw, 2rem);
        }

        .contact-info-card:hover {
            transform: translateY(-5px);
        }

        #canvas-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        /* Mobile navigation improvements */
        @media (max-width: 639px) {
            .nav-desktop {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
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

        /* Submit button animation */
        .submit-btn {
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        /* Responsive spacing */
        .p-responsive {
            padding: clamp(1rem, 3vw, 2rem);
        }

        .m-responsive {
            margin: clamp(1rem, 3vw, 2rem);
        }

        /* Animations */
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
                        <li><a href="about.php" class="hover:text-neo-blue transition-colors">O nás</a></li>
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
                <h2 class="text-4xl font-bold mb-4 text-gradient logo-text">KONTAKTUJTE NÁS</h2>
                <p class="text-xl text-gray-300">Jsme tu pro vás 24/7</p>
            </div>

            <?php if ($message): ?>
            <div class="glass-effect rounded-xl p-6 mb-8 <?php echo $messageType === 'success' ? 'border-green-500/50' : 'border-red-500/50'; ?> border">
                <div class="flex items-center space-x-4">
                    <?php if ($messageType === 'success'): ?>
                    <svg class="w-6 h-6 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 4L12 14.01l-3-3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php else: ?>
                    <svg class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path d="M15 9l-6 6M9 9l6 6" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <?php endif; ?>
                    <p class="<?php echo $messageType === 'success' ? 'text-green-500' : 'text-red-500'; ?>">
                        <?php echo $message; ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <div class="contact-layout mb-12">
                <div class="contact-info-grid">
                    <div class="contact-info-card glass-effect rounded-xl p-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-6 rounded-full neo-gradient flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z" stroke-width="2"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">Telefon</h3>
                        <p class="text-gray-300">+420 123 456 789</p>
                    </div>

                    <div class="contact-info-card glass-effect rounded-xl p-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-6 rounded-full neo-gradient flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 8L10.89 13.26C11.2187 13.4793 11.6049 13.5963 12 13.5963C12.3951 13.5963 12.7813 13.4793 13.11 13.26L21 8M5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">Email</h3>
                        <p class="text-gray-300">info@futureshop.cz</p>
                    </div>

                    <div class="contact-info-card glass-effect rounded-xl p-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-6 rounded-full neo-gradient flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17.657 16.657L13.414 20.9C13.039 21.2746 12.5303 21.4851 12 21.4851C11.4697 21.4851 10.961 21.2746 10.586 20.9L6.343 16.657C3.21895 13.5329 3.21895 8.46708 6.343 5.34304C9.46705 2.21899 14.533 2.21899 17.657 5.34304C20.781 8.46708 20.781 13.5329 17.657 16.657Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">Adresa</h3>
                        <p class="text-gray-300">Technologická 123<br>150 00 Praha 5</p>
                    </div>
                </div>
            </div>

            <div class="glass-effect rounded-xl p-8">
                <form method="post" class="contact-form space-y-6">
                    <div>
                        <label for="jmeno" class="block text-sm font-medium text-gray-300 mb-2">Jméno *</label>
                        <input type="text" id="jmeno" name="jmeno" required
                               value="<?php echo isset($jmeno) ? $jmeno : ''; ?>"
                               class="w-full rounded-lg px-4 py-3">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo isset($email) ? $email : ''; ?>"
                               class="w-full rounded-lg px-4 py-3">
                    </div>

                    <div>
                        <label for="zprava" class="block text-sm font-medium text-gray-300 mb-2">Zpráva *</label>
                        <textarea id="zprava" name="zprava" rows="5" required
                                  class="w-full rounded-lg px-4 py-3"><?php echo isset($zprava) ? $zprava : ''; ?></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="cyber-btn neo-gradient px-8 py-4 rounded-xl font-bold text-lg hover:opacity-90 transition-all flex items-center space-x-3">
                            <span>Odeslat zprávu</span>
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 2L11 13" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
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