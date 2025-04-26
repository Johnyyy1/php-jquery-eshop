<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokončení objednávky</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        .shipping-option {
            transition: all 0.3s ease;
        }

        .shipping-option:hover {
            border-color: #00f2fe;
            background: rgba(0, 242, 254, 0.1);
        }

        .shipping-option input:checked + label {
            border-color: #00f2fe;
            background: rgba(0, 242, 254, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #00f2fe 0%, #4837ff 100%);
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<?php
session_start();
require_once '../database/db_connect.php';

if (!isset($_SESSION['kosik']) || empty($_SESSION['kosik'])) {
    header("Location: kosik.php");
    exit();
}

$jmeno_prijmeni = $ulice = $cislo_popisne = $psc = $mesto = '';
$doprava = 'osobni'; 
$errors = [];
$formular_odeslany = false;

$kosik_produkty = [];
$celkova_cena = 0;
$celkova_cena_dph = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate name
    if (empty($_POST['jmeno_prijmeni'])) {
        $errors['jmeno_prijmeni'] = 'Zadejte jméno a příjmení';
    } else {
        $jmeno_prijmeni = filter_var($_POST['jmeno_prijmeni'], FILTER_SANITIZE_STRING);
    }

    // Validate street
    if (empty($_POST['ulice'])) {
        $errors['ulice'] = 'Zadejte ulici';
    } else {
        $ulice = filter_var($_POST['ulice'], FILTER_SANITIZE_STRING);
    }

    // Validate house number
    if (empty($_POST['cislo_popisne'])) {
        $errors['cislo_popisne'] = 'Zadejte číslo popisné';
    } else if (!is_numeric($_POST['cislo_popisne'])) {
        $errors['cislo_popisne'] = 'Číslo popisné musí být číslo';
    } else {
        $cislo_popisne = filter_var($_POST['cislo_popisne'], FILTER_SANITIZE_NUMBER_INT);
    }

    // Validate city
    if (empty($_POST['mesto'])) {
        $errors['mesto'] = 'Zadejte město';
    } else {
        $mesto = filter_var($_POST['mesto'], FILTER_SANITIZE_STRING);
    }

    // Validate postal code
    if (empty($_POST['psc'])) {
        $errors['psc'] = 'Zadejte PSČ';
    } else if (!preg_match('/^[0-9]{5}$/', $_POST['psc'])) {
        $errors['psc'] = 'PSČ musí být 5 číslic';
    } else {
        $psc = filter_var($_POST['psc'], FILTER_SANITIZE_NUMBER_INT);
    }

    // Validate shipping method
    $doprava = isset($_POST['doprava']) && in_array($_POST['doprava'], ['osobni', 'prepravce']) 
        ? $_POST['doprava'] 
        : 'osobni';

    if (empty($errors)) {
        $_SESSION['objednavka'] = [
            'jmeno_prijmeni' => $jmeno_prijmeni,
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

// Calculate cart totals
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
                            <label for="jmeno_prijmeni" class="block text-sm font-medium text-gray-300 mb-2">
                                Jméno a příjmení *
                            </label>
                            <input type="text" id="jmeno_prijmeni" name="jmeno_prijmeni" 
                                   value="<?php echo htmlspecialchars($jmeno_prijmeni); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3" required>
                            <?php if (isset($errors['jmeno_prijmeni'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['jmeno_prijmeni']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="ulice" class="block text-sm font-medium text-gray-300 mb-2">
                                Ulice *
                            </label>
                            <input type="text" id="ulice" name="ulice" 
                                   value="<?php echo htmlspecialchars($ulice); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3 <?php echo isset($errors['ulice']) ? 'border-red-500' : ''; ?>" 
                                   required>
                            <?php if (isset($errors['ulice'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['ulice']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="cislo_popisne" class="block text-sm font-medium text-gray-300 mb-2">
                                Číslo popisné *
                            </label>
                            <input type="text" id="cislo_popisne" name="cislo_popisne" 
                                   value="<?php echo htmlspecialchars($cislo_popisne); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3 <?php echo isset($errors['cislo_popisne']) ? 'border-red-500' : ''; ?>" 
                                   required>
                            <?php if (isset($errors['cislo_popisne'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['cislo_popisne']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="mesto" class="block text-sm font-medium text-gray-300 mb-2">
                                Město *
                            </label>
                            <input type="text" id="mesto" name="mesto" 
                                   value="<?php echo htmlspecialchars($mesto); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3 <?php echo isset($errors['mesto']) ? 'border-red-500' : ''; ?>" 
                                   required>
                            <?php if (isset($errors['mesto'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['mesto']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="psc" class="block text-sm font-medium text-gray-300 mb-2">
                                PSČ *
                            </label>
                            <input type="text" id="psc" name="psc" 
                                   value="<?php echo htmlspecialchars($psc); ?>" 
                                   class="form-input w-full rounded-lg px-4 py-3 <?php echo isset($errors['psc']) ? 'border-red-500' : ''; ?>" 
                                   maxlength="5" placeholder="12345" required>
                            <?php if (isset($errors['psc'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo $errors['psc']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Způsob dopravy</h3>
                        <div class="space-y-4">
                            <div class="flex items-center shipping-option">
                                <input type="radio" id="osobni" name="doprava" value="osobni" class="hidden" checked>
                                <label for="osobni" class="flex items-center justify-between w-full p-4 border border-gray-700 rounded-lg cursor-pointer">
                                    <span class="flex items-center">
                                        <span class="w-5 h-5 mr-3 rounded-full border-2 border-gray-500 flex items-center justify-center">
                                            <i class="fas fa-check text-transparent"></i>
                                        </span>
                                        Osobní odběr
                                    </span>
                                    <span class="text-gray-400">Zdarma</span>
                                </label>
                            </div>
                            <div class="flex items-center shipping-option">
                                <input type="radio" id="prepravce" name="doprava" value="prepravce" class="hidden">
                                <label for="prepravce" class="flex items-center justify-between w-full p-4 border border-gray-700 rounded-lg cursor-pointer">
                                    <span class="flex items-center">
                                        <span class="w-5 h-5 mr-3 rounded-full border-2 border-gray-500 flex items-center justify-center">
                                            <i class="fas fa-check text-transparent"></i>
                                        </span>
                                        Doprava přepravcem
                                    </span>
                                    <span class="text-gray-400" id="doprava-cena">129,00 Kč</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Celková cena</h3>
                        <p id="celkova-cena" class="text-2xl font-bold"><?php echo number_format($celkova_cena_dph, 2, ',', ' '); ?> Kč</p>
                    </div>

                    <button type="submit" class="btn-submit w-full py-3 mt-8 font-semibold text-white rounded-lg hover:bg-blue-700">Dokončit objednávku</button>
                </form>
            </div>
        </div>
    </main>

<script>
// Price calculation
        $(document).ready(function() {
            let cenaBezDopravy = <?php echo $celkova_cena_dph; ?>;
            const dopravaCena = 129;
            
            function aktualizujCenu() {
                let celkovaCena = cenaBezDopravy;
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
            
            // Initial calculation
            aktualizujCenu();
            
            // Update when shipping method changes
            $('input[name="doprava"]').on('change', aktualizujCenu);
            
            // Add visual feedback when selecting shipping options
            $('.shipping-option input[type="radio"]').on('change', function() {
                $('.shipping-option').removeClass('border-neo-blue bg-neo-blue bg-opacity-10');
                $(this).closest('.shipping-option').addClass('border-neo-blue bg-neo-blue bg-opacity-10');
            });
        });

        // Add form validation and submission handling
        $(document).ready(function() {
            // Client-side validation
            function validatePSC(psc) {
                return /^[0-9]{5}$/.test(psc);
            }

            function validateCisloPopisne(cislo) {
                return /^\d+$/.test(cislo);
            }

            $('form').on('submit', function(e) {
                const psc = $('#psc').val();
                const cisloPopisne = $('#cislo_popisne').val();
                let hasErrors = false;

                // Reset previous error states
                $('.form-input').removeClass('error');
                $('.error-message').remove();

                // Validate PSČ
                if (!validatePSC(psc)) {
                    e.preventDefault();
                    $('#psc').addClass('error');
                    $('#psc').after('<p class="error-message text-red-500 text-sm mt-1">PSČ musí obsahovat přesně 5 číslic</p>');
                    hasErrors = true;
                }

                // Validate house number
                if (!validateCisloPopisne(cisloPopisne)) {
                    e.preventDefault();
                    $('#cislo_popisne').addClass('error');
                    $('#cislo_popisne').after('<p class="error-message text-red-500 text-sm mt-1">Číslo popisné musí být číslo</p>');
                    hasErrors = true;
                }

                // Check required fields
                $('input[required]').each(function() {
                    if (!$(this).val().trim()) {
                        e.preventDefault();
                        $(this).addClass('error');
                        $(this).after('<p class="error-message text-red-500 text-sm mt-1">Toto pole je povinné</p>');
                        hasErrors = true;
                    }
                });

                if (hasErrors) {
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.error').first().offset().top - 100
                    }, 500);
                } else {
                    // Show loading state on button
                    $('button[type="submit"]').prop('disabled', true)
                        .html('<span class="inline-block animate-spin mr-2">↻</span>Odesílám...');
                }
            });

            // Real-time validation
            $('#psc').on('input', function() {
                $(this).removeClass('error');
                $(this).next('.error-message').remove();
                if ($(this).val() && !validatePSC($(this).val())) {
                    $(this).addClass('error');
                    $(this).after('<p class="error-message text-red-500 text-sm mt-1">PSČ musí obsahovat přesně 5 číslic</p>');
                }
            });

            $('#cislo_popisne').on('input', function() {
                $(this).removeClass('error');
                $(this).next('.error-message').remove();
                if ($(this).val() && !validateCisloPopisne($(this).val())) {
                    $(this).addClass('error');
                    $(this).after('<p class="error-message text-red-500 text-sm mt-1">Číslo popisné musí být číslo</p>');
                }
            });
        });
    </script>
</body>
</html>