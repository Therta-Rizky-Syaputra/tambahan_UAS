<?php
  session_start();
  require "../config/db.php";

  if (!isset($_SESSION["id"])) {
      header("Location: " . $BASE_URL . "auth.php");
      exit;
  }

  if ($_SESSION["role"] === "kasir") {
      header("Location: " . $BASE_URL . "kasir/dashboard.php");
      exit;
  }

  $outlet_code = $_SESSION['outlet_code'];
  $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;


  if (!$product_id) {
      header("Location: " . $BASE_URL . "admin/dashboard.php");
      exit;
  }

 
  $query = mysqli_query($conn, "SELECT * FROM product WHERE id = '$product_id' AND outlet_code = '$outlet_code'");
  
  if (mysqli_num_rows($query) === 0) {
      header("Location: " . $BASE_URL . "admin/dashboard.php");
      exit;
  }

  $product = mysqli_fetch_assoc($query);

  $error = false;
  $err_message = "";
  $success = false;

  if (isset($_POST['updateProduct'])) {
      $product_name = htmlspecialchars($_POST['product_name']);
      $description  = htmlspecialchars($_POST['description']);
      $stock_large  = htmlspecialchars($_POST['stock_large']);
      $stock_small  = htmlspecialchars($_POST['stock_small']);
      $category     = htmlspecialchars($_POST['category']);
      $price        = htmlspecialchars($_POST['price']);

      if (
          empty($product_name) ||
          empty($description) ||
          empty($stock_large) ||
          empty($stock_small) ||
          empty($category) ||
          empty($price)
      ) {
          $error = true;
          $err_message = "All fields must be filled.";
      } else {
        
          if (!empty($_FILES['image']['name'])) {
              $image = $_FILES['image'];
              $fileTmp  = $image['tmp_name'];
              $fileType = mime_content_type($fileTmp);

              $allowedTypes = [
                  'image/jpeg',
                  'image/png',
                  'image/webp'
              ];

              if (!in_array($fileType, $allowedTypes)) {
                  $error = true;
                  $err_message = "Only image files are allowed.";
              } elseif ($image['size'] > 10 * 1024 * 1024) {
                  $error = true;
                  $err_message = "Image size must not exceed 10MB.";
              } else {
                  $imageData = addslashes(file_get_contents($fileTmp));
                  
                  $update_query = mysqli_query($conn, "
                      UPDATE product
                      SET product_name = '$product_name',
                          description = '$description',
                          stock_large = '$stock_large',
                          stock_small = '$stock_small',
                          category = '$category',
                          price = '$price',
                          image = '$imageData'
                      WHERE id = '$product_id' AND outlet_code = '$outlet_code'
                  ");

                  if ($update_query) {
                      $success = true;
                      header("Refresh: 2; url=" . $BASE_URL . "admin/dashboard.php");
                  } else {
                      $error = true;
                      $err_message = "Failed to update product.";
                  }
              }
          } else {
              
              $update_query = mysqli_query($conn, "
                  UPDATE product
                  SET product_name = '$product_name',
                      description = '$description',
                      stock_large = '$stock_large',
                      stock_small = '$stock_small',
                      category = '$category',
                      price = '$price'
                  WHERE id = '$product_id' AND outlet_code = '$outlet_code'
              ");

              if ($update_query) {
                  $success = true;
                  header("Refresh: 2; url=" . $BASE_URL . "admin/dashboard.php");
              } else {
                  $error = true;
                  $err_message = "Failed to update product.";
              }
          }
      }
  }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Product | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../assets/css/bootstrap.css" />
    <link rel="stylesheet" href="../assets/styles.css" />
    <link
      rel="stylesheet"
      href="../assets/bootstrap-icons/bootstrap-icons.css"
    />
    <link rel="icon" href="../public/logo.png" />
  </head>
  <body class="bg-cream">
    <!-- alert -->
    <div class="position-fixed z-3 alert-main end-0 top-0">
      <div class="p-2">
        <?php if($error): ?>
          <div
            id="alert-payment"
            class="alert alert-danger d-flex justify-content-between align-items-center"
            role="alert"
          > 
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-exclamation-octagon"></i>
              <p class="my-0"><?= $err_message; ?></p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        <?php if($success): ?>
          <div
            id="alert-payment"
            class="alert alert-success d-flex justify-content-between align-items-center"
            role="alert"
          > 
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-check-circle"></i>
              <p class="my-0">Product updated successfully. Redirecting...</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="w-100 p-4">
      <a href="dashboard.php" class="link-underline link-underline-opacity-0">
        <button class="btn btn-dark rounded-5">
          <i class="bi bi-arrow-left fs-5"></i>
        </button>
      </a>
    </div>

    <div class="container px-4 pb-5">
      <div class="mb-4">
        <h2 class="fredoka-font-medium">Edit Product</h2>
      </div>

      <div class="bg-white shadow rounded-4 p-4">
        <form id="editProductForm" enctype="multipart/form-data" method="POST" action="">
          <!-- Nama Product -->
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Product Name</label>
            <input
              type="text"
              name="product_name"
              class="form-control rounded-3"
              placeholder="Enter product name"
              value="<?= htmlspecialchars($product['product_name']); ?>"
              required
            />
          </div>

          <!-- Deskripsi -->
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Description</label>
            <textarea
              name="description"
              rows="4"
              class="form-control rounded-3"
              placeholder="Enter product description"
              required
            ><?= htmlspecialchars($product['description']); ?></textarea>
          </div>

          <!-- Stok -->
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Stock Large</label>
            <input
              type="number"
              name="stock_large"
              class="form-control rounded-3"
              placeholder="Enter stock"
              value="<?= $product['stock_large']; ?>"
              required
            />
          </div>
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Stock Small</label>
            <input
              type="number"
              name="stock_small"
              class="form-control rounded-3"
              placeholder="Enter stock"
              value="<?= $product['stock_small']; ?>"
              required
            />
          </div>

          <!-- Jenis -->
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Category</label>
            <select name="category" class="form-select rounded-3" required>
              <option value="">Select category</option>
              <option value="makanan" <?= $product['category'] === 'makanan' ? 'selected' : ''; ?>>Makanan</option>
              <option value="minuman" <?= $product['category'] === 'minuman' ? 'selected' : ''; ?>>Minuman</option>
            </select>
          </div>

          <!-- Harga -->
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Price</label>
            <input
              type="number"
              name="price"
              class="form-control rounded-3"
              placeholder="Enter price"
              value="<?= $product['price']; ?>"
              required
            />
          </div>

          <!-- Gambar -->
          <div class="mb-4">
            <label class="form-label fredoka-font-medium">Product Image (Optional)</label>
            <div class="mb-3">
              <img
                src="data:image/jpeg;base64,<?= base64_encode($product['image']); ?>"
                alt="<?= $product['product_name']; ?>"
                style="max-width: 200px; max-height: 200px;"
                class="img-thumbnail rounded-3"
              />
            </div>
            <input
              type="file"
              name="image"
              class="form-control rounded-3"
              accept="image/*"
            />
            <small class="text-muted">Leave empty to keep current image</small>
          </div>

          <!-- Tombol -->
          <div class="d-flex justify-content-end gap-2 fredoka-font">
            <a href="dashboard.php" class="btn btn-secondary rounded-3 px-4">
              Cancel
            </a>
            <button type="submit" name="updateProduct" class="btn btn-warning rounded-3 px-4">
              Update Product
            </button>
          </div>
        </form>
      </div>
    </div>

    <script src="../assets/js/bootstrap.js"></script>
  </body>
</html>
