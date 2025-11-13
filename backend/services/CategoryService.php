<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/BaseDao.php';
require_once __DIR__ . '/../config.php';

class CategoryService extends BaseService {
    public function __construct() {
        $dao = new CategoryDao();
        parent::__construct($dao);
    }

    /**
     * Create a new category with validation
     */
    public function create($data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules
        $validator->required('name', $data['name'] ?? null);
        
        if (isset($data['name'])) {
            $validator->minLength('name', $data['name'], 2);
            $validator->maxLength('name', $data['name'], 45);
            
            // Check if category name already exists
            $existing = $this->dao->getByName($data['name']);
            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'Category name already exists',
                    'errors' => ['name' => 'This category name is already taken']
                ];
            }
        }

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        // Set timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::create($data);
    }

    /**
     * Update category with validation
     */
    public function update($id, $data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules for update
        if (isset($data['name'])) {
            $validator->minLength('name', $data['name'], 2);
            $validator->maxLength('name', $data['name'], 45);
            
            // Check if category name is already taken by another category
            $existing = $this->dao->getByName($data['name']);
            if ($existing && $existing['id'] != $id) {
                return [
                    'success' => false,
                    'message' => 'Category name already exists',
                    'errors' => ['name' => 'This category name is already taken']
                ];
            }
        }

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::update($id, $data);
    }

    /**
     * Get category by name
     */
    public function getByName($name) {
        try {
            if (empty($name)) {
                return [
                    'success' => false,
                    'message' => 'Category name is required'
                ];
            }

            $category = $this->dao->getByName($name);
            
            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found'
                ];
            }

            return [
                'success' => true,
                'data' => $category
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve category: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get categories with product count
     */
    public function getWithProductCount() {
        try {
            $categories = $this->dao->getWithProductCount();
            
            return [
                'success' => true,
                'data' => $categories
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve categories: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete category (with check for associated products)
     */
    public function delete($id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid ID provided'
                ];
            }

            // Check if category exists
            $category = $this->dao->getById($id);
            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found'
                ];
            }

            // Check if category has products
            require_once __DIR__ . '/../dao/ProductDao.php';
            $productDao = new ProductDao();
            $products = $productDao->getByCategory($id);
            
            if (!empty($products)) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete category with associated products',
                    'errors' => ['category' => 'Please remove or reassign all products before deleting this category']
                ];
            }

            $result = $this->dao->delete($id);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Category deleted successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to delete category'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ];
        }
    }
}
?>
