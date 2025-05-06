<?php
require_once 'auth.php';
requireLogin();
require_once '../src/db.php';

// Enable error logging (disable display for production)
ini_set('display_errors', 1); // Temporary for debugging; set to 0 after
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Initialize arrays for chart data
$os_labels = [];
$os_counts = [];
$country_labels = [];
$country_counts = [];
$geo_labels = [];
$geo_counts = [];
$browser_labels = [];
$browser_counts = [];
$time_labels = ['Morning (6-12)', 'Afternoon (12-18)', 'Evening (18-0)', 'Night (0-6)'];
$time_counts = [0, 0, 0, 0];
$yt_country_labels = [];
$yt_country_counts = [];
$yt_clicks_labels = [];
$yt_clicks_counts = [];
$yt_os_labels = [];
$yt_os_counts = [];
$yt_browser_labels = [];
$yt_browser_counts = [];
$yt_time_counts = [0, 0, 0, 0];
$error_message = '';

try {
    $conn = getDbConnection();

    // Submissions Metrics
    if (!$conn->query("SELECT 1 FROM submissions LIMIT 1")) {
        $error_message .= "Submissions table not found or empty: " . $conn->error . ". ";
        error_log("Submissions table error: " . $conn->error);
    } else {
        $os_data = $conn->query("SELECT operating_system, COUNT(*) as count FROM submissions GROUP BY operating_system");
        if ($os_data) {
            while ($row = $os_data->fetch_assoc()) {
                $os_labels[] = $row['operating_system'] ?: 'Unknown';
                $os_counts[] = $row['count'];
            }
        } else {
            $error_message .= "OS query failed: " . $conn->error . ". ";
            error_log("OS query error: " . $conn->error);
        }

        $country_data = $conn->query("SELECT country, COUNT(*) as count FROM submissions GROUP BY country");
        if ($country_data) {
            while ($row = $country_data->fetch_assoc()) {
                $country_labels[] = $row['country'] ?: 'Unknown';
                $country_counts[] = $row['count'];
            }
        } else {
            $error_message .= "Country query failed: " . $conn->error . ". ";
            error_log("Country query error: " . $conn->error);
        }

        $geo_data = $conn->query("SELECT geolocation, COUNT(*) as count FROM submissions GROUP BY geolocation");
        if ($geo_data) {
            while ($row = $geo_data->fetch_assoc()) {
                $geo_labels[] = $row['geolocation'] ?: 'Unknown';
                $geo_counts[] = $row['count'];
            }
        } else {
            $error_message .= "Geolocation query failed: " . $conn->error . ". ";
            error_log("Geolocation query error: " . $conn->error);
        }

        $browser_data = $conn->query("SELECT browser, COUNT(*) as count FROM submissions GROUP BY browser");
        if ($browser_data) {
            while ($row = $browser_data->fetch_assoc()) {
                $browser_labels[] = $row['browser'] ?: 'Unknown';
                $browser_counts[] = $row['count'];
            }
        } else {
            $error_message .= "Browser query failed: " . $conn->error . ". ";
            error_log("Browser query error: " . $conn->error);
        }

        $time_data = $conn->query("SELECT HOUR(created_at) as hour, COUNT(*) as count FROM submissions GROUP BY HOUR(created_at)");
        if ($time_data) {
            while ($row = $time_data->fetch_assoc()) {
                $hour = $row['hour'];
                if ($hour >= 6 && $hour < 12) $time_counts[0] += $row['count'];
                elseif ($hour >= 12 && $hour < 18) $time_counts[1] += $row['count'];
                elseif ($hour >= 18) $time_counts[2] += $row['count'];
                else $time_counts[3] += $row['count'];
            }
        } else {
            $error_message .= "Submissions time query failed: " . $conn->error . ". ";
            error_log("Submissions time query error: " . $conn->error);
        }
    }

    // YouTube Clicks Metrics
    if (!$conn->query("SELECT 1 FROM youtube_clicks LIMIT 1")) {
        $error_message .= "YouTube clicks table not found or empty: " . $conn->error . ". ";
        error_log("YouTube clicks table error: " . $conn->error);
    } else {
        $yt_country_data = $conn->query("SELECT country, COUNT(*) as count FROM youtube_clicks GROUP BY country");
        if ($yt_country_data) {
            while ($row = $yt_country_data->fetch_assoc()) {
                $yt_country_labels[] = $row['country'] ?: 'Unknown';
                $yt_country_counts[] = $row['count'];
            }
        } else {
            $error_message .= "YouTube country query failed: " . $conn->error . ". ";
            error_log("YouTube country query error: " . $conn->error);
        }

        $yt_clicks_data = $conn->query("SELECT clicks, COUNT(*) as count FROM youtube_clicks GROUP BY clicks");
        if ($yt_clicks_data) {
            while ($row = $yt_clicks_data->fetch_assoc()) {
                $yt_clicks_labels[] = $row['clicks'] . ' Click' . ($row['clicks'] > 1 ? 's' : '');
                $yt_clicks_counts[] = $row['count'];
            }
        } else {
            $error_message .= "YouTube clicks query failed: " . $conn->error . ". ";
            error_log("YouTube clicks query error: " . $conn->error);
        }

        $yt_os_data = $conn->query("SELECT operating_system, COUNT(*) as count FROM youtube_clicks GROUP BY operating_system");
        if ($yt_os_data) {
            while ($row = $yt_os_data->fetch_assoc()) {
                $yt_os_labels[] = $row['operating_system'] ?: 'Unknown';
                $yt_os_counts[] = $row['count'];
            }
        } else {
            $error_message .= "YouTube OS query failed: " . $conn->error . ". ";
            error_log("YouTube OS query error: " . $conn->error);
        }

        $yt_browser_data = $conn->query("SELECT browser, COUNT(*) as count FROM youtube_clicks GROUP BY browser");
        if ($yt_browser_data) {
            while ($row = $yt_browser_data->fetch_assoc()) {
                $yt_browser_labels[] = $row['browser'] ?: 'Unknown';
                $yt_browser_counts[] = $row['count'];
            }
        } else {
            $error_message .= "YouTube browser query failed: " . $conn->error . ". ";
            error_log("YouTube browser query error: " . $conn->error);
        }

        $yt_time_data = $conn->query("SELECT HOUR(click_timestamp) as hour, COUNT(*) as count FROM youtube_clicks GROUP BY HOUR(click_timestamp)");
        if ($yt_time_data) {
            while ($row = $yt_time_data->fetch_assoc()) {
                $hour = $row['hour'];
                if ($hour >= 6 && $hour < 12) $yt_time_counts[0] += $row['count'];
                elseif ($hour >= 12 && $hour < 18) $yt_time_counts[1] += $row['count'];
                elseif ($hour >= 18) $yt_time_counts[2] += $row['count'];
                else $yt_time_counts[3] += $row['count'];
            }
        } else {
            $error_message .= "YouTube time query failed: " . $conn->error . ". ";
            error_log("YouTube time query error: " . $conn->error);
        }
    }

    // Fetch all submissions
    $submissions = $conn->query("SELECT * FROM submissions ORDER BY created_at DESC");
    if (!$submissions) {
        $error_message .= "Failed to fetch submissions: " . $conn->error . ". ";
        error_log("Submissions fetch error: " . $conn->error);
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    http_response_code(500);
    die("Internal Server Error: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - (not)badtech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#1e3c72]">Admin Dashboard</h1>
            <div>
                <a href="change_password.php" class="bg-[#2a5298] text-white px-4 py-2 rounded-lg hover:bg-[#1e3c72] mr-2">Change Password</a>
                <a href="settings.php" class="bg-[#2a5298] text-white px-4 py-2 rounded-lg hover:bg-[#1e3c72] mr-2">Settings</a>
                <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
            </div>
        </div>

        <?php if ($error_message): ?>
            <p class="text-red-600 text-sm mb-4"><?php echo htmlspecialchars($error_message); ?> Please ensure data exists in the database.</p>
        <?php endif; ?>

        <!-- Submissions Metrics -->
        <h2 class="text-2xl font-semibold text-[#1e3c72] mb-4">Submissions Metrics</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Operating System</h3>
                <canvas id="osChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Country</h3>
                <canvas id="countryChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Geolocation Enabled</h3>
                <canvas id="geoChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Browser Used</h3>
                <canvas id="browserChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Time of Day</h3>
                <canvas id="timeChart"></canvas>
            </div>
        </div>

        <!-- YouTube Clicks Metrics -->
        <h2 class="text-2xl font-semibold text-[#1e3c72] mb-4">YouTube Clicks Metrics</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Country</h3>
                <canvas id="ytCountryChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Clicks per Individual</h3>
                <canvas id="ytClicksChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Operating System</h3>
                <canvas id="ytOsChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Browser Used</h3>
                <canvas id="ytBrowserChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Time of Day</h3>
                <canvas id="ytTimeChart"></canvas>
            </div>
        </div>

        <!-- Signups List -->
        <h2 class="text-2xl font-semibold text-[#1e3c72] mb-4">Signups</h2>
        <div class="bg-white p-4 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-[#2a5298] text-white">
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Phone</th>
                        <th class="px-4 py-2">City</th>
                        <th class="px-4 py-2">State</th>
                        <th class="px-4 py-2">Zip</th>
                        <th class="px-4 py-2">Country</th>
                        <th class="px-4 py-2">Timezone</th>
                        <th class="px-4 py-2">Geolocation</th>
                        <th class="px-4 py-2">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($submissions && $submissions->num_rows > 0): ?>
                        <?php while ($row = $submissions->fetch_assoc()): ?>
                            <tr class="border-t">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['city'] ?: 'N/A'); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['state'] ?: 'N/A'); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['zip'] ?: 'N/A'); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['country'] ?: 'N/A'); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['timezone'] ?: 'N/A'); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['geolocation'] ?: 'N/A'); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="px-4 py-2 text-center">No signups found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Chart.js Pie Charts
        const chartOptions = {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            }
        };

        new Chart(document.getElementById('osChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($os_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($os_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('countryChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($country_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($country_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('geoChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($geo_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($geo_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('browserChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($browser_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($browser_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('timeChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($time_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($time_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('ytCountryChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($yt_country_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($yt_country_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('ytClicksChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($yt_clicks_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($yt_clicks_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('ytOsChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($yt_os_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($yt_os_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('ytBrowserChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($yt_browser_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($yt_browser_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('ytTimeChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($time_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($yt_time_counts); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: chartOptions
        });
    </script>
</body>
</html>