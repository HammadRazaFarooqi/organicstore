// Admin Dashboard JavaScript

$(document).ready(function () {
  // Initialize dashboard
  initDashboard();

  // Initialize charts if on dashboard page
  if ($('#dashboardChart').length) {
    initDashboardChart();
  }

  // Initialize data tables
  if ($('.data-table').length) {
    $('.data-table').DataTable({
      responsive: true,
      pageLength: 10,
      order: [[0, 'desc']],
    });
  }

  // Form validations
  initFormValidations();

  // Image preview
  initImagePreview();

  // Confirm delete actions
  initDeleteConfirmation();

  // Auto-hide alerts
  setTimeout(function () {
    $('.alert').fadeOut('slow');
  }, 5000);
});

// Initialize Dashboard
function initDashboard() {
  // Sidebar toggle for mobile
  $('.sidebar-toggle').on('click', function () {
    $('.main-sidebar').toggleClass('active');
  });

  // Active menu highlighting
  var currentPath = window.location.pathname;
  $('.sidebar-menu a').each(function () {
    var href = $(this).attr('href');
    if (currentPath.includes(href) && href !== '#') {
      $(this).addClass('active');
    }
  });

  // Auto refresh dashboard stats every 30 seconds
  if ($('.info-box').length) {
    setInterval(refreshDashboardStats, 30000);
  }
}

// Initialize Dashboard Chart
function initDashboardChart() {
  var ctx = document.getElementById('dashboardChart').getContext('2d');

  // Sample data - replace with actual data from PHP
  var chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [
        {
          label: 'Sales',
          data: [12, 19, 3, 5, 2, 3],
          borderColor: 'rgb(102, 126, 234)',
          backgroundColor: 'rgba(102, 126, 234, 0.1)',
          tension: 0.4,
        },
        {
          label: 'Orders',
          data: [7, 11, 5, 8, 3, 7],
          borderColor: 'rgb(86, 171, 47)',
          backgroundColor: 'rgba(86, 171, 47, 0.1)',
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

// Form Validations
function initFormValidations() {
  // Category form validation
  $('#categoryForm').on('submit', function (e) {
    var name = $('#categoryName').val().trim();
    if (name === '') {
      e.preventDefault();
      showAlert('Category name is required!', 'danger');
      return false;
    }
  });

  // Product form validation
  $('#productForm').on('submit', function (e) {
    var name = $('#productName').val().trim();
    var price = $('#productPrice').val();
    var category = $('#productCategory').val();

    if (name === '') {
      e.preventDefault();
      showAlert('Product name is required!', 'danger');
      return false;
    }

    if (price === '' || price <= 0) {
      e.preventDefault();
      showAlert('Valid product price is required!', 'danger');
      return false;
    }

    if (category === '') {
      e.preventDefault();
      showAlert('Please select a category!', 'danger');
      return false;
    }
  });

  // User form validation
  $('#userForm').on('submit', function (e) {
    var email = $('#userEmail').val();
    var password = $('#userPassword').val();

    if (!isValidEmail(email)) {
      e.preventDefault();
      showAlert('Please enter a valid email address!', 'danger');
      return false;
    }

    if (password.length < 6) {
      e.preventDefault();
      showAlert('Password must be at least 6 characters long!', 'danger');
      return false;
    }
  });

  // Discount form validation
  $('#discountForm').on('submit', function (e) {
    var code = $('#discountCode').val().trim();
    var percentage = $('#discountPercentage').val();
    var endDate = $('#discountEndDate').val();

    if (code === '') {
      e.preventDefault();
      showAlert('Discount code is required!', 'danger');
      return false;
    }

    if (percentage === '' || percentage <= 0 || percentage > 100) {
      e.preventDefault();
      showAlert('Valid discount percentage (1-100) is required!', 'danger');
      return false;
    }

    if (endDate === '') {
      e.preventDefault();
      showAlert('End date is required!', 'danger');
      return false;
    }
  });
}

// Image Preview
function initImagePreview() {
  $('#productImage').on('change', function () {
    var input = this;
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#imagePreview').attr('src', e.target.result).show();
      };
      reader.readAsDataURL(input.files[0]);
    }
  });
}

// Delete Confirmation
function initDeleteConfirmation() {
  $('.delete-btn').on('click', function (e) {
    e.preventDefault();
    var href = $(this).attr('href');
    var item = $(this).data('item') || 'item';

    if (
      confirm(
        'Are you sure you want to delete this ' +
          item +
          '? This action cannot be undone.'
      )
    ) {
      window.location.href = href;
    }
  });
}

// Utility Functions
function showAlert(message, type) {
  var alertClass = 'alert-' + type;
  var alertHtml =
    '<div class="alert ' +
    alertClass +
    ' alert-dismissible fade show" role="alert">' +
    message +
    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
    '<span aria-hidden="true">&times;</span>' +
    '</button>' +
    '</div>';

  $('.content-header').after(alertHtml);

  setTimeout(function () {
    $('.alert').fadeOut('slow');
  }, 5000);
}

function isValidEmail(email) {
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Refresh Dashboard Stats
function refreshDashboardStats() {
  $.ajax({
    url: 'actions/dashboard_stats.php',
    type: 'GET',
    dataType: 'json',
    success: function (data) {
      if (data.success) {
        $('#categoryCount').text(data.categories);
        $('#productCount').text(data.products);
        $('#userCount').text(data.users);
        $('#salesCount').text(data.sales);
      }
    },
    error: function () {
      console.log('Failed to refresh dashboard stats');
    },
  });
}

// AJAX Functions for CRUD Operations

// Category Functions
function addCategory() {
  var formData = new FormData($('#categoryForm')[0]);

  $.ajax({
    url: 'actions/category_actions.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        showAlert(response.message, 'success');
        $('#categoryModal').modal('hide');
        location.reload();
      } else {
        showAlert(response.message, 'danger');
      }
    },
    error: function () {
      showAlert('An error occurred. Please try again.', 'danger');
    },
  });
}

// Product Functions
function addProduct() {
  var formData = new FormData($('#productForm')[0]);

  $.ajax({
    url: 'actions/product_actions.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        showAlert(response.message, 'success');
        $('#productModal').modal('hide');
        location.reload();
      } else {
        showAlert(response.message, 'danger');
      }
    },
    error: function () {
      showAlert('An error occurred. Please try again.', 'danger');
    },
  });
}

// Toggle Product Status
function toggleProductStatus(productId, currentStatus) {
  var newStatus = currentStatus === 'active' ? 'inactive' : 'active';

  $.ajax({
    url: 'actions/product_actions.php',
    type: 'POST',
    data: {
      action: 'toggle_status',
      product_id: productId,
      status: newStatus,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        location.reload();
      } else {
        showAlert(response.message, 'danger');
      }
    },
    error: function () {
      showAlert('An error occurred. Please try again.', 'danger');
    },
  });
}

// User Functions
function addUser() {
  var formData = new FormData($('#userForm')[0]);

  $.ajax({
    url: 'actions/user_actions.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        showAlert(response.message, 'success');
        $('#userModal').modal('hide');
        location.reload();
      } else {
        showAlert(response.message, 'danger');
      }
    },
    error: function () {
      showAlert('An error occurred. Please try again.', 'danger');
    },
  });
}

// Discount Functions
function addDiscount() {
  var formData = new FormData($('#discountForm')[0]);

  $.ajax({
    url: 'actions/discount_actions.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        showAlert(response.message, 'success');
        $('#discountModal').modal('hide');
        location.reload();
      } else {
        showAlert(response.message, 'danger');
      }
    },
    error: function () {
      showAlert('An error occurred. Please try again.', 'danger');
    },
  });
}

// Search and Filter Functions
function searchTable() {
  var input = document.getElementById('searchInput');
  var filter = input.value.toLowerCase();
  var table = document.getElementById('dataTable');
  var tr = table.getElementsByTagName('tr');

  for (var i = 1; i < tr.length; i++) {
    var td = tr[i].getElementsByTagName('td');
    var found = false;

    for (var j = 0; j < td.length; j++) {
      if (td[j]) {
        var txtValue = td[j].textContent || td[j].innerText;
        if (txtValue.toLowerCase().indexOf(filter) > -1) {
          found = true;
          break;
        }
      }
    }

    tr[i].style.display = found ? '' : 'none';
  }
}

// Export Functions
function exportToCSV(tableId, filename) {
  var csv = [];
  var rows = document.querySelectorAll('#' + tableId + ' tr');

  for (var i = 0; i < rows.length; i++) {
    var row = [],
      cols = rows[i].querySelectorAll('td, th');

    for (var j = 0; j < cols.length - 1; j++) {
      // Exclude action column
      row.push(cols[j].innerText);
    }

    csv.push(row.join(','));
  }

  downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
  var csvFile;
  var downloadLink;

  csvFile = new Blob([csv], { type: 'text/csv' });
  downloadLink = document.createElement('a');
  downloadLink.download = filename;
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = 'none';
  document.body.appendChild(downloadLink);
  downloadLink.click();
}

// Print Function
function printTable(tableId) {
  var printWindow = window.open('', '', 'height=600,width=800');
  var table = document.getElementById(tableId).outerHTML;

  printWindow.document.write('<html><head><title>Print</title>');
  printWindow.document.write(
    '<style>table {border-collapse: collapse; width: 100%;} th, td {border: 1px solid #ddd; padding: 8px; text-align: left;} th {background-color: #f2f2f2;}</style>'
  );
  printWindow.document.write('</head><body>');
  printWindow.document.write(table);
  printWindow.document.write('</body></html>');
  printWindow.document.close();
  printWindow.print();
}

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  sidebar.classList.toggle('collapsed');
}
document.addEventListener('DOMContentLoaded', function () {
  const toggleBtn = document.querySelector('.sidebar-toggle');
  const sidebar = document.getElementById('sidebar');

  toggleBtn.addEventListener('click', function () {
    sidebar.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
  });
});
