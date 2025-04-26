<?php
session_start();
require_once '../database/db_connect.php';

if (!isset($_SESSION['kosik'])) {
    $_SESSION['kosik'] = [];
}

function calculateTotals($conn) {
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
    
    return [
        'produkty' => $kosik_produkty,
        'celkova_cena' => $celkova_cena,
        'celkova_cena_dph' => $celkova_cena_dph
    ];
}

// Handle AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (isset($_GET['pridat_id']) || isset($_GET['odstranit_id'])) {
    $produkt_id = isset($_GET['pridat_id']) ? $_GET['pridat_id'] : $_GET['odstranit_id'];
    $is_increment = isset($_GET['pridat_id']);

    if ($is_increment) {
        if (isset($_SESSION['kosik'][$produkt_id])) {
            $_SESSION['kosik'][$produkt_id]++;
        } else {
            $_SESSION['kosik'][$produkt_id] = 1;
        }
    } else {
        if (isset($_SESSION['kosik'][$produkt_id])) {
            $_SESSION['kosik'][$produkt_id]--;
            if ($_SESSION['kosik'][$produkt_id] <= 0) {
                unset($_SESSION['kosik'][$produkt_id]);
            }
        }
    }

    if ($isAjax) {
        $totals = calculateTotals($conn);
        $response = [
            'success' => true,
            'kosik_pocet' => array_sum($_SESSION['kosik']),
            'total_price' => number_format($totals['celkova_cena_dph'], 2, ',', ' '),
            'subtotal' => number_format($totals['celkova_cena'], 2, ',', ' '),
            'dph' => number_format($totals['celkova_cena_dph'] - $totals['celkova_cena'], 2, ',', ' ')
        ];
        
        // Add item-specific prices if the item still exists
        if (isset($_SESSION['kosik'][$produkt_id])) {
            foreach ($totals['produkty'] as $produkt) {
                if ($produkt['id'] == $produkt_id) {
                    $response['item_price'] = number_format($produkt['cena'] * $produkt['mnozstvi'], 2, ',', ' ');
                    $response['item_price_dph'] = number_format(($produkt['cena'] * $produkt['mnozstvi'] * 1.21), 2, ',', ' ');
                    break;
                }
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        header("Location: kosik.php");
        exit();
    }
}

// Rest of the existing code...
$kosik_produkty = [];
$celkova_cena = 0;
$celkova_cena_dph = 0;

if (!empty($_SESSION['kosik'])) {
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
        } else {
            unset($_SESSION['kosik'][$produkt_id]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Košík | FutureShop</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        /* Cart item responsive styles */
        .cart-items {
            display: grid;
            gap: 1rem;
        }

        .cart-item {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
            padding: 1rem;
        }

        @media (min-width: 640px) {
            .cart-item {
                grid-template-columns: auto 1fr auto;
                align-items: center;
                padding: 1.5rem;
            }
        }

        /* Quantity controls responsive */
        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        @media (max-width: 639px) {
            .quantity-controls {
                margin-top: 1rem;
            }
        }

        /* Summary section responsive */
        .cart-summary {
            position: sticky;
            top: 5rem;
            margin-top: 2rem;
        }

        @media (min-width: 1024px) {
            .cart-layout {
                display: grid;
                grid-template-columns: 1fr 320px;
                gap: 2rem;
            }

            .cart-summary {
                margin-top: 0;
            }
        }

        /* Empty cart responsive */
        .empty-cart {
            text-align: center;
            padding: clamp(2rem, 5vw, 4rem);
        }

        .empty-cart svg {
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

        /* Cart item animations */
        .cart-item {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .cart-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 242, 254, 0.1), transparent);
            transition: 0.5s;
        }

        .cart-item:hover::before {
            left: 100%;
        }

        /* Mobile optimizations */
        @media (max-width: 639px) {
            .product-info {
                text-align: center;
            }

            .price-info {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 1rem;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) {
            .quantity-btn:active {
                transform: scale(0.95);
            }
        }

        /* Animations */
        @keyframes gradient-shift {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }

        .quantity-btn {
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            transform: scale(1.1);
        }

        /* Canvas background */
        #canvas-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        /* Checkout button */
        .checkout-btn {
            position: relative;
            overflow: hidden;
        }

        .checkout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .checkout-btn:hover::before {
            left: 100%;
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
        <?php if (!empty($kosik_produkty)): ?>
            <div class="glass-effect rounded-xl p-8">
                <div class="flex justify-between items-center mb-12">
                    <div class="text-center">
                        <h2 class="text-4xl font-bold mb-4 text-gradient logo-text">VÁŠ KOŠÍK</h2>
                        <p class="text-xl text-gray-300">Dokončete svůj nákup budoucnosti</p>
                    </div>
                    <a href="?vymazat_kosik=1" 
                       class="cyber-btn bg-red-500 hover:bg-red-600 px-6 py-3 rounded-xl font-bold text-lg transition-colors flex items-center space-x-2"
                       onclick="return confirm('Opravdu chcete vyprázdnit celý košík?')">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7M10 11V17M14 11V17M15 7V4C15 3.44772 14.5523 3 14 3H10C9.44772 3 9 3.44772 9 4V7M4 7H20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Vyprázdnit košík</span>
                    </a>
                </div>
                
                <div class="space-y-6 mb-12">
                    <?php foreach ($kosik_produkty as $produkt): ?>
                        <div class="cart-item glass-effect rounded-xl p-6 flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold mb-2 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                                    <?php echo htmlspecialchars($produkt['nazev']); ?>
                                </h3>
                                <p class="text-gray-300">
                                    <?php echo number_format($produkt['cena'], 2, ',', ' ') . ' Kč'; ?>
                                </p>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <button type="button"
                                   class="w-8 h-8 flex items-center justify-center rounded-full decrease-quantity"
                                   data-produkt-id="<?php echo $produkt['id']; ?>">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M5 12H19" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <span class="text-xl font-bold w-8 text-center">
                                    <?php echo $produkt['mnozstvi']; ?>
                                </span>
                                <button type="button"
                                   class="w-8 h-8 flex items-center justify-center rounded-full increase-quantity"
                                   data-produkt-id="<?php echo $produkt['id']; ?>">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M12 5V19M5 12H19" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <a href="?smazat_produkt=<?php echo $produkt['id']; ?>" 
                                   class="w-8 h-8 flex items-center justify-center rounded-full bg-red-500"
                                   onclick="return confirm('Opravdu chcete odstranit tento produkt z košíku?')">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M6 6L18 18M6 18L18 6" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </a>
                            </div>
                            <div class="text-right min-w-[150px]">
                                <p class="text-xl font-bold bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent item-price">
                                    <?php echo number_format($produkt['cena'] * $produkt['mnozstvi'], 2, ',', ' ') . ' Kč'; ?>
                                </p>
                                <p class="text-sm text-gray-400 item-price-dph">
                                    s DPH: <?php echo number_format(($produkt['cena'] * $produkt['mnozstvi'] * 1.21), 2, ',', ' ') . ' Kč'; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="glass-effect rounded-xl p-6 mb-8">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-gray-300">
                            <span>Mezisoučet:</span>
                            <span class="mezisoucet"><?php echo number_format($celkova_cena, 2, ',', ' ') . ' Kč'; ?></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-300">
                            <span>DPH (21%):</span>
                            <span class="dph"><?php echo number_format($celkova_cena_dph - $celkova_cena, 2, ',', ' ') . ' Kč'; ?></span>
                        </div>
                        <div class="flex justify-between items-center text-xl font-bold pt-4 border-t border-gray-700">
                            <span>Celková cena:</span>
                            <span class="celkova-cena bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                                <?php echo number_format($celkova_cena_dph, 2, ',', ' ') . ' Kč'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="checkout.php" 
                       class="cyber-btn neo-gradient px-8 py-4 rounded-xl font-bold text-lg hover:opacity-90 transition-all flex items-center space-x-3">
                        <span>Pokračovat k pokladně</span>
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="glass-effect rounded-xl p-12 text-center max-w-2xl mx-auto animate-float">
                <svg class="w-24 h-24 mx-auto mb-6 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M20.01 18.6L19.08 6.32C19.02 5.55 18.37 4.95 17.6 4.95H16.49V4.75C16.49 2.68 14.81 1 12.74 1C10.67 1 8.99 2.68 8.99 4.75V4.95H7.88C7.11 4.95 6.46 5.55 6.4 6.32L5.47 18.6C5.4 19.65 6.25 20.55 7.31 20.55H18.19C19.24 20.55 20.09 19.65 20.01 18.6Z" stroke-width="2"/>
                    <path d="M12 10V14M10 12H14" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <h2 class="text-3xl font-bold mb-4 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                    Váš košík je prázdný
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    Prozkoumejte naši nabídku a objevte produkty budoucnosti
                </p>
                <a href="../index.php" 
                   class="cyber-btn neo-gradient px-8 py-4 rounded-xl inline-flex items-center space-x-3 hover:opacity-90 transition-all">
                    <span>Zpět do obchodu</span>
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
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
    <script src="../js/script.js"></script>
</body>
</html>
