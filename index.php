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
    <title>Menu Wood Kafee</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome pour l'icône de la porte -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles personnalisés pour un design élégant et professionnel */
        body {
            background-color: #fffcf7; /* Fond blanc cassé */
            font-family: 'Poppins', sans-serif;
            color: #5a4630; /* Marron moyen */
        }

        /* Header */
        header {
            background: linear-gradient(to right, #f9e8d2, #e6d5b8); /* Dégradé beige clair */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-bottom: 2px solid #d4a373; /* Bordure dorée */
        }

        header nav ul li a {
            transition: color 0.3s ease;
        }

        header nav ul li a:hover {
            color: #d4a373; /* Doré au survol */
        }

        /* Menu */
        .category-card {
            border: 1px solid #d4a373; /* Bordure dorée subtile */
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .category-header {
            background: linear-gradient(to right, #f9e8d2, #e6d5b8); /* Dégradé beige clair */
        }

        .subcategory-header {
            background-color: #f9e8d2; /* Beige clair */
        }

        .subcategory-header:hover {
            background-color: #e6d5b8; /* Beige légèrement plus foncé au survol */
        }

        /* Accordéon */
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

        /* Style pour la modale */
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

        /* Footer */
        footer {
            background: linear-gradient(to right, #f9e8d2, #e6d5b8);
            border-top: 2px solid #d4a373;
        }

        footer a {
            transition: transform 0.3s ease;
        }

        footer a:hover {
            transform: scale(1.2);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
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
        }
    </style>
</head>
<body>
    <!-- Header Professionnel -->
    <header class="text-[#5a4630] py-6 px-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="font-['Playfair_Display'] text-4xl font-semibold tracking-wide sm:text-3xl">Wood Kafee</h1>
            <nav>
                <ul class="flex gap-6 sm:flex-col sm:gap-2">
                    
                    
                        <button id="connectServerBtn" class="text-[#d4a373] hover:text-[#c68b59] transition-colors duration-300">
                            <i class="fas fa-door-open text-2xl sm:text-xl"></i>
                        </button>
                  
                </ul>
            </nav>
        </div>
    </header>

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
                <div class="category-card bg-white overflow-hidden">
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
                                <div class="subcategory-header p-3 cursor-pointer hover:bg-[#e6d5b8] transition-colors duration-300">
                                    <h3 class="text-xl font-bold"><?php echo htmlspecialchars($subcategory['nom']); ?></h3>
                                </div>
                                <div class="subcategory-content bg-white">
                                    <?php
                                    $subcategoryProduits = array_filter($produits, function($prod) use ($subcategory) {
                                        return $prod['sous_categorie_id'] == $subcategory['id'];
                                    });

                                    foreach ($subcategoryProduits as $produit):
                                    ?>
                                        <div class="product flex justify-between items-center p-2 border-b border-dashed border-[#d4a373] hover:bg-[#f9e8d2] transition-all duration-300">
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
                                            <div class="description text-sm italic text-[#7f6b47] p-2 text-center">Description : <?php echo htmlspecialchars($produit['description']); ?></div>
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

    <!-- Footer Professionnel -->
    <footer class="text-[#5a4630] py-12">
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
                <p class="text-sm mt-2"><a href="mailto:contact@woodkafee.com" class="text-[#d4a373] hover:text-[#c68b59] transition-colors duration-300">contact@woodkafee.com</a></p>
            </div>
            <div>
                <h3 class="font-['Playfair_Display'] text-xl mb-4">Suivez-Nous</h3>
                <div class="flex justify-center md:justify-start gap-6">
                    <a href="#" class="text-[#d4a373] hover:text-[#c68b59] transition-transform duration-300">
                        <i class="fab fa-facebook-f text-2xl"></i>
                    </a>
                    <a href="#" class="text-[#d4a373] hover:text-[#c68b59] transition-transform duration-300">
                        <i class="fab fa-instagram text-2xl"></i>
                    </a>
                    <a href="#" class="text-[#d4a373] hover:text-[#c68b59] transition-transform duration-300">
                        <i class="fab fa-twitter text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="text-center mt-6 text-sm border-t border-[#d4a373] pt-4">
            © 2025 Wood Kafee. Tous droits réservés. | <a href="#privacy" class="text-[#d4a373] hover:text-[#c68b59] transition-colors duration-300">Politique de Confidentialité</a>
        </div>
    </footer>

    <script>
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