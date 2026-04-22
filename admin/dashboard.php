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

    $query_product = mysqli_query($conn, "SELECT * FROM product WHERE outlet_code = '$outlet_code'");
    $query_outlet = mysqli_query($conn, "SELECT * FROM outlet WHERE outlet_code = '$outlet_code'");

    $outlet = mysqli_fetch_assoc($query_outlet);

    // Handle delete alert
    $deleted = isset($_GET['deleted']) && $_GET['deleted'] === 'true';
    $delete_error = isset($_GET['error']) && $_GET['error'] === 'true';
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
        <?php if($deleted): ?>
          <div
            id="alert-deleted"
            class="alert alert-success d-flex justify-content-between align-items-center"
            role="alert"
          > 
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-check-circle"></i>
              <p class="my-0">Product deleted successfully.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        <?php if($delete_error): ?>
          <div
            id="alert-delete-error"
            class="alert alert-danger d-flex justify-content-between align-items-center"
            role="alert"
          > 
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-exclamation-octagon"></i>
              <p class="my-0">Failed to delete product.</p>
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
              class="w-100 btn btn-warning text-start px-4 py-3 d-flex align-items-center gap-3"
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
              class="w-100 btn btn-light text-start px-4 py-3 d-flex align-items-center gap-3"
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

    <div class="container pb-5 px-4">
      <div class="mb-4">
        <h2 class="fredoka-font-medium">Selamat datang, Admin</h2>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="bg-white rounded-4 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-3">
                <div
                  class="bg-warning rounded-circle d-flex justify-content-center align-items-center"
                  style="width: 55px; height: 55px"
                >
                  <i class="bi bi-fork-knife fs-4"></i>
                </div>
                <div>
                  <p class="mb-1 text-muted">Total Seats</p>
                  <h4 class="mb-0 fredoka-font-medium"><?= $outlet['total_tables'] ?></h4>
                </div>
              </div>

              <button
                class="btn btn-warning rounded-circle d-flex align-items-center justify-content-center"
                style="width: 42px; height: 42px"
              >
                <i class="bi bi-pencil-square"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="bg-white rounded-4 shadow-sm p-4">
            <div class="d-flex align-items-center gap-3">
              <div
                class="bg-warning rounded-circle d-flex justify-content-center align-items-center"
                style="width: 55px; height: 55px"
              >
                <i class="bi bi-shop fs-4"></i>
              </div>
              <div>
                <p class="mb-1 text-muted">Outlet Code</p>
                <h4 class="mb-0 fredoka-font-medium"><?= $outlet['outlet_code'] ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Product Card -->
        <?php while($product = mysqli_fetch_assoc($query_product)):?>
          <div class="col-lg-4 col-md-6">
            <div class="bg-light rounded-4 shadow-sm p-4 h-100">
              <div class="d-flex gap-3 align-items-start">
                <img
                  src="data:image/jpeg;base64,<?= base64_encode($product['image']); ?>"
                  alt="<?= $product['product_name']; ?>"
                  style="width: 110px"
                  class="object-fit-contain"
                />

                <div class="w-100">
                  <h5 class="fredoka-font-medium mb-2"><?= $product['product_name']; ?></h5>
                  <p class="mb-2 text-muted">
                    <?= $product['description']; ?>
                  </p>
                  <p class="mb-0 fredoka-font">
                    Stock large: <?= $product['stock_large']; ?>
                  </p>
                  <p class="mb-4 fredoka-font">
                    Stock small: <?= $product['stock_small']; ?>
                  </p>

                  <div class="d-flex justify-content-between align-items-center gap-2">
                    <h4 class="mb-0 fredoka-font-medium">Rp. <?= number_format($product['price']); ?></h4>

                    <div class="d-flex gap-2">
                      <a href="edit-product.php?id=<?= $product['id']; ?>" class="link-underline link-underline-opacity-0">
                        <button
                          class="btn btn-warning rounded-circle d-flex align-items-center justify-content-center"
                          style="width: 45px; height: 45px"
                          type="button"
                          title="Edit"
                        >
                          <i class="bi bi-pencil-square fs-6"></i>
                        </button>
                      </a>
                      <button
                        class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 45px; height: 45px"
                        type="button"
                        title="Delete"
                        onclick="confirmDelete(<?= $product['id']; ?>, '<?= addslashes($product['product_name']); ?>')"
                      >
                        <i class="bi bi-trash fs-6"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <script src="../assets/js/bootstrap.js"></script>
    <script>
      function confirmDelete(productId, productName) {
        if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
          window.location.href = `../controller/delete-product.php?id=${productId}`;
        }
      }
    </script>
  </body>
</html>
