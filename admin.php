<?php
session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: index.php");
    exit();
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

// Gestion de la déconnexion
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Gestion des opérations CRUD (Ajouter, Modifier, Supprimer)
if (isset($_POST['add_category'])) {
    $nom = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO categories (nom) VALUES (?)");
    $stmt->execute([$nom]);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['edit_category'])) {
    $id = $_POST['category_id'];
    $nom = $_POST['category_name'];
    $stmt = $conn->prepare("UPDATE categories SET nom = ? WHERE id = ?");
    $stmt->execute([$nom, $id]);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['delete_category'])) {
    $id = $_POST['category_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['add_subcategory'])) {
    $nom = $_POST['subcategory_name'];
    $categorie_id = $_POST['category_id'];
    $stmt = $conn->prepare("INSERT INTO sous_categories (nom, categorie_id) VALUES (?, ?)");
    $stmt->execute([$nom, $categorie_id]);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['edit_subcategory'])) {
    $id = $_POST['subcategory_id'];
    $nom = $_POST['subcategory_name'];
    $categorie_id = $_POST['category_id'];
    $stmt = $conn->prepare("UPDATE sous_categories SET nom = ?, categorie_id = ? WHERE id = ?");
    $stmt->execute([$nom, $categorie_id, $id]);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['delete_subcategory'])) {
    $id = $_POST['subcategory_id'];
    $stmt = $conn->prepare("DELETE FROM sous_categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['add_product'])) {
    $nom = $_POST['product_name'];
    $prix = $_POST['product_price'];
    $description = $_POST['product_description'];
    $sous_categorie_id = $_POST['subcategory_id'];
    $stmt = $conn->prepare("INSERT INTO produits (nom, prix, description, sous_categorie_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $prix, $description, $sous_categorie_id]);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['edit_product'])) {
    $id = $_POST['product_id'];
    $nom = $_POST['product_name'];
    $prix = $_POST['product_price'];
    $description = $_POST['product_description'];
    $sous_categorie_id = $_POST['subcategory_id'];
    $stmt = $conn->prepare("UPDATE produits SET nom = ?, prix = ?, description = ?, sous_categorie_id = ? WHERE id = ?");
    $stmt->execute([$nom, $prix, $description, $sous_categorie_id, $id]);
    header("Location: admin.php");
    exit();
}

if (isset($_POST['delete_product'])) {
    $id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
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
    <title>Administration - Wood Kafee</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Playfair+Display:wght@400;600&family=Dancing_Script:wght@700&display=swap" rel="stylesheet">
    <style>
        /* Styles personnalisés pour un design élégant et professionnel */
        body {
            background-image: url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Poppins', sans-serif;
            color: white;
            margin: 0;
        }

        /* Dark overlay for readability */
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

        /* Elegant Header */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 2;
            padding: 1rem 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out;
        }

        header.hidden {
            transform: translateY(-100%);
        }

        header .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 7xl;
            margin: 0 auto;
            padding: 0 1rem;
        }

        header .header-content h1 {
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        header nav ul li button {
            transition: color 0.3s ease;
        }

        header nav ul li button:hover {
            color: #d4a373;
        }

        .logo-img {
            height: 5rem;
            width: auto;
            transition: transform 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.1);
        }

        /* Admin Section */
        .admin-section {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(8px);
            margin-top: 80px; /* Adjusted for fixed header */
            padding: 2rem;
        }

        .admin-section h2 {
            font-family: 'Playfair Display', serif;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            font-size: 2rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .admin-section .flex {
            justify-content: center;
            gap: 1.5rem;
        }

        .admin-section button {
            font-size: 1.1rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, background-color 0.3s ease;
        }

        .admin-section button:hover {
            transform: translateY(-2px);
        }

        /* Elegant Menu */
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
            display: flex;
            align-items: center;
            justify-content: space-between;
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
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
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
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .product:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .description {
            color: #e0e0e0;
            font-style: italic;
            font-size: 0.9rem;
        }

        /* Admin Actions Icons */
        .admin-actions {
            display: flex;
            gap: 0.5rem;
        }

        .admin-actions button {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            padding: 0.5rem;
            transition: color 0.3s ease, transform 0.2s ease;
            cursor: pointer;
        }

        .admin-actions button:hover {
            transform: scale(1.2);
        }

        .admin-actions .add-btn:hover {
            color: #27ae60;
        }

        .admin-actions .edit-btn:hover {
            color: #d4a373;
        }

        .admin-actions .delete-btn:hover {
            color: #e74c3c;
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
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            color: white;
        }

        .modal-content h2 {
            font-family: 'Playfair Display', serif;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .modal-content input,
        .modal-content select,
        .modal-content textarea {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid #d4a373;
        }

        .modal-content input::placeholder,
        .modal-content textarea::placeholder {
            color: #e0e0e0;
        }

        /* Buttons */
        button {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background: transparent;
            border-top: none;
            position: relative;
            color: white;
            padding: 3rem 0;
        }

        footer h3, footer p, footer a, footer div {
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

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .category-card {
                width: 100%;
                min-height: 100px;
                display: flex;
                flex-direction: column;
                margin: 0 auto;
            }

            .category-content {
                flex-grow: 1;
                overflow-y: auto;
                max-height: 0 !important;
                opacity: 0 !important;
            }

            .category-content.active {
                max-height: 2000px !important;
                opacity: 1 !important;
            }

            .subcategory-content {
                max-height: 0 !important;
                opacity: 0 !important;
            }

            .subcategory-content.active {
                max-height: 1000px !important;
                opacity: 1 !important;
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

            header .header-content h1 {
                font-size: 2rem;
            }

            .logo-img {
                height: 4rem;
            }

            footer .grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .admin-section {
                margin-top: 60px; /* Adjusted for fixed header on mobile */
            }

            .admin-section .flex {
                flex-direction: column;
                gap: 1rem;
            }

            .admin-section button {
                font-size: 1rem;
                padding: 0.5rem 1rem;
            }

            .admin-actions button {
                font-size: 1rem;
                padding: 0.3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Elegant Header -->
    <header>
        <div class="header-content">
            <h1>
            </h1>
            <nav>
                <ul class="flex gap-6 sm:flex-col sm:gap-2">
                    <li>
                        <form method="POST">
                            <button type="submit" name="logout" class="text-[#00000] hover:text-[#c68b59] transition-colors duration-300">
                                <i class="fas fa-door-open text-2xl sm:text-xl"></i>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Interface Admin -->
    <div class="admin-section max-w-7xl mx-auto my-4">
        <h2 class="text-2xl font-bold mb-4">Gestion du Menu</h2>
        <div class="flex gap-4">
            <button class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653]" onclick="openModal('addCategoryModal')">Ajouter une Catégorie</button>
            <button class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653]" onclick="openModal('addSubcategoryModal')">Ajouter une Sous-Catégorie</button>
            <button class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653]" onclick="openModal('addProductModal')">Ajouter un Produit</button>
        </div>
    </div>

    <!-- Menu avec Gestion -->
    <div class="menu max-w-7xl mx-auto py-8 px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php foreach ($categories as $category): ?>
                <div class="category-card overflow-hidden">
                    <div class="category-header p-4 cursor-pointer">
                        <h2>
                            <?php echo htmlspecialchars($category['nom']); ?>
                        </h2>
                        <div class="admin-actions">
                            <button class="add-btn" onclick="openAddSubcategoryModal(<?php echo $category['id']; ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="edit-btn" onclick="openEditCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['nom']); ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-btn" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
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
                                    <h3><?php echo htmlspecialchars($subcategory['nom']); ?></h3>
                                    <div class="admin-actions">
                                        <button class="add-btn" onclick="openModal('addProductModal')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button class="edit-btn" onclick="openEditSubcategoryModal(<?php echo $subcategory['id']; ?>, '<?php echo htmlspecialchars($subcategory['nom']); ?>', <?php echo $subcategory['categorie_id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="delete-btn" onclick="deleteSubcategory(<?php echo $subcategory['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="subcategory-content">
                                    <?php
                                    $subcategoryProduits = array_filter($produits, function($prod) use ($subcategory) {
                                        return $prod['sous_categorie_id'] == $subcategory['id'];
                                    });

                                    foreach ($subcategoryProduits as $produit):
                                    ?>
                                        <div class="product">
                                            <span class="flex-1"><?php echo htmlspecialchars($produit['nom']); ?></span>
                                            <span class="text-right w-24">
                                                <?php 
                                                $prix = isset($produit['prix']) ? number_format($produit['prix'], 2) : '0.00';
                                                $devise = isset($produit['devise']) && !empty($produit['devise']) ? htmlspecialchars($produit['devise']) : 'DT';
                                                echo $prix . ' ' . $devise;
                                                ?>
                                            </span>
                                            <div class="admin-actions">
                                                <button class="edit-btn" onclick="openEditProductModal(<?php echo $produit['id']; ?>, '<?php echo htmlspecialchars($produit['nom']); ?>', <?php echo isset($produit['prix']) ? $produit['prix'] : 0; ?>, '<?php echo htmlspecialchars($produit['description'] ?? ''); ?>', <?php echo $produit['sous_categorie_id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="delete-btn" onclick="deleteProduct(<?php echo $produit['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
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

    <!-- Modales pour ajouter/modifier -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl mb-4">Ajouter une Catégorie</h2>
            <form method="POST">
                <input type="text" name="category_name" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" placeholder="Nom de la catégorie" required>
                <div class="flex gap-2">
                    <button type="submit" name="add_category" class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300">Ajouter</button>
                    <button type="button" class="bg-[#e74c3c] text-white px-4 py-2 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="closeModal('addCategoryModal')">Fermer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl mb-4">Modifier une Catégorie</h2>
            <form method="POST">
                <input type="hidden" name="category_id" id="edit_category_id">
                <input type="text" name="category_name" id="edit_category_name" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" required>
                <div class="flex gap-2">
                    <button type="submit" name="edit_category" class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300">Modifier</button>
                    <button type="button" class="bg-[#e74c3c] text-white px-4 py-2 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="closeModal('editCategoryModal')">Fermer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addSubcategoryModal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl mb-4">Ajouter une Sous-Catégorie</h2>
            <form method="POST">
                <input type="hidden" name="category_id" id="add_subcategory_category_id">
                <input type="text" name="subcategory_name" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" placeholder="Nom de la sous-catégorie" required>
                <div class="flex gap-2">
                    <button type="submit" name="add_subcategory" class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300">Ajouter</button>
                    <button type="button" class="bg-[#e74c3c] text-white px-4 py-2 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="closeModal('addSubcategoryModal')">Fermer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editSubcategoryModal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl mb-4">Modifier une Sous-Catégorie</h2>
            <form method="POST">
                <input type="hidden" name="subcategory_id" id="edit_subcategory_id">
                <select name="category_id" id="edit_subcategory_category_id" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="subcategory_name" id="edit_subcategory_name" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" required>
                <div class="flex gap-2">
                    <button type="submit" name="edit_subcategory" class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300">Modifier</button>
                    <button type="button" class="bg-[#e74c3c] text-white px-4 py-2 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="closeModal('editSubcategoryModal')">Fermer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl mb-4">Ajouter un Produit</h2>
            <form method="POST">
                <select name="subcategory_id" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" required>
                    <?php foreach ($sousCategories as $sc): ?>
                        <option value="<?php echo $sc['id']; ?>"><?php echo htmlspecialchars($sc['category_name'] . ' - ' . $sc['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="product_name" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" placeholder="Nom du produit" required>
                <input type="number" step="0.01" name="product_price" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" placeholder="Prix" required>
                <textarea name="product_description" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" placeholder="Description (optionnel)"></textarea>
                <div class="flex gap-2">
                    <button type="submit" name="add_product" class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300">Ajouter</button>
                    <button type="button" class="bg-[#e74c3c] text-white px-4 py-2 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="closeModal('addProductModal')">Fermer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl mb-4">Modifier un Produit</h2>
            <form method="POST">
                <input type="hidden" name="product_id" id="edit_product_id">
                <select name="subcategory_id" id="edit_product_subcategory_id" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" required>
                    <?php foreach ($sousCategories as $sc): ?>
                        <option value="<?php echo $sc['id']; ?>"><?php echo htmlspecialchars($sc['category_name'] . ' - ' . $sc['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="product_name" id="edit_product_name" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" placeholder="Nom du produit" required>
                <input type="number" step="0.01" name="product_price" id="edit_product_price" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" placeholder="Prix" required>
                <textarea name="product_description" id="edit_product_description" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" placeholder="Description (optionnel)"></textarea>
                <div class="flex gap-2">
                    <button type="submit" name="edit_product" class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300">Modifier</button>
                    <button type="button" class="bg-[#e74c3c] text-white px-4 py-2 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="closeModal('editProductModal')">Fermer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
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
                <p class="text.sm">Ouvert : Lun-Dim, 8h-22h</p>
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

        // Fonctions pour gérer les modales d'ajout/modification
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function openAddSubcategoryModal(categoryId) {
            document.getElementById('add_subcategory_category_id').value = categoryId;
            openModal('addSubcategoryModal');
        }

        function openEditCategoryModal(id, name) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            openModal('editCategoryModal');
        }

        function openEditSubcategoryModal(id, name, categoryId) {
            document.getElementById('edit_subcategory_id').value = id;
            document.getElementById('edit_subcategory_name').value = name;
            document.getElementById('edit_subcategory_category_id').value = categoryId;
            openModal('editSubcategoryModal');
        }

        function openEditProductModal(id, name, price, description, subcategoryId) {
            document.getElementById('edit_product_id').value = id;
            document.getElementById('edit_product_name').value = name;
            document.getElementById('edit_product_price').value = price;
            document.getElementById('edit_product_description').value = description;
            document.getElementById('edit_product_subcategory_id').value = subcategoryId;
            openModal('editProductModal');
        }

        function deleteCategory(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_category';
                input.value = '1';
                form.appendChild(input);
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'category_id';
                idInput.value = id;
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteSubcategory(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette sous-catégorie ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_subcategory';
                input.value = '1';
                form.appendChild(input);
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'subcategory_id';
                idInput.value = id;
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteProduct(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_product';
                input.value = '1';
                form.appendChild(input);
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'product_id';
                idInput.value = id;
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Scroll direction detection to hide/show header
        let lastScrollTop = 0;
        const header = document.getElementById('header');

        window.addEventListener('scroll', () => {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop) {
                // Scrolling down - hide the header
                header.classList.add('hidden');
            } else {
                // Scrolling up - show the header
                header.classList.remove('hidden');
            }
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // Prevent negative scroll values
        });
    </script>
</body>
</html>