<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/db_connect.php';

if (isset($_GET['pridat_id'])) {
    $produkt_id = $_GET['pridat_id'];

    if (!isset($_SESSION['kosik'])) {
        $_SESSION['kosik'] = [];
    }

    if (isset($_SESSION['kosik'][$produkt_id])) {
        $_SESSION['kosik'][$produkt_id]++;
    } else {
        $_SESSION['kosik'][$produkt_id] = 1;
    }

    $kosik_pocet = 0;
    foreach ($_SESSION['kosik'] as $mnozstvi) {
        $kosik_pocet += intval($mnozstvi);
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Produkt byl přidán do košíku!',
        'kosik_pocet' => $kosik_pocet
    ]);
    exit();
}

$kategorie_id = isset($_GET['kategorie_id']) ? $_GET['kategorie_id'] : 0;

$sql = "SELECT id, nazev, cena FROM produkty WHERE kategorie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $kategorie_id);
$stmt->execute();
$result = $stmt->get_result();
$produkty = $result->fetch_all(MYSQLI_ASSOC);

function spocitejKosik() {
    $pocet = 0;
    if (isset($_SESSION['kosik']) && is_array($_SESSION['kosik'])) {
        foreach ($_SESSION['kosik'] as $mnozstvi) {
            $pocet += intval($mnozstvi);
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
    <title>Produkty | FutureShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        /* Product grid responsive layout */
        .products-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        /* Product card responsive styles */
        .product-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 242, 254, 0.2);
        }

        .product-image-container {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
        }

        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Filters responsive layout */
        .filters-section {
            display: none;
            position: fixed;
            top: 4rem;
            left: 0;
            right: 0;
            background: rgba(10, 10, 26, 0.95);
            backdrop-filter: blur(12px);
            padding: 1rem;
            z-index: 40;
        }

        @media (min-width: 768px) {
            .filters-section {
                position: static;
                display: block;
                background: transparent;
                padding: 0;
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

        #canvas-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        /* Touch device optimizations */
        @media (hover: none) {
            .cyber-btn:active {
                transform: translateY(2px);
            }
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

        /* Responsive spacing */
        .p-responsive {
            padding: clamp(1rem, 3vw, 2rem);
        }

        .m-responsive {
            margin: clamp(1rem, 3vw, 2rem);
        }

        /* Responsive typography */
        .text-responsive {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
        }

        .heading-responsive {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
        }

        /* Animations */
        @keyframes gradient-shift {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }

        @keyframes border-animate {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }

        /* Add to cart button animation */
        .add-to-cart {
            transition: all 0.3s ease;
        }

        .add-to-cart:hover {
            transform: translateY(-2px);
        }

        .add-to-cart:active {
            transform: translateY(0);
        }

        /* Mobile filter toggle */
        .filter-toggle {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(0, 242, 254, 0.1);
            border: 1px solid rgba(0, 242, 254, 0.3);
            border-radius: 0.5rem;
            color: #00f2fe;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        @media (min-width: 768px) {
            .filter-toggle {
                display: none;
            }
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
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4 text-gradient logo-text">PRODUKTY</h2>
            <p class="text-xl text-gray-300">Objevte naši nabídku inovativních produktů</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($produkty as $produkt): ?>
            <div class="product-card glass-effect rounded-xl p-6 border-glow">
                <div class="mb-6">
                    <h3 class="text-xl font-bold mb-4 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                        <?php echo htmlspecialchars($produkt['nazev']); ?>
                    </h3>
                    <p class="text-2xl font-bold mb-6">
                        <?php echo number_format($produkt['cena'], 2, ',', ' ') . ' Kč'; ?>
                    </p>
                </div>
                <button class="w-full px-6 py-3 rounded-lg bg-neo-blue add-to-cart" 
                        data-produkt-id="<?php echo $produkt['id']; ?>">
                    <span>Přidat do košíku</span>
                </button>
            </div>
            <?php endforeach; ?>
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

        // Cart functionality
        $(document).ready(function() {
            $('.add-to-cart').click(function() {
                const button = $(this);
                const produktId = button.data('produkt-id');
                
                button.prop('disabled', true)
                      .html('<span class="inline-block animate-spin mr-2">↻</span>Přidávám...');
                
                $.ajax({
                    url: window.location.pathname,
                    type: 'GET',
                    data: { pridat_id: produktId },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            // Update all cart count elements
                            const countSpan = $('.-top-2.-right-2');
                            if (data.kosik_pocet > 0) {
                                if (countSpan.length) {
                                    countSpan.text(data.kosik_pocet);
                                } else {
                                    $('.cyber-btn').append(`
                                        <span class="absolute -top-2 -right-2 flex items-center justify-center w-6 h-6 bg-neo-purple text-white text-xs rounded-full">
                                            ${data.kosik_pocet}
                                        </span>
                                    `);
                                }
                            }
                            
                            button.html('<svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none"><path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Přidáno')
                                  .removeClass('neo-gradient')
                                  .addClass('bg-green-500');
                            
                            setTimeout(() => {
                                button.html('<svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none"><path d="M8 12H16M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3.5 3.5H4.89L6.64 13.795C6.89 15.075 8.01 16 9.315 16H16.685C17.99 16 19.11 15.075 19.36 13.795L20.5 7H5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Přidat do košíku</span>')
                                  .removeClass('bg-green-500')
                                  .addClass('neo-gradient')
                                  .prop('disabled', false);
                            }, 2000);
                        } else {
                            button.html('<svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none"><path d="M8 12H16M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3.5 3.5H4.89L6.64 13.795C6.89 15.075 8.01 16 9.315 16H16.685C17.99 16 19.11 15.075 19.36 13.795L20.5 7H5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Přidat do košíku</span>')
                              .prop('disabled', false);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error:', textStatus, errorThrown);
                        button.html('<svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none"><path d="M8 12H16M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3.5 3.5H4.89L6.64 13.795C6.89 15.075 8.01 16 9.315 16H16.685C17.99 16 19.11 15.075 19.36 13.795L20.5 7H5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Přidat do košíku</span>')
                          .prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>

