<?php
session_start();
require_once '../database/db_connect.php';


$jmeno_prijmeni = $ulice = $cislo_popisne = $psc = $mesto = '';
$doprava = 'osobni'; 
$errors = [];
$formular_odeslany = false;

$kosik_produkty = [];
$celkova_cena = 0;
$celkova_cena_dph = 0;

if (!isset($_SESSION['kosik']) || empty($_SESSION['kosik'])) {
    header("Location: kosik.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['jmeno_prijeni'])) {
        $errors['jmeno_prijeni'] = 'Zadejte jméno a příjmení';
    } else {
        $jmeno_prijeni = htmlspecialchars($_POST['jmeno_prijeni']);
    }

    if (empty($_POST['ulice'])) {
        $errors['ulice'] = 'Zadejte ulici';
    } else {
        $ulice = htmlspecialchars($_POST['ulice']);
    }

    if (empty($_POST['cislo_popisne'])) {
        $errors['cislo_popisne'] = 'Zadejte číslo popisné';
    } else if (!is_numeric($_POST['cislo_popisne'])) {
        $errors['cislo_popisne'] = 'Číslo popisné musí být číslo';
    } else {
        $cislo_popisne = htmlspecialchars($_POST['cislo_popisne']);
    }

    if (empty($_POST['mesto'])) {
        $errors['mesto'] = 'Zadejte město';
    } else {
        $mesto = htmlspecialchars($_POST['mesto']);
    }

    if (empty($_POST['psc'])) {
        $errors['psc'] = 'Zadejte PSČ';
    } else if (!preg_match('/^[0-9]{5}$/', $_POST['psc'])) {
        $errors['psc'] = 'PSČ musí být 5 číslic';
    } else {
        $psc = htmlspecialchars($_POST['psc']);
    }

    $doprava = isset($_POST['doprava']) ? $_POST['doprava'] : 'osobni';

    if (empty($errors)) {
        $_SESSION['objednavka'] = [
            'jmeno_prijeni' => $jmeno_prijeni,
            'ulice' => $ulice,
            'cislo_popisne' => $cislo_popisne,
            'mesto' => $mesto,
            'psc' => $psc,
            'doprava' => $doprava
        ];
        
        header("Location: souhrn.php");
        exit();
    }
    
    $formular_odeslany = true;
}

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
    <title>Pokladna | FutureShop</title>
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

        /* Form layout improvements */
        .checkout-form {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .checkout-form {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Responsive form elements */
        .form-input, .form-select {
            width: 100%;
            font-size: 16px;
            padding: 0.75rem 1rem;
        }

        /* Shipping options responsive layout */
        .shipping-options {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 640px) {
            .shipping-options {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Existing styles */
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 242, 254, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(72, 55, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 50% 50%, rgba(236, 56, 188, 0.05) 0%, transparent 50%);
            background-attachment: fixed;
            background-color: #080810;
            color: white;
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

        .form-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #00f2fe;
            box-shadow: 0 0 0 2px rgba(0, 242, 254, 0.2);
            outline: none;
        }

        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
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
                        <li>
                            <a href="kosik.php" class="hover:text-neo-blue transition-colors">
                                Košík (<?php echo $kosik_pocet; ?>)
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 pt-32 pb-12 flex-grow">
        <div class="max-w-4xl mx-auto">
            <?php if ($formular_odeslany && !empty($errors)): ?>
                <div class="glass-effect rounded-xl p-6 mb-8 border border-red-500/50">
                    <div class="flex items-center space-x-4 mb-4">
                        <svg class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <path d="M12 8v4m0 4h.01" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <h3 class="text-xl font-bold text-red-500">Opravte prosím následující chyby:</h3>
                    </div>
                    <ul class="space-y-2 text-red-400">
                        <?php foreach ($errors as $error): ?>
                            <li class="flex items-center space-x-2">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span><?php echo $error; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="glass-effect rounded-xl p-8">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold mb-4 text-gradient logo-text">DOKONČENÍ OBJEDNÁVKY</h2>
                    <p class="text-xl text-gray-300">Vyplňte své údaje pro doručení</p>
                </div>

                <form action="checkout.php" method="post" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="jmeno_prijeni" class="block text-sm font-medium text-gray-300 mb-2">
                                Jméno a příjmení *
                            </label>
                            <input type="text" id="jmeno_prijeni" name="jmeno_prijeni" 
                                   value="<?php echo htmlspecialchars($jmeno_prijeni); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3" required>
                        </div>

                        <div>
                            <label for="ulice" class="block text-sm font-medium text-gray-300 mb-2">
                                Ulice *
                            </label>
                            <input type="text" id="ulice" name="ulice" 
                                   value="<?php echo htmlspecialchars($ulice); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3" required>
                        </div>

                        <div>
                            <label for="cislo_popisne" class="block text-sm font-medium text-gray-300 mb-2">
                                Číslo popisné *
                            </label>
                            <input type="text" id="cislo_popisne" name="cislo_popisne" 
                                   value="<?php echo htmlspecialchars($cislo_popisne); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3" required>
                        </div>

                        <div>
                            <label for="mesto" class="block text-sm font-medium text-gray-300 mb-2">
                                Město *
                            </label>
                            <input type="text" id="mesto" name="mesto" 
                                   value="<?php echo htmlspecialchars($mesto); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3" required>
                        </div>

                        <div>
                            <label for="psc" class="block text-sm font-medium text-gray-300 mb-2">
                                PSČ *
                            </label>
                            <input type="text" id="psc" name="psc" 
                                   value="<?php echo htmlspecialchars($psc); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3" 
                                   maxlength="5" placeholder="12345" required>
                        </div>
                    </div>

                    <div class="mt-12">
                        <h3 class="text-2xl font-bold mb-6 bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent">
                            Způsob dopravy
                        </h3>
                        <div class="space-y-4">
                            <div class="shipping-option glass-effect rounded-xl overflow-hidden">
                                <input type="radio" name="doprava" id="osobni" value="osobni" 
                                       <?php echo $doprava === 'osobni' ? 'checked' : ''; ?> 
                                       class="hidden">
                                <label for="osobni" class="flex items-center p-6 cursor-pointer transition-all">
                                    <div class="w-6 h-6 rounded-full border-2 border-neo-blue mr-4 flex items-center justify-center">
                                        <div class="w-3 h-3 rounded-full bg-neo-blue opacity-0 transition-opacity"></div>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold">Osobní odběr</h4>
                                        <p class="text-gray-400">Zdarma</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="shipping-option glass-effect rounded-xl overflow-hidden">
                                <input type="radio" name="doprava" id="prepravce" value="prepravce" 
                                       <?php echo $doprava === 'prepravce' ? 'checked' : ''; ?> 
                                       class="hidden">
                                <label for="prepravce" class="flex items-center p-6 cursor-pointer transition-all">
                                    <div class="w-6 h-6 rounded-full border-2 border-neo-blue mr-4 flex items-center justify-center">
                                        <div class="w-3 h-3 rounded-full bg-neo-blue opacity-0 transition-opacity"></div>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold">Přepravní společnost</h4>
                                        <p class="text-gray-400">129 Kč</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="glass-effect rounded-xl p-6 mt-8">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-gray-300">
                                <span>Mezisoučet:</span>
                                <span><?php echo number_format($celkova_cena, 2, ',', ' '); ?> Kč</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-300">
                                <span>DPH (21%):</span>
                                <span><?php echo number_format($celkova_cena_dph - $celkova_cena, 2, ',', ' '); ?> Kč</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-300" id="doprava-row">
                                <span>Doprava:</span>
                                <span id="doprava-cena">0,00 Kč</span>
                            </div>
                            <div class="flex justify-between items-center text-xl font-bold pt-4 border-t border-gray-700">
                                <span>Celková cena:</span>
                                <span class="bg-gradient-to-r from-neo-blue to-neo-purple bg-clip-text text-transparent" id="celkova-cena">
                                    <?php echo number_format($celkova_cena_dph, 2, ',', ' '); ?> Kč
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-8">
                        <button type="submit" 
                                class="cyber-btn neo-gradient px-8 py-4 rounded-xl font-bold text-lg hover:opacity-90 transition-all flex items-center space-x-3">
                            <span>Pokračovat k souhrnu</span>
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
        // Radio button animation
        const radioInputs = document.querySelectorAll('input[type="radio"]');
        radioInputs.forEach(input => {
            input.addEventListener('change', () => {
                document.querySelectorAll('input[type="radio"] + label .w-3').forEach(dot => {
                    dot.style.opacity = '0';
                });
                if (input.checked) {
                    input.nextElementSibling.querySelector('.w-3').style.opacity = '1';
                }
            });
        });

        // Price calculation
        $(document).ready(function() {
            let cenaSDPH = <?php echo $celkova_cena_dph; ?>;
            const dopravaCena = 129;
            
            function aktualizujCenu() {
                let celkovaCena = cenaSDPH;
                const vybranaDopravaElement = $('input[name="doprava"]:checked');
                
                if (vybranaDopravaElement.val() === 'prepravce') {
                    celkovaCena += dopravaCena;
                    $('#doprava-cena').text('129,00 Kč');
                } else {
                    $('#doprava-cena').text('0,00 Kč');
                }
                
                $('#celkova-cena').text(celkovaCena.toLocaleString('cs-CZ', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).replace('.', ',') + ' Kč');
            }
            
            aktualizujCenu();
            $('input[name="doprava"]').change(aktualizujCenu);
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
</body>
</html>