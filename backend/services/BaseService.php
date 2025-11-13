<?php
require_once __DIR__ . '/../dao/BaseDao.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils/Validator.php';

class BaseService {
    protected $dao;
    protected $validator;

    public function __construct($dao) {
        $this->dao = $dao;
        $this->validator = new Validator();
    }

    /**
     * Get all records
     */
    public function getAll() {
        try {
            return [
                'success' => true,
                'data' => $this->dao->getAll()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve records: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get record by ID
     */
    public function getById($id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid ID provided'
                ];
            }

            $record = $this->dao->getById($id);
            
            if (!$record) {
                return [
                    'success' => false,
                    'message' => 'Record not found'
                ];
            }

            return [
                'success' => true,
                'data' => $record
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve record: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new record (to be overridden by child classes with validation)
     */
    public function create($data) {
        try {
            $id = $this->dao->insert($data);
            
            if ($id) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Record created successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create record'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create record: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update record (to be overridden by child classes with validation)
     */
    public function update($id, $data) {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid ID provided'
                ];
            }

            // Check if record exists
            $existing = $this->dao->getById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Record not found'
                ];
            }

            $result = $this->dao->update($id, $data);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Record updated successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to update record'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update record: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete record
     */
    public function delete($id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid ID provided'
                ];
            }

            // Check if record exists
            $existing = $this->dao->getById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Record not found'
                ];
            }

            $result = $this->dao->delete($id);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Record deleted successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to delete record'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete record: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get validator instance
     */
    protected function getValidator() {
        return $this->validator;
    }
}
?>
