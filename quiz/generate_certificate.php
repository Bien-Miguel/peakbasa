<?php
session_start();
require_once '../vendor/autoload.php'; // DomPDF autoload
require_once '../conn.php'; // Database connection

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header("Location: ../Verification/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// Get user's email and total stars from database
$stmt = $conn->prepare("SELECT email, total_score FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

$email = $user_data['email'] ?? '';
$total_stars = $user_data['total_score'] ?? 0;
$date = date('F d, Y');

// Check if action is download or email
$action = $_GET['action'] ?? 'download';

// Define paths for images (Relative to this PHP file)
// Ensure these files exist in your ../ui/ folder!
$starIcon = 'https://peakbasa.site/PeakBasa/ui/star.png';
$trophyIcon = 'https://peakbasa.site/PeakBasa/ui/trophy.png';
$mountainIcon = 'https://peakbasa.site/PeakBasa/ui/mountain.png'; // Or your specific logo file

// HTML template for the certificate
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap");
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        @page { margin: 0; padding: 0; }
        
        body {
            font-family: "Poppins", Arial, sans-serif;
            margin: 0; padding: 0;
        }
        
        .certificate {
            width: calc(100% - 84px);
            max-height: 595px;
            border: 12px solid #ec5757;
            border-radius: 15px;
            padding: 10px 30px;
            text-align: center;
            background: linear-gradient(135deg, #fdf0f0 0%, #ffffff 100%);
            position: relative;
            margin: 0 auto;
        }
        
        .certificate-header { margin-bottom: 12px; }
        
        /* Updated Logo Style for Image */
        .logo img {
            width: 60px;
            height: auto;
            margin-bottom: 5px;
        }
        
        .title {
            font-size: 36px;
            color: #ec5757;
            font-weight: 800;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .subtitle {
            font-size: 15px;
            color: #7c4646;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .recipient { margin: 15px 0; }
        
        .presented-to {
            font-size: 13px;
            color: #7c4646;
            font-style: italic;
            margin-bottom: 6px;
        }
        
        .recipient-name {
            font-size: 32px;
            color: #ec5757;
            font-weight: 800;
            margin: 8px 0;
            border-bottom: 3px solid #fad1d1;
            padding-bottom: 6px;
            display: inline-block;
            min-width: 320px;
        }
        
        .achievement {
            font-size: 12px;
            color: #7c4646;
            line-height: 1.5;
            margin: 15px 40px;
            font-weight: 500;
        }
        
        .stats { margin: 15px 0; }
        .stats table { margin: 0 auto; border-collapse: collapse; }
        
        .stat-item {
            background: #fdecec;
            padding: 10px 20px;
            border-radius: 10px;
            border: 2px solid #fad1d1;
            display: inline-block;
            margin: 0 12px;
        }
        
        .stat-value {
            font-size: 26px;
            color: #ec5757;
            font-weight: 800;
            display: block;
        }
        
        .stat-label {
            font-size: 11px;
            color: #7c4646;
            font-weight: 600;
            display: block;
            margin-top: 2px;
        }
        
        .footer { margin-top: 20px; }
        .footer table { width: 100%; border-collapse: collapse; }
        
        .signature-block { text-align: center; padding: 0 20px; }
        .signature-line {
            width: 160px;
            border-top: 2px solid #7c4646;
            margin: 12px auto 4px;
        }
        .signature-name { font-size: 12px; color: #7c4646; font-weight: 700; }
        .signature-title { font-size: 10px; color: #7c4646; font-weight: 500; }
        
        .date-block {
            font-size: 12px;
            color: #7c4646;
            text-align: center;
            padding: 0 20px;
        }
        
        /* Updated Decorative Corner Styles for Images */
        .decorative-corner {
            position: absolute;
            width: 35px; /* Adjust size of icons here */
            height: 35px;
            opacity: 0.4; /* Opacity for subtle look */
        }
        
        .corner-tl { top: 20px; left: 20px; }
        .corner-tr { top: 20px; right: 20px; }
        .corner-bl { bottom: 20px; left: 20px; }
        .corner-br { bottom: 20px; right: 20px; }
    </style>
</head>
<body>
    <div class="certificate">
        <img src="' . $starIcon . '" class="decorative-corner corner-tl">
        <img src="' . $trophyIcon . '" class="decorative-corner corner-tr">
        <img src="' . $mountainIcon . '" class="decorative-corner corner-bl">
        <img src="' . $starIcon . '" class="decorative-corner corner-br">
        
        <div class="certificate-header">
            <div class="logo">
                <img src="' . $mountainIcon . '" alt="Logo">
            </div>
            <div class="title">PeakBasa</div>
            <div class="subtitle">Certificate of Completion</div>
        </div>
        
        <div class="recipient">
            <div class="presented-to">This certificate is proudly presented to</div>
            <div class="recipient-name">' . $username . '</div>
        </div>
        
        <div class="achievement">
            For successfully completing all levels of the PeakBasa Filipino Language Learning Journey
            and demonstrating exceptional dedication to mastering the Filipino language.
        </div>
        
        <div class="stats">
            <table>
                <tr>
                    <td>
                        <div class="stat-item">
                            <span class="stat-value">' . $total_stars . '</span>
                            <span class="stat-label">Total Stars Earned</span>
                        </div>
                    </td>
                    <td>
                        <div class="stat-item">
                            <span class="stat-value">4</span>
                            <span class="stat-label">Levels Completed</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="footer">
            <table>
                <tr>
                    <td width="50%">
                        <div class="signature-block">
                            <div class="signature-line"></div>
                            <div class="signature-name">PeakBasa Team</div>
                            <div class="signature-title">Program Director</div>
                        </div>
                    </td>
                    <td width="50%">
                        <div class="date-block">
                            <strong>Date Issued:</strong><br>
                            ' . $date . '
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
';

// Configure DomPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // CRITICAL for loading local images
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Get canvas to set page size
$canvas = $dompdf->getCanvas();
$canvas->page_text(0, 0, "", null, 0, array(0, 0, 0));

if ($action === 'email' && !empty($email)) {
    // Save PDF to temporary file
    $pdf_output = $dompdf->output();
    $temp_file = sys_get_temp_dir() . '/peakbasa_certificate_' . $user_id . '.pdf';
    file_put_contents($temp_file, $pdf_output);
    
    // Email configuration
    $to = $email;
    $subject = 'Congratulations! Your PeakBasa Certificate of Completion';
    
    // NOTE: Emojis in Email Body (HTML) usually work fine, so I left them as text.
    // If you want images in the email body too, let me know.
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #fcdcdcff 0%, #fdf0f0 100%); padding: 30px; text-align: center; border-radius: 10px; }
            .header h1 { color: #ec5757; margin: 0; }
            .content { padding: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🏔️ Congratulations, ' . $username . '! 🏆</h1>
            </div>
            <div class="content">
                <p>Dear ' . $username . ',</p>
                <p>We are thrilled to inform you that you have successfully completed all levels of the PeakBasa Filipino Language Learning Journey!</p>
                <p><strong>Your Achievement:</strong></p>
                <ul>
                    <li>Total Stars Earned: <strong>' . $total_stars . ' ⭐</strong></li>
                    <li>Levels Completed: <strong>4/4</strong></li>
                    <li>Certificate Date: <strong>' . $date . '</strong></li>
                </ul>
                <p>Attached to this email is your official Certificate of Completion.</p>
                <p><strong>Mabuhay!</strong> You\'ve reached the peak! 🎉</p>
                <p>Best regards,<br>The PeakBasa Team</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' PeakBasa. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: PeakBasa <noreply@peakbasa.com>" . "\r\n";
    
    // Boundary for attachment
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
    
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"" . "\r\n";
    
    // Multipart message
    $message_body = "--{$mime_boundary}\r\n";
    $message_body .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
    $message_body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message_body .= $message . "\r\n\r\n";
    
    // Attach PDF
    $file_content = chunk_split(base64_encode(file_get_contents($temp_file)));
    $message_body .= "--{$mime_boundary}\r\n";
    $message_body .= "Content-Type: application/pdf; name=\"PeakBasa_Certificate.pdf\"\r\n";
    $message_body .= "Content-Transfer-Encoding: base64\r\n";
    $message_body .= "Content-Disposition: attachment; filename=\"PeakBasa_Certificate.pdf\"\r\n\r\n";
    $message_body .= $file_content . "\r\n\r\n";
    $message_body .= "--{$mime_boundary}--";
    
    // Send email
    if (mail($to, $subject, $message_body, $headers)) {
        unlink($temp_file); // Delete temporary file
        $_SESSION['certificate_message'] = "Certificate sent successfully to {$email}!";
    } else {
        $_SESSION['certificate_message'] = "Failed to send email. Please try downloading instead.";
    }
    
    header("Location: game_complete.php");
    exit;
    
} else {
    // Download PDF
    $dompdf->stream("PeakBasa_Certificate_{$username}.pdf", array("Attachment" => 1));
    exit;
}
?>