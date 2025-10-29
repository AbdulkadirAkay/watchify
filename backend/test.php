<?php
require_once './config.php';
require_once './dao/BaseDao.php';
require_once './dao/UserDao.php';
require_once './dao/CategoryDao.php';
require_once './dao/ProductDao.php';
require_once './dao/OrderDao.php';
require_once './dao/OrderProductDao.php';

echo "<h1>Watchify DAO CRUD Operations Test</h1>";

// Initialize DAOs
$userDao = new UserDao();
$categoryDao = new CategoryDao();
$productDao = new ProductDao();
$orderDao = new OrderDao();
$orderProductDao = new OrderProductDao();

echo "<h2>1. UserDao CRUD Operations</h2>";

// Create User
echo "<h3>Creating Users:</h3>";
$user1 = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('password123', PASSWORD_DEFAULT),
    'is_admin' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$user2 = [
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
    'is_admin' => 1,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$user1Result = $userDao->insert($user1);
$user2Result = $userDao->insert($user2);
echo "User 1 created with ID: " . ($user1Result ? $user1Result : "Failed") . "<br>";
echo "User 2 created with ID: " . ($user2Result ? $user2Result : "Failed") . "<br>";

// Read Users
echo "<h3>Reading Users:</h3>";
$allUsers = $userDao->getAll();
echo "Total users: " . count($allUsers) . "<br>";

$userByEmail = $userDao->getByEmail('john@example.com');
echo "User by email: " . ($userByEmail ? $userByEmail['name'] : "Not found") . "<br>";

// Update User
echo "<h3>Updating User:</h3>";
if ($userByEmail) {
    $updateResult = $userDao->update($userByEmail['id'], ['name' => 'John Updated', 'updated_at' => date('Y-m-d H:i:s')]);
    echo "User update: " . ($updateResult ? "Success" : "Failed") . "<br>";
}



echo "<hr>";

echo "<h2>2. CategoryDao CRUD Operations</h2>";

// Create Categories
echo "<h3>Creating Categories:</h3>";
$category1 = [
    'name' => 'Luxury Watches',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$category2 = [
    'name' => 'Sports Watches',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$category3 = [
    'name' => 'Smart Watches',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$cat1Result = $categoryDao->insert($category1);
$cat2Result = $categoryDao->insert($category2);
$cat3Result = $categoryDao->insert($category3);
echo "Category 1 created with ID: " . ($cat1Result ? $cat1Result : "Failed") . "<br>";
echo "Category 2 created with ID: " . ($cat2Result ? $cat2Result : "Failed") . "<br>";
echo "Category 3 created with ID: " . ($cat3Result ? $cat3Result : "Failed") . "<br>";

// Read Categories
echo "<h3>Reading Categories:</h3>";
$allCategories = $categoryDao->getAll();
echo "Total categories: " . count($allCategories) . "<br>";

$categoryByName = $categoryDao->getByName('Luxury Watches');
echo "Category by name: " . ($categoryByName ? $categoryByName['name'] : "Not found") . "<br>";

$categoriesWithCount = $categoryDao->getWithProductCount();
echo "Categories with product count: <pre>" . print_r($categoriesWithCount, true) . "</pre><br>";

// Update Category
echo "<h3>Updating Category:</h3>";
if ($categoryByName) {
    $updateResult = $categoryDao->update($categoryByName['id'], ['name' => 'Premium Watches', 'updated_at' => date('Y-m-d H:i:s')]);
    echo "Category update: " . ($updateResult ? "Success" : "Failed") . "<br>";
}

echo "<hr>";

echo "<h2>3. ProductDao CRUD Operations</h2>";

// Create Products
echo "<h3>Creating Products:</h3>";

$product1 = [
    'name' => 'Rolex Submariner',
    'brand' => 'Rolex',
    'price' => 8500,
    'quantity' => 5,
    'image_url' => 'rolex_submariner.jpg',
    'description' => 'Classic diving watch',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'category_id' => $cat1Result
];

$product2 = [
    'name' => 'Omega Speedmaster',
    'brand' => 'Omega',
    'price' => 4200,
    'quantity' => 8,
    'image_url' => 'omega_speedmaster.jpg',
    'description' => 'Moon landing watch',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'category_id' => $cat2Result
];

$product3 = [
    'name' => 'Apple Watch Series 9',
    'brand' => 'Apple',
    'price' => 399,
    'quantity' => 15,
    'image_url' => 'apple_watch_9.jpg',
    'description' => 'Latest smartwatch',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'category_id' => $cat3Result
];

$prod1Result = $productDao->insert($product1);
$prod2Result = $productDao->insert($product2);
$prod3Result = $productDao->insert($product3);
echo "Product 1 created with ID: " . ($prod1Result ? $prod1Result : "Failed") . "<br>";
echo "Product 2 created with ID: " . ($prod2Result ? $prod2Result : "Failed") . "<br>";
echo "Product 3 created with ID: " . ($prod3Result ? $prod3Result : "Failed") . "<br>";

// Read Products
echo "<h3>Reading Products:</h3>";
$allProducts = $productDao->getAll();
echo "Total products: " . count($allProducts) . "<br>";

$productsByCategory = $productDao->getByCategory($cat1Result);
echo "Products in category " . $cat1Result . ": " . count($productsByCategory) . "<br>";

$productsByBrand = $productDao->getByBrand('Rolex');
echo "Rolex products: " . count($productsByBrand) . "<br>";

$availableProducts = $productDao->getAvailableProducts();
echo "Available products: " . count($availableProducts) . "<br>";

$newArrivals = $productDao->getNewArrivals(5);
echo "New arrivals: " . count($newArrivals) . "<br>";

// Update Product
echo "<h3>Updating Product:</h3>";
if (count($allProducts) > 0) {
    $firstProduct = $allProducts[0];
    $updateResult = $productDao->update($firstProduct['id'], ['price' => 9000, 'updated_at' => date('Y-m-d H:i:s')]);
    echo "Product update: " . ($updateResult ? "Success" : "Failed") . "<br>";
}

// Test quantity operations
echo "<h3>Testing Quantity Operations:</h3>";
if (count($allProducts) > 0) {
    $firstProduct = $allProducts[0];
    $quantityUpdate = $productDao->updateQuantity($firstProduct['id'], 10);
    echo "Quantity update: " . ($quantityUpdate ? "Success" : "Failed") . "<br>";
    
    $quantityDecrease = $productDao->decreaseQuantity($firstProduct['id'], 2);
    echo "Quantity decrease: " . ($quantityDecrease ? "Success" : "Failed") . "<br>";
}

echo "<hr>";

echo "<h2>4. OrderDao CRUD Operations</h2>";

// Create Orders
echo "<h3>Creating Orders:</h3>";
$order1 = [
    'created_at' => date('Y-m-d H:i:s'),
    'total_price' => 8500,
    'shipping_cost' => 50,
    'payment_method' => 'credit_card',
    'address' => '123 Main St, New York, NY 10001',
    'status' => 'pending',
    'updated_at' => date('Y-m-d H:i:s'),
    'user_id' => $user1Result
];

$order2 = [
    'created_at' => date('Y-m-d H:i:s'),
    'total_price' => 4200,
    'shipping_cost' => 30,
    'payment_method' => 'paypal',
    'address' => '456 Oak Ave, Los Angeles, CA 90210',
    'status' => 'completed',
    'updated_at' => date('Y-m-d H:i:s'),
    'user_id' => $user1Result
];

$order1Result = $orderDao->insert($order1);
$order2Result = $orderDao->insert($order2);
echo "Order 1 created with ID: " . ($order1Result ? $order1Result : "Failed") . "<br>";
echo "Order 2 created with ID: " . ($order2Result ? $order2Result : "Failed") . "<br>";

// Read Orders
echo "<h3>Reading Orders:</h3>";
$allOrders = $orderDao->getAll();
echo "Total orders: " . count($allOrders) . "<br>";

$ordersByUser = $orderDao->getByUserId($user1Result);
echo "Orders for user " . $user1Result . ": " . count($ordersByUser) . "<br>";

$ordersByStatus = $orderDao->getByStatus('pending');
echo "Pending orders: " . count($ordersByStatus) . "<br>";


$ordersWithUserInfo = $orderDao->getOrdersWithUserInfo();
echo "Orders with user info: " . count($ordersWithUserInfo) . "<br>";


// Update Order
echo "<h3>Updating Order:</h3>";
if (count($allOrders) > 0) {
    $firstOrder = $allOrders[0];
    $statusUpdate = $orderDao->updateStatus($firstOrder['id'], 'processing');
    echo "Order status update: " . ($statusUpdate ? "Success" : "Failed") . "<br>";
}

echo "<hr>";

echo "<h2>5. OrderProductDao CRUD Operations</h2>";

// Create Order Products
echo "<h3>Creating Order Products:</h3>";
$orderProduct1 = [
    'order_id' => $order1Result,
    'product_id' => $prod1Result,
    'quantity' => 1,
    'unit_price' => 8500,
    'created_at' => date('Y-m-d H:i:s')
];

$orderProduct2 = [
    'order_id' => $order2Result,
    'product_id' => $prod2Result,
    'quantity' => 1,
    'unit_price' => 4200,
    'created_at' => date('Y-m-d H:i:s')
];

$orderProduct3 = [
    'order_id' => $order2Result,
    'product_id' => $prod3Result,
    'quantity' => 2,
    'unit_price' => 399,
    'created_at' => date('Y-m-d H:i:s')
];

$op1Result = $orderProductDao->insert($orderProduct1);
$op2Result = $orderProductDao->insert($orderProduct2);
$op3Result = $orderProductDao->insert($orderProduct3);
echo "Order Product 1 created with ID: " . ($op1Result ? $op1Result : "Failed") . "<br>";
echo "Order Product 2 created with ID: " . ($op2Result ? $op2Result : "Failed") . "<br>";
echo "Order Product 3 created with ID: " . ($op3Result ? $op3Result : "Failed") . "<br>";

// Read Order Products
echo "<h3>Reading Order Products:</h3>";
$allOrderProducts = $orderProductDao->getAll();
echo "Total order products: " . count($allOrderProducts) . "<br>";

$orderProductsByOrder = $orderProductDao->getByOrderId($order1Result);
echo "Products in order " . $order1Result . ": " . count($orderProductsByOrder) . "<br>";

$orderProductsByProduct = $orderProductDao->getByProductId($prod1Result);
echo "Orders containing product " . $prod1Result . ": " . count($orderProductsByProduct) . "<br>";

$orderProductsWithDetails = $orderProductDao->getOrderProductsWithDetails($order1Result);
echo "Order products with details for order " . $order1Result . ": " . count($orderProductsWithDetails) . "<br>";

$orderTotal = $orderProductDao->getOrderTotal($order1Result);
echo "Total for order " . $order1Result . ": $" . $orderTotal . "<br>";

// Test Order Product Operations
echo "<h3>Testing Order Product Operations:</h3>";
$addProductResult = $orderProductDao->addProductToOrder($order2Result, $prod3Result, 1, 399);
echo "Add product to order: " . ($addProductResult ? "Success" : "Failed") . "<br>";


echo "<hr>";

echo "<h2>6. Advanced Queries Test</h2>";

echo "<h3>Testing Complex Queries:</h3>";

// Test popular products
$popularProducts = $productDao->getPopularProducts(3);
echo "Popular products: " . count($popularProducts) . "<br>";

// Test orders by date range
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');
$ordersByDateRange = $orderDao->getOrdersByDateRange($startDate, $endDate);
echo "Orders in last 30 days: " . count($ordersByDateRange) . "<br>";



// Delete User
echo "<h3>Deleting User:</h3>";
$deleteResult = $userDao->delete($userByEmail['id']);
echo "User delete: " . ($deleteResult ? "Success" : "Failed") . "<br>";



echo "<h2>Test Complete!</h2>";
echo "<p>All CRUD operations have been tested. Check the database to verify the data was created correctly.</p>";
?>


