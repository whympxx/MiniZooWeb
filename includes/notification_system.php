<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Handle notification requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send_order_confirmation':
            $order_id = (int)($_POST['order_id'] ?? 0);
            if ($order_id > 0) {
                sendOrderConfirmationNotification($order_id);
            }
            break;
        case 'send_order_rejection':
            $order_id = (int)($_POST['order_id'] ?? 0);
            if ($order_id > 0) {
                sendOrderRejectionNotification($order_id);
            }
            break;
        case 'test_notification':
            $email = $_POST['email'] ?? '';
            if (!empty($email)) {
                testNotification($email);
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit();
    }
}

function sendOrderConfirmationNotification($order_id) {
    global $pdo;
    
    try {
        // Get order details
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email as user_email, u.phone as user_phone
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Calculate price
        $price_per_ticket = 0;
        switch($order['kategori']) {
            case 'dewasa':
                $price_per_ticket = 50000;
                break;
            case 'anak':
                $price_per_ticket = 30000;
                break;
            case 'keluarga':
                $price_per_ticket = 120000;
                break;
        }
        $total_price = $price_per_ticket * $order['jumlah'];
        
        // Prepare email content
        $subject = "Konfirmasi Pembayaran Tiket - Order #" . $order_id;
        $message = generateConfirmationEmail($order, $total_price);
        
        // Send email (placeholder for now)
        $email_sent = sendEmail($order['email'], $subject, $message);
        
        // Log the notification
        logNotification('order_confirmation', $order_id, $order['email'], $email_sent);
        
        if ($email_sent) {
            echo json_encode(['success' => true, 'message' => 'Confirmation email sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send confirmation email']);
        }
        
    } catch (Exception $e) {
        error_log("Send confirmation notification error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error sending notification']);
    }
}

function sendOrderRejectionNotification($order_id) {
    global $pdo;
    
    try {
        // Get order details
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email as user_email, u.phone as user_phone
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Prepare email content
        $subject = "Pembayaran Tiket Ditolak - Order #" . $order_id;
        $message = generateRejectionEmail($order);
        
        // Send email (placeholder for now)
        $email_sent = sendEmail($order['email'], $subject, $message);
        
        // Log the notification
        logNotification('order_rejection', $order_id, $order['email'], $email_sent);
        
        if ($email_sent) {
            echo json_encode(['success' => true, 'message' => 'Rejection email sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send rejection email']);
        }
        
    } catch (Exception $e) {
        error_log("Send rejection notification error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error sending notification']);
    }
}

function testNotification($email) {
    $subject = "Test Notification - Zoo Management System";
    $message = "
    <html>
    <body>
        <h2>Test Notification</h2>
        <p>This is a test notification from the Zoo Management System.</p>
        <p>If you received this email, the notification system is working correctly.</p>
        <p>Time: " . date('Y-m-d H:i:s') . "</p>
    </body>
    </html>
    ";
    
    $email_sent = sendEmail($email, $subject, $message);
    
    if ($email_sent) {
        echo json_encode(['success' => true, 'message' => 'Test email sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send test email']);
    }
}

function generateConfirmationEmail($order, $total_price) {
    $category_labels = [
        'dewasa' => 'Dewasa',
        'anak' => 'Anak-anak',
        'keluarga' => 'Keluarga'
    ];
    
    $category = $category_labels[$order['kategori']] ?? $order['kategori'];
    $tanggal = date('d F Y', strtotime($order['tanggal']));
    
    return "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>🎫 Konfirmasi Pembayaran Tiket</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px;'>Zoo Management System</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2 style='color: #28a745; margin-top: 0;'>✅ Pembayaran Berhasil Dikonfirmasi</h2>
                
                <p>Halo <strong>{$order['nama']}</strong>,</p>
                
                <p>Pembayaran untuk pesanan tiket Anda telah berhasil dikonfirmasi. Berikut adalah detail pesanan Anda:</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                    <h3 style='margin-top: 0; color: #333;'>📋 Detail Pesanan</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Order ID:</td>
                            <td style='padding: 8px 0;'>#{$order['id']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Kategori Tiket:</td>
                            <td style='padding: 8px 0;'>{$category}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Jumlah Tiket:</td>
                            <td style='padding: 8px 0;'>{$order['jumlah']} tiket</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Tanggal Kunjungan:</td>
                            <td style='padding: 8px 0;'>{$tanggal}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Total Pembayaran:</td>
                            <td style='padding: 8px 0; color: #28a745; font-weight: bold;'>Rp " . number_format($total_price, 0, ',', '.') . "</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                    <h4 style='margin: 0 0 10px 0; color: #28a745;'>📅 Informasi Kunjungan</h4>
                    <p style='margin: 0;'>Silakan datang ke kebun binatang pada tanggal yang telah dipilih. Jangan lupa membawa bukti pembayaran ini.</p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <p style='color: #666; font-size: 14px;'>Terima kasih telah memilih kebun binatang kami!</p>
                    <p style='color: #666; font-size: 14px;'>Jika ada pertanyaan, silakan hubungi kami.</p>
                </div>
            </div>
            
            <div style='text-align: center; margin-top: 20px; color: #666; font-size: 12px;'>
                <p>Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function generateRejectionEmail($order) {
    $category_labels = [
        'dewasa' => 'Dewasa',
        'anak' => 'Anak-anak',
        'keluarga' => 'Keluarga'
    ];
    
    $category = $category_labels[$order['kategori']] ?? $order['kategori'];
    $tanggal = date('d F Y', strtotime($order['tanggal']));
    
    return "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>❌ Pembayaran Tiket Ditolak</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px;'>Zoo Management System</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2 style='color: #dc3545; margin-top: 0;'>⚠️ Pembayaran Tidak Dapat Diproses</h2>
                
                <p>Halo <strong>{$order['nama']}</strong>,</p>
                
                <p>Mohon maaf, pembayaran untuk pesanan tiket Anda tidak dapat diproses. Berikut adalah detail pesanan yang ditolak:</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                    <h3 style='margin-top: 0; color: #333;'>📋 Detail Pesanan</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Order ID:</td>
                            <td style='padding: 8px 0;'>#{$order['id']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Kategori Tiket:</td>
                            <td style='padding: 8px 0;'>{$category}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Jumlah Tiket:</td>
                            <td style='padding: 8px 0;'>{$order['jumlah']} tiket</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Tanggal Kunjungan:</td>
                            <td style='padding: 8px 0;'>{$tanggal}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                    <h4 style='margin: 0 0 10px 0; color: #dc3545;'>🔍 Kemungkinan Penyebab</h4>
                    <ul style='margin: 0; padding-left: 20px;'>
                        <li>Pembayaran tidak lengkap atau tidak valid</li>
                        <li>Dokumen pembayaran tidak jelas</li>
                        <li>Informasi pembayaran tidak sesuai</li>
                        <li>Kendala teknis dalam sistem</li>
                    </ul>
                </div>
                
                <div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>
                    <h4 style='margin: 0 0 10px 0; color: #17a2b8;'>💡 Langkah Selanjutnya</h4>
                    <p style='margin: 0;'>Silakan hubungi kami untuk informasi lebih lanjut atau melakukan pemesanan ulang dengan pembayaran yang valid.</p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <p style='color: #666; font-size: 14px;'>Terima kasih atas pengertian Anda.</p>
                </div>
            </div>
            
            <div style='text-align: center; margin-top: 20px; color: #666; font-size: 12px;'>
                <p>Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function sendEmail($to, $subject, $message) {
    // This is a placeholder function
    // In a real application, you would use PHPMailer, SwiftMailer, or similar library
    // For now, we'll just log the email attempt
    
    $email_data = [
        'to' => $to,
        'subject' => $subject,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $log_file = 'logs/email_logs.txt';
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, json_encode($email_data) . "\n", FILE_APPEND | LOCK_EX);
    
    // Simulate email sending (return true for success)
    return true;
}

function logNotification($type, $order_id, $recipient, $success) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'order_id' => $order_id,
        'recipient' => $recipient,
        'success' => $success,
        'admin_id' => $_SESSION['user_id'] ?? 'unknown'
    ];
    
    $log_file = 'logs/notifications.log';
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Handle GET requests for notification logs
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['logs'])) {
    try {
        $log_file = 'logs/notifications.log';
        $logs = [];
        
        if (file_exists($log_file)) {
            $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $log_entry = json_decode($line, true);
                if ($log_entry) {
                    $logs[] = $log_entry;
                }
            }
        }
        
        // Return last 50 logs
        $logs = array_slice(array_reverse($logs), 0, 50);
        
        echo json_encode([
            'success' => true,
            'logs' => $logs,
            'count' => count($logs)
        ]);
        
    } catch (Exception $e) {
        error_log("Get notification logs error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error retrieving logs']);
    }
}
?> 