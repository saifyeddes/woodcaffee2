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

// Définir les icônes pour chaque catégorie
$categoryIcons = [
    'Petit Dej' => 'fa-croissant',
    'Boissons Chaudes' => 'fa-coffee',
    'Boissons Froides' => 'fa-glass-whiskey',
    'Boissons WOOD KAFFEE' => 'fa-cocktail',
    'Sucrés' => 'fa-cake-candles',
    'Salés' => 'fa-egg',
    'Boissons' => 'fa-wine-glass-alt'
];
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
    <style>
        /* Styles supplémentaires pour les animations et ajustements */
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
    </style>
</head>
<body class="bg-[#f5f1e9] text-[#3e2f1b] font-['Poppins',sans-serif]">
    <!-- Header -->
    <header class="bg-[#3e2f1b] text-white py-6 px-4 shadow-lg">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="font-['Playfair_Display'] text-4xl font-semibold tracking-wide">Administration - Wood Kafee</h1>
            <form method="POST">
            <button type="submit" name="logout" class="text-[#d4a373] hover:text-[#c68b59] transition-colors duration-300">
                            <i class="fas fa-door-open text-2xl sm:text-xl"></i>
                        </button>
            </form>
        </div>
    </header>

    <!-- Interface Admin -->
    <div class="admin-section bg-white p-4 rounded-lg shadow-md max-w-7xl mx-auto my-4">
        <h2 class="font-['Playfair_Display'] text-2xl font-bold mb-4">Gestion du Menu</h2>
        <div class="flex gap-4">
            <button class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300" onclick="openModal('addCategoryModal')">Ajouter une Catégorie</button>
            <button class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300" onclick="openModal('addSubcategoryModal')">Ajouter une Sous-Catégorie</button>
            <button class="bg-[#27ae60] text-white px-4 py-2 rounded-lg hover:bg-[#219653] transition-colors duration-300" onclick="openModal('addProductModal')">Ajouter un Produit</button>
        </div>
    </div>

    <!-- Menu avec Gestion -->
    <div class="menu max-w-7xl mx-auto py-8 px-4">
        <?php foreach ($categories as $category): ?>
            <div class="category">
                <div class="category-header bg-white p-4 rounded-lg shadow-md cursor-pointer hover:bg-[#f9e8d2] transition-colors duration-300">
                    <h2 class="font-['Playfair_Display'] text-2xl font-bold flex items-center gap-2">
                        <i class="fas <?php echo isset($categoryIcons[$category['nom']]) ? $categoryIcons[$category['nom']] : 'fa-list'; ?> text-[#d4a373]"></i>
                        <?php echo htmlspecialchars($category['nom']); ?>
                    </h2>
                </div>
                <div class="category-content">
                    <div class="admin-actions flex gap-2 p-2">
                        <button class="bg-[#d4a373] text-white px-3 py-1 rounded-lg hover:bg-[#c68b59] transition-colors duration-300" onclick="openEditCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['nom']); ?>')">Modifier</button>
                        <button class="bg-[#e74c3c] text-white px-3 py-1 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="deleteCategory(<?php echo $category['id']; ?>)">Supprimer</button>
                    </div>
                    <?php
                    $categorySousCategories = array_filter($sousCategories, function($sc) use ($category) {
                        return $sc['categorie_id'] == $category['id'];
                    });

                    foreach ($categorySousCategories as $subcategory):
                    ?>
                        <div class="subcategory">
                            <div class="subcategory-header bg-[#f9e8d2] p-3 rounded-md cursor-pointer hover:bg-[#e6d5b8] transition-colors duration-300">
                                <h3 class="text-xl font-bold flex items-center gap-2">
                                    <?php echo htmlspecialchars($subcategory['nom']); ?>
                                    <span class="ml-auto">▼</span>
                                </h3>
                            </div>
                            <div class="subcategory-content bg-white rounded-md shadow-sm">
                                <div class="admin-actions flex gap-2 p-2">
                                    <button class="bg-[#d4a373] text-white px-3 py-1 rounded-lg hover:bg-[#c68b59] transition-colors duration-300" onclick="openEditSubcategoryModal(<?php echo $subcategory['id']; ?>, '<?php echo htmlspecialchars($subcategory['nom']); ?>', <?php echo $subcategory['categorie_id']; ?>)">Modifier</button>
                                    <button class="bg-[#e74c3c] text-white px-3 py-1 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="deleteSubcategory(<?php echo $subcategory['id']; ?>)">Supprimer</button>
                                </div>
                                <?php
                                $subcategoryProduits = array_filter($produits, function($prod) use ($subcategory) {
                                    return $prod['sous_categorie_id'] == $subcategory['id'];
                                });

                                foreach ($subcategoryProduits as $produit):
                                ?>
                                    <div class="product flex justify-between items-center p-2 border-b border-dashed border-[#d4a373] hover:bg-[#f9e8d2] hover:transform hover:translate-x-2 transition-all duration-300">
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
                                    <div class="admin-actions flex gap-2 p-2">
                                        <button class="bg-[#d4a373] text-white px-3 py-1 rounded-lg hover:bg-[#c68b59] transition-colors duration-300" onclick="openEditProductModal(<?php echo $produit['id']; ?>, '<?php echo htmlspecialchars($produit['nom']); ?>', <?php echo isset($produit['prix']) ? $produit['prix'] : 0; ?>, '<?php echo htmlspecialchars($produit['description'] ?? ''); ?>', <?php echo $produit['sous_categorie_id']; ?>)">Modifier</button>
                                        <button class="bg-[#e74c3c] text-white px-3 py-1 rounded-lg hover:bg-[#c0392b] transition-colors duration-300" onclick="deleteProduct(<?php echo $produit['id']; ?>)">Supprimer</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modales pour ajouter/modifier -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <h2 class="font-['Playfair_Display'] text-2xl mb-4">Ajouter une Catégorie</h2>
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
            <h2 class="font-['Playfair_Display'] text-2xl mb-4">Modifier une Catégorie</h2>
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
            <h2 class="font-['Playfair_Display'] text-2xl mb-4">Ajouter une Sous-Catégorie</h2>
            <form method="POST">
                <select name="category_id" class="w-full p-2 mb-4 border border-[#d4a373] rounded-md" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
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
            <h2 class="font-['Playfair_Display'] text-2xl mb-4">Modifier une Sous-Catégorie</h2>
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
            <h2 class="font-['Playfair_Display'] text-2xl mb-4">Ajouter un Produit</h2>
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
            <h2 class="font-['Playfair_Display'] text-2xl mb-4">Modifier un Produit</h2>
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
    </script>
</body>
</html>