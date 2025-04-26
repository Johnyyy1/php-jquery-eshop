<?php
session_start();
require_once '../database/db_connect.php';

if (!isset($_SESSION['objednavka']) || !isset($_SESSION['kosik']) || empty($_SESSION['kosik'])) {
    header("Location: kosik.php");
    exit();
}

$objednavka = $_SESSION['objednavka'];
$jmeno_prijmeni = $objednavka['jmeno_prijmeni'];
$ulice = $objednavka['ulice'];
$cislo_popisne = $objednavka['cislo_popisne'];
$mesto = $objednavka['mesto'];
$psc = $objednavka['psc'];
$doprava = $objednavka['doprava'];

$kosik_produkty = [];
$celkova_cena = 0;
$celkova_cena_dph = 0;

foreach ($_SESSION['kosik'] as $produkt_id => $mnozstvi) {
    $sql = "SELECT id, nazev, cena FROM produkty WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $produkt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produkt = $result->fetch_assoc();
    
    if ($produkt) {
        $produkt['mnozstvi'] = $mnozstvi;
        $kosik_produkty[] = $produkt;
        $celkova_cena += $produkt['cena'] * $mnozstvi;
        $celkova_cena_dph += ($produkt['cena'] * 1.21) * $mnozstvi;
    }
}

$doprava_cena = ($doprava === 'prepravce') ? 129 : 0;
$celkova_cena_s_dopravou = $celkova_cena + $doprava_cena;
$celkova_cena_s_dopravou_dph = $celkova_cena_dph + $doprava_cena;

$objednavka_odeslana = false;
$objednavka_cislo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql_objednavka = "INSERT INTO objednavky (jmeno_prijmeni, ulice, cislo_popisne, mesto, psc, doprava, stav) 
    VALUES (?, ?, ?, ?, ?, ?, 'čeká na vyřízení')";
    $stmt_objednavka = $conn->prepare($sql_objednavka);
    $stmt_objednavka->bind_param("ssssss", $jmeno_prijmeni, $ulice, $cislo_popisne, $mesto, $psc, $doprava);
    
    if ($stmt_objednavka->execute()) {
        $objednavka_id = $conn->insert_id;
        $objednavka_cislo = date('Ymd') . str_pad($objednavka_id, 5, '0', STR_PAD_LEFT);
        
        $sql_polozky = "INSERT INTO objednavky_polozky (objednavka_id, produkt_id, mnozstvi, cena_kus) VALUES (?, ?, ?, ?)";
        $stmt_polozky = $conn->prepare($sql_polozky);
        
        foreach ($kosik_produkty as $produkt) {
            $stmt_polozky->bind_param("iiid", $objednavka_id, $produkt['id'], $produkt['mnozstvi'], $produkt['cena']);
            $stmt_polozky->execute();
        }
        
        unset($_SESSION['kosik']);
        unset($_SESSION['objednavka']);
        
        $objednavka_odeslana = true;
    }
}

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
    <title>Souhrn objednávky | FutureShop</title>
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

        /* Order summary layout */
        .order-summary {
            display: grid;
            gap: 2rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .order-summary {
                grid-template-columns: 2fr 1fr;
            }
        }

        /* Order items responsive */
        .order-item {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
            padding: 1rem;
        }

        @media (min-width: 640px) {
            .order-item {
                grid-template-columns: 1fr auto;
                align-items: center;
                padding: 1.5rem;
            }

            .order-item-info {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
        }

        /* Success animation */
        .success-checkmark {
            width: clamp(4rem, 10vw, 6rem);
            height: clamp(4rem, 10vw, 6rem);
            margin: 0 auto 1.5rem;
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

        /* Order item animations */
        .order-item {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .order-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 242, 254, 0.1), transparent);
            transition: 0.5s;
        }

        .order-item:hover::before {
            left: 100%;
        }

        .success-checkmark {
            animation: scale-up 0.5s ease-in-out;
        }

        /* Mobile navigation improvements */
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

        /* Customer info responsive */
        .customer-info {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 640px) {
            .customer-info {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Touch device optimizations */
        @media (hover: none) {
            .cyber-btn:active {
                transform: translateY(2px);
            }
        }

        /* Responsive typography */
        .text-responsive {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
        }

        .heading-responsive {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
        }

        /* Responsive spacing */
        .p-responsive {
            padding: clamp(1rem, 3vw, 2rem);
        }

        .m-responsive {
            margin: clamp(1rem, 3vw, 2rem);
        }

        /* Price display responsive */
        .price-display {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        @media (min-width: 640px) {
            .price-display {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        /* Animations */
        @keyframes gradient-shift {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }

        @keyframes scale-up {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
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
                    <ul class="flex space-x-6">
                        <li><a href="../index.php" class="hover:text-neo-blue transition-colors">Domů</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 pt-32 pb-12 flex-grow">
        <div class="max-w-4xl mx-auto">
            <?php if ($objednavka_odeslana): ?>
                <div class="glass-effect rounded-xl p-8 text-center">
                    <div class="success-checkmark mb-8">
                        <svg class="w-24 h-24 mx-auto text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 4L12 14.01l-3-3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h2 class="text-4xl font-bold mb-4 text-gradient logo-text">DĚKUJEME ZA VAŠI OBJEDNÁVKU</h2>
                    <p class="text-xl text-gray-300 mb-8">
                        Vaše objednávka byla úspěšně dokončena. Číslo objednávky: <?php echo $objednavka_id; ?>
                    </p>
                    <a href="../index.php" 
                       class="cyber-btn neo-gradient px-8 py-4 rounded-xl inline-flex items-center space-x-3 hover:opacity-90 transition-all">
                        <span>Zpět na hlavní stránku</span>
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            <?php else: ?>
                <div class="glass-effect rounded-xl p-8">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold mb-4 text-gradient logo-text">SOUHRN OBJEDNÁVKY</h2>
                        <p class="text-xl text-gray-300">Zkontrolujte prosím údaje své objednávky</p>
                    </div>

                    <div class="space-y-8">
                        <div class="glass-effect rounded-xl p-6">
                            <h3 class="text-2xl font-bold mb-4 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                                Doručovací údaje
                            </h3>
                            <div class="grid grid-cols-2 gap-4 text-gray-300">
                                <div>
                                    <p class="font-medium">Jméno a příjmení:</p>
                                    <p><?php echo htmlspecialchars($jmeno_prijmeni); ?></p>
                                </div>
                                <div>
                                    <p class="font-medium">Ulice:</p>
                                    <p><?php echo htmlspecialchars($ulice . ' ' . $cislo_popisne); ?></p>
                                </div>
                                <div>
                                    <p class="font-medium">Město:</p>
                                    <p><?php echo htmlspecialchars($mesto); ?></p>
                                </div>
                                <div>
                                    <p class="font-medium">PSČ:</p>
                                    <p><?php echo htmlspecialchars($psc); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-2xl font-bold bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                                Objednané položky
                            </h3>
                            <?php foreach ($kosik_produkty as $produkt): ?>
                                <div class="order-item glass-effect rounded-xl p-6 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-bold text-neo-blue">
                                            <?php echo htmlspecialchars($produkt['nazev']); ?>
                                        </h4>
                                        <p class="text-gray-400">Počet kusů: <?php echo $produkt['mnozstvi']; ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                                            <?php echo number_format($produkt['cena'] * $produkt['mnozstvi'], 2, ',', ' '); ?> Kč
                                        </p>
                                        <p class="text-sm text-gray-400">
                                            s DPH: <?php echo number_format(($produkt['cena'] * $produkt['mnozstvi'] * 1.21), 2, ',', ' '); ?> Kč
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="glass-effect rounded-xl p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-gray-300">
                                    <span>Mezisoučet:</span>
                                    <span><?php echo number_format($celkova_cena, 2, ',', ' '); ?> Kč</span>
                                </div>
                                <div class="flex justify-between items-center text-gray-300">
                                    <span>DPH (21%):</span>
                                    <span><?php echo number_format($celkova_cena_dph - $celkova_cena, 2, ',', ' '); ?> Kč</span>
                                </div>
                                <div class="flex justify-between items-center text-gray-300">
                                    <span>Doprava (<?php echo $doprava === 'osobni' ? 'Osobní odběr' : 'Přepravce'; ?>):</span>
                                    <span><?php echo $doprava === 'osobni' ? '0,00' : '129,00'; ?> Kč</span>
                                </div>
                                <div class="flex justify-between items-center text-xl font-bold pt-4 border-t border-gray-700">
                                    <span>Celková cena:</span>
                                    <span class="bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                                        <?php echo number_format($celkova_cena_dph + ($doprava === 'prepravce' ? 129 : 0), 2, ',', ' '); ?> Kč
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="checkout.php" 
                               class="cyber-btn px-8 py-4 rounded-xl font-bold text-lg border border-neo-blue hover:bg-neo-blue/10 transition-all">
                                Upravit údaje
                            </a>
                            <form action="souhrn.php" method="post" class="inline">
                                <button type="submit" 
                                        class="cyber-btn neo-gradient px-8 py-4 rounded-xl font-bold text-lg hover:opacity-90 transition-all flex items-center space-x-3">
                                    <span>Dokončit objednávku</span>
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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