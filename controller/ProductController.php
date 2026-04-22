<?php

class ProductController {
    private $conn;
    private $outlet_code;

    public function __construct($conn, $outlet_code) {
        $this->conn = $conn;
        $this->outlet_code = $outlet_code;
    }

  
    public function createProduct($data, $image) {
        $product_name = mysqli_real_escape_string($this->conn, htmlspecialchars($data['product_name']));
        $description = mysqli_real_escape_string($this->conn, htmlspecialchars($data['description']));
        $stock_large = intval($data['stock_large']);
        $stock_small = intval($data['stock_small']);
        $category = mysqli_real_escape_string($this->conn, htmlspecialchars($data['category']));
        $price = intval($data['price']);

        // Validate inputs
        if (empty($product_name) || empty($description) || $stock_large < 0 || $stock_small < 0 || empty($category) || $price < 0) {
            return [
                'success' => false,
                'message' => 'Invalid input data.'
            ];
        }

        // Validate image
        if (empty($image['name'])) {
            return [
                'success' => false,
                'message' => 'Image is required.'
            ];
        }

        $fileTmp = $image['tmp_name'];
        $fileType = mime_content_type($fileTmp);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($fileType, $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Only image files (JPEG, PNG, WebP) are allowed.'
            ];
        }

        if ($image['size'] > 10 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => 'Image size must not exceed 10MB.'
            ];
        }

        // Insert product
        $imageData = addslashes(file_get_contents($fileTmp));
        
        $query = mysqli_query($this->conn, "
            INSERT INTO product 
            (outlet_code, product_name, description, stock_large, stock_small, category, price, image)
            VALUES 
            ('$this->outlet_code', '$product_name', '$description', '$stock_large', '$stock_small', '$category', '$price', '$imageData')
        ");

        if ($query) {
            return [
                'success' => true,
                'message' => 'Product created successfully.',
                'product_id' => mysqli_insert_id($this->conn)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create product: ' . mysqli_error($this->conn)
            ];
        }
    }

   
    public function getAllProducts() {
        $query = mysqli_query($this->conn, "SELECT * FROM product WHERE outlet_code = '$this->outlet_code' ORDER BY id DESC");
        
        if (!$query) {
            return [
                'success' => false,
                'message' => 'Failed to fetch products.',
                'products' => []
            ];
        }

        $products = [];
        while ($product = mysqli_fetch_assoc($query)) {
            $products[] = $product;
        }

        return [
            'success' => true,
            'products' => $products
        ];
    }

   
    public function getProductById($product_id) {
        $product_id = intval($product_id);
        $query = mysqli_query($this->conn, "SELECT * FROM product WHERE id = '$product_id' AND outlet_code = '$this->outlet_code'");
        
        if (!$query || mysqli_num_rows($query) === 0) {
            return [
                'success' => false,
                'message' => 'Product not found.',
                'product' => null
            ];
        }

        $product = mysqli_fetch_assoc($query);
        return [
            'success' => true,
            'product' => $product
        ];
    }

    
    public function updateProduct($product_id, $data, $image = null) {
        $product_id = intval($product_id);
        
        // Check if product exists and belongs to outlet
        $check = $this->getProductById($product_id);
        if (!$check['success']) {
            return [
                'success' => false,
                'message' => 'Product not found.'
            ];
        }

        $product_name = mysqli_real_escape_string($this->conn, htmlspecialchars($data['product_name']));
        $description = mysqli_real_escape_string($this->conn, htmlspecialchars($data['description']));
        $stock_large = intval($data['stock_large']);
        $stock_small = intval($data['stock_small']);
        $category = mysqli_real_escape_string($this->conn, htmlspecialchars($data['category']));
        $price = intval($data['price']);

        
        if (empty($product_name) || empty($description) || $stock_large < 0 || $stock_small < 0 || empty($category) || $price < 0) {
            return [
                'success' => false,
                'message' => 'Invalid input data.'
            ];
        }

        
        if ($image && !empty($image['name'])) {
            $fileTmp = $image['tmp_name'];
            $fileType = mime_content_type($fileTmp);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

            if (!in_array($fileType, $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Only image files (JPEG, PNG, WebP) are allowed.'
                ];
            }

            if ($image['size'] > 10 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'Image size must not exceed 10MB.'
                ];
            }

            $imageData = addslashes(file_get_contents($fileTmp));
            
            $query = mysqli_query($this->conn, "
                UPDATE product 
                SET product_name = '$product_name',
                    description = '$description',
                    stock_large = '$stock_large',
                    stock_small = '$stock_small',
                    category = '$category',
                    price = '$price',
                    image = '$imageData'
                WHERE id = '$product_id' AND outlet_code = '$this->outlet_code'
            ");
        } else {
            // Update without image
            $query = mysqli_query($this->conn, "
                UPDATE product 
                SET product_name = '$product_name',
                    description = '$description',
                    stock_large = '$stock_large',
                    stock_small = '$stock_small',
                    category = '$category',
                    price = '$price'
                WHERE id = '$product_id' AND outlet_code = '$this->outlet_code'
            ");
        }

        if ($query) {
            return [
                'success' => true,
                'message' => 'Product updated successfully.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update product: ' . mysqli_error($this->conn)
            ];
        }
    }

    /**
     * DELETE - Delete product
     */
    public function deleteProduct($product_id) {
        $product_id = intval($product_id);

        // Check if product exists and belongs to outlet
        $check = $this->getProductById($product_id);
        if (!$check['success']) {
            return [
                'success' => false,
                'message' => 'Product not found.'
            ];
        }

        $query = mysqli_query($this->conn, "DELETE FROM product WHERE id = '$product_id' AND outlet_code = '$this->outlet_code'");

        if ($query) {
            return [
                'success' => true,
                'message' => 'Product deleted successfully.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to delete product: ' . mysqli_error($this->conn)
            ];
        }
    }
}
?>
