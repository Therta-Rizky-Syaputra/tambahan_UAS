<?php
    session_start();
    require "../config/db.php";
    require "../controller/OrderController.php";

    if( !isset($_SESSION["id"]) ){
        header("location: " . $BASE_URL . 'auth.php');
        exit;
    }

    if ($_SESSION['role'] === "admin") {
        header("location: " . $BASE_URL . 'admin/dashboard.php');
        exit;
    }

    // Get outlet code from session
    $outlet_code = $_SESSION['outlet_code'] ?? 'BRG-1024'; // Default fallback

    // Initialize OrderController
    $orderController = new OrderController($conn, $outlet_code);

    // Get pending orders
    $pendingOrders = $orderController->getPendingOrders();

    // Get order stats
    $stats = $orderController->getOrderStats();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | Kasir</title>
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
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">Kasir</h5>
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
            href="finish.php"
            class="w-100 link-underline link-underline-opacity-0"
          >
            <button
              class="w-100 btn btn-light text-start px-4 py-3 d-flex align-items-center gap-3"
            >
              <i class="bi bi-calendar2-check"></i>
              Finish
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

    <div class="container py-4 pt-0">
      <!-- Outlet code -->
      <div class="d-flex justify-content-end mb-4">
        <div class="bg-dark text-white px-4 py-2 rounded-4 fredoka-font-medium">
          Outlet: <?php echo htmlspecialchars($outlet_code); ?>
        </div>
      </div>

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="bg-white rounded-4 shadow-sm p-3 text-center">
            <h3 class="text-warning mb-1"><?php echo $stats['stats']['pending_count']; ?></h3>
            <small class="text-muted">Pending Orders</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="bg-white rounded-4 shadow-sm p-3 text-center">
            <h3 class="text-success mb-1"><?php echo $stats['stats']['completed_count']; ?></h3>
            <small class="text-muted">Completed Today</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="bg-white rounded-4 shadow-sm p-3 text-center">
            <h3 class="text-danger mb-1"><?php echo $stats['stats']['cancelled_count']; ?></h3>
            <small class="text-muted">Cancelled</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="bg-white rounded-4 shadow-sm p-3 text-center">
            <h3 class="text-primary mb-1">Rp <?php echo number_format($stats['stats']['total_revenue'], 0, ',', '.'); ?></h3>
            <small class="text-muted">Revenue Today</small>
          </div>
        </div>
      </div>

      <!-- Order list -->
      <div class="row g-4">
        <?php if ($pendingOrders['success'] && !empty($pendingOrders['orders'])): ?>
          <?php foreach ($pendingOrders['orders'] as $order): ?>
            <div class="col-lg-4 col-md-6">
              <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                <!-- table number and order id -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="fredoka-font-medium mb-0">Table <?php echo htmlspecialchars($order['table_number']); ?></h4>
                  <span class="badge text-bg-warning px-3 py-2">Pending</span>
                </div>

                <!-- order id -->
                <div class="mb-3">
                  <small class="text-muted">Order ID: <?php echo htmlspecialchars($order['order_id']); ?></small>
                </div>

                <!-- ordered items -->
                <div class="mb-4">
                  <?php
                  $items = explode('; ', $order['items']);
                  foreach ($items as $item):
                  ?>
                    <div class="d-flex justify-content-between mb-2">
                      <div>
                        <p class="mb-0 fredoka-font-medium"><?php echo htmlspecialchars($item); ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- total and payment method -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Payment:</span>
                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total:</span>
                    <span class="fw-bold text-success">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                  </div>
                </div>

                <!-- finish button -->
                <button class="btn btn-success w-100 rounded-3" onclick="completeOrder(<?php echo $order['id']; ?>)">
                  <i class="bi bi-check-circle me-2"></i>
                  Complete Order
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="bg-white rounded-4 shadow-sm p-5 text-center">
              <i class="bi bi-receipt-x fs-1 text-muted mb-3"></i>
              <h5 class="text-muted">No pending orders</h5>
              <p class="text-muted mb-0">All orders have been processed</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <script src="../assets/js/bootstrap.js"></script>
    <script>
      function completeOrder(orderId) {
        if (confirm('Are you sure you want to complete this order?')) {
          fetch('../controller/complete-order.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: orderId })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Order completed successfully!');
              location.reload();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the order');
          });
        }
      }
    </script>
  </body>
</html>
