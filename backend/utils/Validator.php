<?php

class Validator {
    private $errors = [];

    /**
     * Validate required fields
     */
    public function required($field, $value, $message = null) {
        if (empty($value) && $value !== '0' && $value !== 0) {
            $this->errors[$field] = $message ?? ucfirst($field) . " is required";
            return false;
        }
        return true;
    }

    /**
     * Validate email format
     */
    public function email($field, $value, $message = null) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Invalid email format";
            return false;
        }
        return true;
    }

    /**
     * Validate minimum length
     */
    public function minLength($field, $value, $min, $message = null) {
        if (strlen($value) < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least {$min} characters";
            return false;
        }
        return true;
    }

    /**
     * Validate maximum length
     */
    public function maxLength($field, $value, $max, $message = null) {
        if (strlen($value) > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must not exceed {$max} characters";
            return false;
        }
        return true;
    }

    /**
     * Validate numeric value
     */
    public function numeric($field, $value, $message = null) {
        if (!is_numeric($value)) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be a number";
            return false;
        }
        return true;
    }

    /**
     * Validate positive number
     */
    public function positive($field, $value, $message = null) {
        if (!is_numeric($value) || $value <= 0) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be a positive number";
            return false;
        }
        return true;
    }

    /**
     * Validate integer
     */
    public function integer($field, $value, $message = null) {
        if (!is_numeric($value) || !is_int((int)$value) || (int)$value != $value) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be an integer";
            return false;
        }
        return true;
    }

    /**
     * Validate minimum value
     */
    public function min($field, $value, $min, $message = null) {
        if (is_numeric($value) && $value < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least {$min}";
            return false;
        }
        return true;
    }

    /**
     * Validate maximum value
     */
    public function max($field, $value, $max, $message = null) {
        if (is_numeric($value) && $value > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must not exceed {$max}";
            return false;
        }
        return true;
    }

    /**
     * Validate value is in array
     */
    public function in($field, $value, $allowed, $message = null) {
        if (!in_array($value, $allowed)) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be one of: " . implode(", ", $allowed);
            return false;
        }
        return true;
    }

    /**
     * Validate URL format
     */
    public function url($field, $value, $message = null) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = $message ?? "Invalid URL format";
            return false;
        }
        return true;
    }

    /**
     * Get all validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Check if validation passed
     */
    public function isValid() {
        return empty($this->errors);
    }

    /**
     * Clear all errors
     */
    public function clear() {
        $this->errors = [];
    }

    /**
     * Get first error message
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}

?>
