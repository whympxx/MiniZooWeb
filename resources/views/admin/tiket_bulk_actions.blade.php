<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.csrf')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Actions - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/admin-tiket-bulk-actions.css') }}">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -30px, 0);
            }
            70% {
                transform: translate3d(0, -15px, 0);
            }
            90% {
                transform: translate3d(0, -4px, 0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .animate-slide-in-right {
            animation: slideInRight 0.6s ease-out;
        }
        
        .animate-pulse-slow {
            animation: pulse 2s infinite;
        }
        
        .animate-bounce-slow {
            animation: bounce 2s infinite;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    @include('admin.partials.theme')
</head>
<body class="zoo-body zoo-admin min-h-screen">
    @include('partials.scenery')
    <!-- Navigation -->
    @include('admin.partials.nav')

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8 animate-fade-in-up">
            <h1 class="text-4xl font-bold gradient-text mb-2">
                <i class="fas fa-tasks text-indigo-600 mr-3 animate-bounce-slow"></i>Bulk Actions
            </h1>
            <p class="text-gray-600 text-lg">Kelola pesanan tiket secara massal dengan mudah</p>
        </div>

        <!-- Bulk Actions Panel -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 animate-slide-in-right card-hover">
            <div class="flex items-center mb-6">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mr-4 animate-float">
                    <i class="fas fa-magic text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Bulk Operations</h2>
                    <p class="text-gray-600">Pilih pesanan dan lakukan aksi massal</p>
                </div>
            </div>

            <!-- Selection Controls -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button id="selectAll" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced">
                    <i class="fas fa-check-double mr-2"></i>Select All
                </button>
                <button id="deselectAll" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced">
                    <i class="fas fa-times mr-2"></i>Deselect All
                </button>
                <button id="selectPending" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced">
                    <i class="fas fa-clock mr-2"></i>Select Pending
                </button>
                <button id="selectPaid" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced">
                    <i class="fas fa-check mr-2"></i>Select Paid
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button id="bulkConfirm" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i>Confirm Orders
                </button>
                <button id="bulkReject" class="bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-times-circle mr-2"></i>Reject Orders
                </button>
                <button id="bulkDelete" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-trash mr-2"></i>Delete Orders
                </button>
                <button id="bulkExport" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-download mr-2"></i>Export Orders
                </button>
            </div>

            <!-- Status Display -->
            <div id="statusDisplay" class="hidden">
                <div class="flex items-center p-4 rounded-lg bg-blue-50 border border-blue-200">
                    <div class="spinner-enhanced mr-3"></div>
                    <div class="flex-1">
                        <span class="text-blue-800 font-medium">Processing...</span>
                        <div class="progress-bar mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Results Display -->
            <div id="resultsDisplay" class="hidden">
                <div class="p-4 rounded-lg bg-green-50 border border-green-200">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                        <span id="resultMessage" class="text-green-800 font-medium"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in-up card-hover" style="animation-delay: 0.2s;">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">Orders List</h3>
                <p class="text-gray-600 text-sm">Select orders to perform bulk actions</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Orders will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Download Modal -->
    <div id="downloadModal" class="fixed inset-0 modal-backdrop hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full modal-enhanced">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-download text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Export Successful!</h3>
                    <p class="text-sm text-gray-500 mb-6">Your file has been generated successfully.</p>
                    <div class="flex space-x-3">
                        <button id="downloadFile" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                        <button id="closeModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let selectedOrders = [];
        let allOrders = [];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
            setupEventListeners();
        });

        function loadOrders() {
            // Simulate loading orders (replace with actual AJAX call)
            fetch('{{ route('admin.orders.data') }}')
                .then(response => response.json())
                .then(data => {
                    allOrders = data.orders || [];
                    renderOrdersTable();
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    // Fallback to sample data
                    allOrders = [
                        {id: 1, username: 'john_doe', nama: 'John Doe', kategori: 'dewasa', jumlah: 2, status: 'pending', tanggal: '2024-01-15'},
                        {id: 2, username: 'jane_smith', nama: 'Jane Smith', kategori: 'anak', jumlah: 1, status: 'paid', tanggal: '2024-01-14'},
                        {id: 3, username: 'bob_wilson', nama: 'Bob Wilson', kategori: 'keluarga', jumlah: 1, status: 'pending', tanggal: '2024-01-13'}
                    ];
                    renderOrdersTable();
                });
        }

        function renderOrdersTable() {
            const tbody = document.getElementById('ordersTableBody');
            tbody.innerHTML = '';

            allOrders.forEach((order, index) => {
                const row = document.createElement('tr');
                row.className = `hover:bg-gray-50 transition-colors duration-200 table-row-animate`;
                row.style.animationDelay = `${index * 0.1}s`;
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" class="order-checkbox enhanced-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="${order.id}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${order.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${order.nama}</div>
                        <div class="text-sm text-gray-500">@${order.username}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${order.kategori} - ${order.jumlah} tiket</div>
                        <div class="text-sm text-gray-500">${getCategoryPrice(order.kategori)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-badge ${getStatusClass(order.status)}">
                            ${order.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.tanggal}</td>
                `;
                tbody.appendChild(row);
            });

            // Add event listeners to checkboxes
            document.querySelectorAll('.order-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedOrders);
            });
        }

        function getCategoryPrice(kategori) {
            const prices = {
                'dewasa': 'Rp {{ number_format(\App\Support\Settings::ticketPrice('dewasa'), 0, ',', '.') }}',
                'anak': 'Rp {{ number_format(\App\Support\Settings::ticketPrice('anak'), 0, ',', '.') }}',
                'keluarga': 'Rp {{ number_format(\App\Support\Settings::ticketPrice('keluarga'), 0, ',', '.') }}'
            };
            return prices[kategori] || 'Rp 0';
        }

        function getStatusClass(status) {
            const classes = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'paid': 'bg-green-100 text-green-800',
                'failed': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        function setupEventListeners() {
            // Select all checkbox
            document.getElementById('selectAllCheckbox').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.order-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedOrders();
            });

            // Selection buttons
            document.getElementById('selectAll').addEventListener('click', selectAll);
            document.getElementById('deselectAll').addEventListener('click', deselectAll);
            document.getElementById('selectPending').addEventListener('click', () => selectByStatus('pending'));
            document.getElementById('selectPaid').addEventListener('click', () => selectByStatus('paid'));

            // Action buttons
            document.getElementById('bulkConfirm').addEventListener('click', () => performBulkAction('bulk_confirm'));
            document.getElementById('bulkReject').addEventListener('click', () => performBulkAction('bulk_reject'));
            document.getElementById('bulkDelete').addEventListener('click', () => performBulkAction('bulk_delete'));
            document.getElementById('bulkExport').addEventListener('click', () => performBulkAction('bulk_export'));

            // Modal buttons
            document.getElementById('downloadFile').addEventListener('click', downloadFile);
            document.getElementById('closeModal').addEventListener('click', closeModal);
        }

        function updateSelectedOrders() {
            selectedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked'))
                .map(checkbox => parseInt(checkbox.value));
            
            updateActionButtons();
        }

        function updateActionButtons() {
            const buttons = ['bulkConfirm', 'bulkReject', 'bulkDelete', 'bulkExport'];
            const hasSelection = selectedOrders.length > 0;
            
            buttons.forEach(buttonId => {
                const button = document.getElementById(buttonId);
                button.disabled = !hasSelection;
                if (hasSelection) {
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    button.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });
        }

        function selectAll() {
            document.querySelectorAll('.order-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAllCheckbox').checked = true;
            updateSelectedOrders();
        }

        function deselectAll() {
            document.querySelectorAll('.order-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
            updateSelectedOrders();
        }

        function selectByStatus(status) {
            document.querySelectorAll('.order-checkbox').forEach((checkbox, index) => {
                if (allOrders[index] && allOrders[index].status === status) {
                    checkbox.checked = true;
                }
            });
            updateSelectedOrders();
        }

        function performBulkAction(action) {
            if (selectedOrders.length === 0) {
                showNotification('Please select at least one order', 'warning');
                return;
            }

            // Show confirmation for destructive actions
            if (action === 'bulk_delete') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: `This will permanently delete ${selectedOrders.length} order(s). This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeBulkAction(action);
                    }
                });
            } else {
                executeBulkAction(action);
            }
        }

        function executeBulkAction(action) {
            showStatus('Processing...');
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('order_ids', selectedOrders.join(','));

            fetch('{{ route('admin.orders.bulk') }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideStatus();
                if (data.success) {
                    showNotification(data.message, 'success');
                    if (action === 'bulk_export' && data.download_url) {
                        showDownloadModal(data.download_url);
                    } else {
                        // Refresh the orders list
                        loadOrders();
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                hideStatus();
                console.error('Error:', error);
                showNotification('An error occurred while processing the request', 'error');
            });
        }

        function showStatus(message) {
            document.getElementById('statusDisplay').classList.remove('hidden');
        }

        function hideStatus() {
            document.getElementById('statusDisplay').classList.add('hidden');
        }

        function showNotification(message, type) {
            const icon = type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'error';
            const color = type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#ef4444';
            
            Swal.fire({
                title: type === 'success' ? 'Success!' : type === 'warning' ? 'Warning!' : 'Error!',
                text: message,
                icon: icon,
                confirmButtonColor: color,
                timer: type === 'success' ? 3000 : undefined,
                timerProgressBar: type === 'success'
            });
        }

        function showDownloadModal(filename) {
            document.getElementById('downloadModal').classList.remove('hidden');
            // Store filename for download
            document.getElementById('downloadFile').dataset.filename = filename;
        }

        function closeModal() {
            document.getElementById('downloadModal').classList.add('hidden');
        }

        function downloadFile() {
            const filename = document.getElementById('downloadFile').dataset.filename;
            if (filename) {
                window.open(filename, '_blank');
                closeModal();
            }
        }

        // Close modal when clicking outside
        document.getElementById('downloadModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
 