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

  $error = false;
  $err_message = "";

  if (isset($_POST['addProduct'])) {
      $product_name = htmlspecialchars($_POST['product_name']);
      $description  = htmlspecialchars($_POST['description']);
      $stock_large  = htmlspecialchars($_POST['stock_large']);
      $stock_small  = htmlspecialchars($_POST['stock_small']);
      $category     = htmlspecialchars($_POST['category']);
      $price        = htmlspecialchars($_POST['price']);
      $image        = $_FILES['image'];

      if (
          empty($product_name) ||
          empty($description) ||
          empty($stock_large) ||
          empty($stock_small) ||
          empty($category) ||
          empty($price) ||
          empty($image['name'])
      ) {
          $error = true;
          $err_message = "All fields must be filled.";
      } else {
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

              $query = mysqli_query($conn, "
                  INSERT INTO product
                  (outlet_code, product_name, description, stock_large, stock_small, category, price, image)
                  VALUES
                  ('$outlet_code','$product_name', '$description', '$stock_large', '$stock_small', '$category', '$price', '$imageData')
              ");

              if ($query) {
                  header("Location: " . $BASE_URL . "admin/dashboard.php");
                  exit;
              } else {
                  $error = true;
                  $err_message = "Failed to add product.";
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
    <title>Dashboard | Admin</title>
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
      </div>
    </div>

    <div class="w-100 p-4">
      <button
        class="btn btn-dark rounded-5"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasExample"
        aria-controls="offcanvasExample"
      >
        <i class="bi bi-list fs-5"></i>
      </button>
    </div>

    <div
      class="offcanvas offcanvas-start fredoka-font"
      tabindex="-1"
      id="offcanvasExample"
      aria-labelledby="offcanvasExampleLabel"
    >
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">Admin</h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="offcanvas"
          aria-label="Close"
        ></button>
      </div>
      <div
        class="offcanvas-body px-4 d-flex flex-column justify-content-between"
      >
        <div class="d-flex flex-column gap-2 justify-content-start">
          <a
            href="dashboard.php"
            class="w-100 link-underline link-underline-opacity-0"
          >
            <button
              class="w-100 btn btn-light text-start px-4 py-3 d-flex align-items-center gap-3"
            >
              <i class="bi bi-archive-fill"></i>
              Dashboard
            </button>
          </a>
          <a
            href="add-product.php"
            class="w-100 link-underline link-underline-opacity-0"
          >
            <button
              class="w-100 btn btn-warning text-start px-4 py-3 d-flex align-items-center gap-3"
            >
              <i class="bi bi-plus"></i>
              Add Product
            </button>
          </a>
        </div>
        <a href="../controller/logout.php" class="link-underline link-underline-opacity-0">
          <button
            class="w-100 btn btn-outline-danger text-start px-4 py-3 d-flex align-items-center gap-3"
          >
            <i class="bi bi-box-arrow-left"></i>
            Logout
          </button>
        </a>
      </div>
    </div>

    <div class="container px-4 pb-5">
      <div class="mb-4">
        <h2 class="fredoka-font-medium">Add Product</h2>
      </div>

      <div class="bg-white shadow rounded-4 p-4">
        <form id="addProductForm" enctype="multipart/form-data" method="POST" action="">
          <!-- Nama Product -->
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Product Name</label>
            <input
              type="text"
              name="product_name"
              class="form-control rounded-3"
              placeholder="Enter product name"
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
            ></textarea>
          </div>

          <!-- Stok -->
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Stock Large</label>
            <input
              type="number"
              name="stock_large"
              class="form-control rounded-3"
              placeholder="Enter stock"
            />
          </div>
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Stock Small</label>
            <input
              type="number"
              name="stock_small"
              class="form-control rounded-3"
              placeholder="Enter stock"
            />
          </div>

          <!-- Jenis -->
          <div class="mb-3">
            <label class="form-label fredoka-font-medium">Category</label>
            <select name="category" class="form-select rounded-3">
              <option value="">Select category</option>
              <option value="makanan">Makanan</option>
              <option value="minuman">Minuman</option>
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
            />
          </div>

          <!-- Gambar -->
          <div class="mb-4">
            <label class="form-label fredoka-font-medium">Product Image</label>
            <input
              type="file"
              name="image"
              class="form-control rounded-3"
              accept="image/*"
            />
          </div>

          <!-- Tombol -->
          <div class="d-flex justify-content-end gap-2 fredoka-font">
            <button type="submit" name="addProduct" class="btn btn-dark rounded-3 px-4">
              Save Product
            </button>
          </div>
        </form>
      </div>
    </div>

    <script src="../assets/js/bootstrap.js"></script>
  </body>
</html>
