<?php
require_once 'src/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$conn = getDbConnection();

// Get IP addresses
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ipv4 = null;
$ipv6 = null;

// Parse IP address to determine IPv4 or IPv6
if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    $ipv4 = $ip_address;
} elseif (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
    $ipv6 = $ip_address;
}

// Get client data from POST
$time_zone = trim($_POST['time_zone'] ?? 'Unknown');
$latitude = isset($_POST['latitude']) && is_numeric($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) && is_numeric($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$country = trim($_POST['country'] ?? null);

// Detect OS, browser, and browser version from User-Agent
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$os = 'Unknown';
$browser = 'Unknown';
$browser_version = 'Unknown';

// Detect OS
if (preg_match('/Windows/i', $user_agent)) $os = 'Windows';
elseif (preg_match('/Mac OS/i', $user_agent)) $os = 'Mac OS';
elseif (preg_match('/Linux/i', $user_agent)) $os = 'Linux';
elseif (preg_match('/Android/i', $user_agent)) $os = 'Android';
elseif (preg_match('/iOS|iPhone|iPad/i', $user_agent)) $os = 'iOS';

// Detect browser and version
if (preg_match('/(Chrome|Firefox|Safari|Edge|Opera)\/([\d.]+)/i', $user_agent, $matches)) {
    $browser = $matches[1];
    $browser_version = $matches[2];
} elseif (preg_match('/Version\/([\d.]+).*Safari/i', $user_agent, $matches)) {
    $browser = 'Safari';
    $browser_version = $matches[1];
}

// Check for existing record by ipv4 or ipv6
$existing_id = null;
$existing_clicks = 0;

if ($ipv4 || $ipv6) {
    $stmt = $conn->prepare("SELECT id, clicks FROM youtube_clicks WHERE ipv4 = ? OR ipv6 = ? LIMIT 1");
    $stmt->bind_param("ss", $ipv4, $ipv6);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $existing_id = $row['id'];
        $existing_clicks = $row['clicks'];
    }
    $stmt->close();
}

// Prepare data for insert or update
if ($existing_id) {
    // Update existing record
    $new_clicks = $existing_clicks + 1;
    $stmt = $conn->prepare("
        UPDATE youtube_clicks
        SET clicks = ?,
            operating_system = ?,
            browser = ?,
            browser_version = ?,
            time_zone = ?,
            country = ?,
            latitude = ?,
            longitude = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->bind_param(
        "isssssddi",
        $new_clicks,
        $os,
        $browser,
        $browser_version,
        $time_zone,
        $country,
        $latitude,
        $longitude,
        $existing_id
    );
} else {
    // Insert new record
    $stmt = $conn->prepare("
        INSERT INTO youtube_clicks (
            ipv4, ipv6, operating_system, browser, browser_version,
            time_zone, country, latitude, longitude, clicks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->bind_param(
        "sssssssdd",
        $ipv4,
        $ipv6,
        $os,
        $browser,
        $browser_version,
        $time_zone,
        $country,
        $latitude,
        $longitude
    );
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to log click']);
}

$stmt->close();
$conn->close();
?>