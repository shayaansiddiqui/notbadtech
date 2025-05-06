<?php
require_once 'src/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$conn = getDbConnection();

$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$zip = trim($_POST['zip'] ?? '');
$country = trim($_POST['country'] ?? '');
$longitude = isset($_POST['longitude']) && is_numeric($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$latitude = isset($_POST['latitude']) && is_numeric($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$operating_system = trim($_POST['operating_system'] ?? '');
$browser = trim($_POST['browser'] ?? '');
$timezone = trim($_POST['timezone'] ?? 'Unknown');
$original_city = trim($_POST['original_city'] ?? '');
$original_state = trim($_POST['original_state'] ?? '');
$original_zip = trim($_POST['original_zip'] ?? '');

// Get IP address
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Determine geolocation status
$geolocation = 'NO';
if ($latitude !== null && $longitude !== null) {
    // Geolocation was used
    if ($city === $original_city && $state === $original_state && $zip === $original_zip) {
        $geolocation = 'YES'; // All fields match API data
    } elseif ($city !== $original_city || $state !== $original_state || $zip !== $original_zip) {
        $geolocation = 'MIXED'; // At least one field was modified
    }
}

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Required fields are missing']);
    $conn->close();
    exit;
}

// Check for duplicate email
$stmt = $conn->prepare("SELECT id FROM submissions WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'This email is already registered']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert submission
$stmt = $conn->prepare("
    INSERT INTO submissions (
        first_name, last_name, email, phone, city, state, zip, country,
        longitude, latitude, ip_address, operating_system, browser,
        timezone, geolocation, verified, verifiedBy, verificationMethod
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NULL, NULL)
");
$stmt->bind_param(
    "ssssssssddsssss",
    $firstName,
    $lastName,
    $email,
    $phone,
    $city,
    $state,
    $zip,
    $country,
    $longitude,
    $latitude,
    $ip_address,
    $operating_system,
    $browser,
    $timezone,
    $geolocation
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save submission']);
}

$stmt->close();
$conn->close();
?>