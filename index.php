<?php
session_start();

// Gestion de la connexion admin au début du fichier
$defaultAdminCode = "1111";
$loginError = '';

if (isset($_POST['admin_login'])) {
    $code = isset($_POST['admin_code']) ? trim($_POST['admin_code']) : '';
    if ($code === $defaultAdminCode) {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $loginError = "Code incorrect.";
    }
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woodcaffe";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}

// Récupérer les données des tables
$categoriesStmt = $conn->query("SELECT * FROM categories");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$sousCategoriesStmt = $conn->query("SELECT sc.*, c.nom as category_name FROM sous_categories sc JOIN categories c ON sc.categorie_id = c.id");
$sousCategories = $sousCategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$produitsStmt = $conn->query("SELECT p.*, sc.nom as subcategory_name, c.nom as category_name FROM produits p JOIN sous_categories sc ON p.sous_categorie_id = sc.id JOIN categories c ON sc.categorie_id = c.id");
$produits = $produitsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wood Kafee</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome pour l'icône de la porte -->
    <link href="https://fonts.googleapis.com/css2?family=Dancing_Script:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles personnalisés pour un design élégant et professionnel */
        body {
            background-image: url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Poppins', sans-serif;
            color: #5a4630;
            margin: 0;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        header {
            background: transparent;
            box-shadow: none;
            border-bottom: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 2;
        }

        header .header-content h1 {
            color: white;
        }

        header nav ul li a {
            transition: color 0.3s ease;
        }

        header nav ul li a:hover {
            color: #d4a373;
        }

        .hero-section {
            position: relative;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .hero-content p {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .clock {
            display: flex;
            gap: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .clock div {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .clock div span:first-child {
            font-size: 2rem;
        }

        .clock div span:last-child {
            font-size: 1rem;
            text-transform: uppercase;
            color: #e0e0e0;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .category-header {
            background: linear-gradient(135deg, rgba(212, 163, 115, 0.8), rgba(230, 213, 184, 0.8));
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .category-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 600;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .subcategory-header {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding:1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .subcategory-header h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            font-weight: 500;
            color: white;
        }

        .subcategory-header:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .subcategory-content {
            background: transparent;
        }

        .product {
            color: white;
            padding: 0.75rem;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.3);
            transition: background 0.3s ease;
        }

        .product:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .description {
            color: #e0e0e0;
            font-style: italic;
            font-size: 0.9rem;
        }

        .category-content, .subcategory-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease, opacity 0.5s ease;
            opacity: 0;
        }

        .category-content.active {
            max-height: 2000px;
            opacity: 1;
            padding: 1rem;
        }

        .subcategory-content.active {
            max-height: 1000px;
            opacity: 1;
            padding: 1rem;
        }

        .category-header h2::after, .subcategory-header h3::after {
            content: '▼';
            display: inline-block;
            margin-left: 0.5rem;
            transition: transform 0.3s ease;
        }

        .category-header.active h2::after, .subcategory-header.active h3::after {
            transform: rotate(180deg);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .calculator-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .calculator-grid button {
            padding: 1rem;
            font-size: 1.2rem;
            background-color: #f9e8d2;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .calculator-grid button:hover {
            background-color: #e6d5b8;
        }

        .calculator-grid .clear-btn {
            background-color: #e74c3c;
            color: white;
        }

        .calculator-grid .clear-btn:hover {
            background-color: #c0392b;
        }

        .calculator-grid .submit-btn {
            background-color: #27ae60;
            color: white;
        }

        .calculator-grid .submit-btn:hover {
            background-color: #219653;
        }

        footer {
            background: transparent;
            border-top: none;
            position: relative;
            color: white;
            padding: 3rem 0;
        }

        footer, footer h3, footer p, footer a, footer div {
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        footer a {
            transition: transform 0.3s ease;
        }

        footer a:hover {
            transform: scale(1.2);
            color: #d4a373;
        }

        @media (max-width: 640px) {
            .hero-section {
                height: 300px;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .clock {
                font-size: 1rem;
                gap: 0.5rem;
            }

            .clock div span:first-child {
                font-size: 1.5rem;
            }

            .clock div span:last-child {
                font-size: 0.8rem;
            }

            .category-card {
                margin: 0 auto;
            }

            .category-header h2 {
                font-size: 1.5rem;
            }

            .subcategory-header h3 {
                font-size: 1.25rem;
            }

            .product span {
                font-size: 0.9rem;
            }

            .description {
                font-size: 0.8rem;
            }

            header nav ul {
                flex-direction: column;
                gap: 1rem;
            }

            footer .grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Transparent Background -->
    <header class="py-6 px-4">
        <div class="header-content max-w-7xl mx-auto flex justify-between items-center">
        <h1 class="font-['Dancing_Script'] text-4xl font-bold tracking-wide sm:text-5xl">WK</h1>                <ul class="flex gap-6 sm:flex-col sm:gap-2">
                    <li>
                        <button id="connectServerBtn" class="text-[#d4a373] hover:text-[#c68b59] transition-colors duration-300">
                            <i class="fas fa-door-open text-2xl sm:text-xl"></i>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section with Clock -->
    <div class="hero-section">
        <div class="hero-content">
            <p class="uppercase">Welcome to</p>
            <h1>Wood Kafee</h1>
            <div class="border-t border-white w-32 mx-auto my-4"></div>
            <div class="clock">
                <div>
                    <span id="hours">00</span>
                    <span>Hours</span>
                </div>
                <div>
                    <span id="minutes">00</span>
                    <span>Minutes</span>
                </div>
                <div>
                    <span id="seconds">00</span>
                    <span>Seconds</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale pour la calculatrice -->
    <div id="calculatorModal" class="modal">
        <div class="modal-content">
            <h2 class="font-['Playfair_Display'] text-2xl mb-4 text-[#5a4630]">Entrez le Mot de Passe</h2>
            <form id="loginForm" method="POST" action="">
                <input id="passwordInput" name="admin_code" type="text" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md text-center text-lg" readonly placeholder="****">
                <div id="errorMessage" class="text-red-500 mb-4 <?php echo isset($loginError) ? '' : 'hidden'; ?>">
                    <?php echo isset($loginError) ? $loginError : 'Mot de passe incorrect !'; ?>
                </div>
                <div class="calculator-grid">
                    <button type="button" onclick="appendNumber('1')">1</button>
                    <button type="button" onclick="appendNumber('2')">2</button>
                    <button type="button" onclick="appendNumber('3')">3</button>
                    <button type="button" onclick="appendNumber('4')">4</button>
                    <button type="button" onclick="appendNumber('5')">5</button>
                    <button type="button" onclick="appendNumber('6')">6</button>
                    <button type="button" onclick="appendNumber('7')">7</button>
                    <button type="button" onclick="appendNumber('8')">8</button>
                    <button type="button" onclick="appendNumber('9')">9</button>
                    <button type="button" onclick="appendNumber('0')">0</button>
                    <button type="button" class="clear-btn" onclick="clearPassword()">Effacer</button>
                    <button type="submit" name="admin_login" class="submit-btn" onclick="submitForm()">Valider</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Menu -->
    <div class="menu max-w-7xl mx-auto py-12 px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php foreach ($categories as $category): ?>
                <div class="category-card overflow-hidden">
                    <div class="category-header p-4 cursor-pointer">
                        <h2 class="font-['Playfair_Display'] text-2xl font-bold"><?php echo htmlspecialchars($category['nom']); ?></h2>
                    </div>
                    <div class="category-content">
                        <?php
                        $categorySousCategories = array_filter($sousCategories, function($sc) use ($category) {
                            return $sc['categorie_id'] == $category['id'];
                        });

                        foreach ($categorySousCategories as $subcategory):
                        ?>
                            <div class="subcategory">
                                <div class="subcategory-header p-3 cursor-pointer transition-colors duration-300">
                                    <h3 class="text-xl font-bold"><?php echo htmlspecialchars($subcategory['nom']); ?></h3>
                                </div>
                                <div class="subcategory-content">
                                    <?php
                                    $subcategoryProduits = array_filter($produits, function($prod) use ($subcategory) {
                                        return $prod['sous_categorie_id'] == $subcategory['id'];
                                    });

                                    foreach ($subcategoryProduits as $produit):
                                    ?>
                                        <div class="product flex justify-between items-center p-2 border-b border-dashed border-[#d4a373] transition-all duration-300">
                                            <span class="flex-1"><?php echo htmlspecialchars($produit['nom']); ?></span>
                                            <span class="text-right w-24">
                                                <?php 
                                                $prix = isset($produit['prix']) ? number_format($produit['prix'], 2) : '0.00';
                                                $devise = isset($produit['devise']) && !empty($produit['devise']) ? htmlspecialchars($produit['devise']) : 'DT';
                                                echo $prix . ' ' . $devise;
                                                ?>
                                            </span>
                                        </div>
                                        <?php if (isset($produit['description']) && !empty($produit['description'])): ?>
                                            <div class="description text-sm italic p-2 text-center">Description : <?php echo htmlspecialchars($produit['description']); ?></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer with Matching Design -->
    <footer class="py-12">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
            <div>
                <h3 class="font-['Playfair_Display'] text-xl mb-4">À Propos</h3>
                <p class="text-sm">Wood Kafee est votre destination pour un moment de détente avec des boissons et des plats savoureux, préparés avec soin.</p>
            </div>
            <div>
                <h3 class="font-['Playfair_Display'] text-xl mb-4">Contact</h3>
                <p class="text-sm">123 Rue du Café, Tunis</p>
                <p class="text-sm">Tél : +216 12 345 678</p>
                <p class="text-sm">Ouvert : Lun-Dim, 8h-22h</p>
                <p class="text-sm mt-2"><a href="mailto:contact@woodkafee.com" class="hover:text-[#c68b59] transition-colors duration-300">contact@woodkafee.com</a></p>
            </div>
            <div>
                <h3 class="font-['Playfair_Display'] text-xl mb-4">Suivez-Nous</h3>
                <div class="flex justify-center md:justify-start gap-6">
                    <a href="#" class="hover:text-[#c68b59] transition-transform duration-300">
                        <i class="fab fa-facebook-f text-2xl"></i>
                    </a>
                    <a href="#" class="hover:text-[#c68b59] transition-transform duration-300">
                        <i class="fab fa-instagram text-2xl"></i>
                    </a>
                    <a href="#" class="hover:text-[#c68b59] transition-transform duration-300">
                        <i class="fab fa-twitter text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="text-center mt-6 text-sm border-t border-[#d4a373] pt-4">
            © 2025 Wood Kafee. Tous droits réservés. | <a href="#privacy" class="hover:text-[#c68b59] transition-colors duration-300">Politique de Confidentialité</a>
        </div>
    </footer>

    <script>
        // Clock Logic
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            document.getElementById('hours').innerText = hours;
            document.getElementById('minutes').innerText = minutes;
            document.getElementById('seconds').innerText = seconds;
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        // Gestion des accordéons pour les catégories
        const categoryHeaders = document.querySelectorAll('.category-header');
        categoryHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const isActive = content.classList.contains('active');

                // Fermer tous les autres accordéons de catégorie
                document.querySelectorAll('.category-content').forEach(item => {
                    if (item !== content) {
                        item.classList.remove('active');
                        item.previousElementSibling.classList.remove('active');
                    }
                });

                // Ouvrir/fermer l'accordéon cliqué
                content.classList.toggle('active');
                header.classList.toggle('active');
            });
        });

        // Gestion des accordéons pour les sous-catégories
        const subcategoryHeaders = document.querySelectorAll('.subcategory-header');
        subcategoryHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const isActive = content.classList.contains('active');

                // Fermer tous les autres accordéons de sous-catégorie dans la même catégorie
                const parentCategory = header.closest('.category-content');
                parentCategory.querySelectorAll('.subcategory-content').forEach(item => {
                    if (item !== content) {
                        item.classList.remove('active');
                        item.previousElementSibling.classList.remove('active');
                    }
                });

                // Ouvrir/fermer l'accordéon cliqué
                content.classList.toggle('active');
                header.classList.toggle('active');
            });
        });

        // Gestion de la modale et de la calculatrice
        const connectServerBtn = document.getElementById('connectServerBtn');
        const calculatorModal = document.getElementById('calculatorModal');
        const passwordInput = document.getElementById('passwordInput');
        const errorMessage = document.getElementById('errorMessage');
        let password = '';

        connectServerBtn.addEventListener('click', () => {
            calculatorModal.style.display = 'flex';
            passwordInput.value = '';
            password = '';
            errorMessage.classList.add('hidden');
        });

        calculatorModal.addEventListener('click', (e) => {
            if (e.target === calculatorModal) {
                calculatorModal.style.display = 'none';
            }
        });

        function appendNumber(number) {
            if (password.length < 4) {
                password += number;
                passwordInput.value = '*'.repeat(password.length);
            }
        }

        function clearPassword() {
            password = '';
            passwordInput.value = '';
            errorMessage.classList.add('hidden');
        }

        function submitForm() {
            document.getElementById('passwordInput').value = password;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>