<?php
require_once 'src/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>(not)badtech - Launching Soon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/libphonenumber-js/bundle/libphonenumber-js.min.js" defer></script>
    <script src="js/form.js" defer></script>
</head>
<body class="bg-gradient-to-br from-[#1e3c72] to-[#2a5298] text-white min-h-screen flex items-center justify-center p-5">
    <div class="max-w-2xl w-full text-center bg-white/95 text-gray-800 rounded-3xl p-10 shadow-xl mx-auto my-10">
        <h1 class="text-4xl text-[#1e3c72] mb-5 font-bold">(not)badtech</h1>
        <p class="text-lg leading-relaxed mb-8 text-gray-600">
            We're reinventing technology for everyday people by listening to your feedback and refining products to be truly user-friendly. Our launch is coming soon – stay in the loop!
        </p>

        <!-- Signup Form -->
        <form id="signup-form" action="submit.php" method="POST" class="space-y-5">
            <div id="form-error" class="text-red-600 text-sm text-center hidden"></div>
            <div class="text-left">
                <label for="firstName" class="block text-sm font-bold text-gray-800 mb-1">First Name</label>
                <input type="text" id="firstName" name="firstName" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
            </div>
            <div class="text-left">
                <label for="lastName" class="block text-sm font-bold text-gray-800 mb-1">Last Name</label>
                <input type="text" id="lastName" name="lastName" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
            </div>
            <div class="text-left">
                <label for="email" class="block text-sm font-bold text-gray-800 mb-1">Email Address</label>
                <input type="email" id="email" name="email" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
            </div>
            <div class="text-left">
                <label for="phone" class="block text-sm font-bold text-gray-800 mb-1">Phone Number</label>
                <input type="tel" id="phone" name="phone" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
            </div>
            <div class="text-left">
                <label for="city" class="block text-sm font-bold text-gray-800 mb-1">City</label>
                <input type="text" id="city" name="city" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
            </div>
            <div id="state-container" class="text-left hidden">
                <label for="state" class="block text-sm font-bold text-gray-800 mb-1">State</label>
                <select id="state" name="state" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
                    <option value="" disabled selected>Select your state</option>
                    <?php
                    $usStates = [
                        "Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia",
                        "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland",
                        "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey",
                        "New Mexico", "New York", "North Carolina", "North Dakota", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina",
                        "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"
                    ];
                    foreach ($usStates as $state) {
                        echo "<option value=\"$state\">$state</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="text-left">
                <label for="zip" class="block text-sm font-bold text-gray-800 mb-1">Zip</label>
                <input type="text" id="zip" name="zip" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
            </div>
            <div class="text-left">
                <label for="country" class="block text-sm font-bold text-gray-800 mb-1">Country</label>
                <select id="country" name="country" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]">
                    <option value="" disabled selected>Select your country</option>
                    <?php
                    $countries = [
                        "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan",
                        "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia",
                        "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada",
                        "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia",
                        "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt",
                        "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon",
                        "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti",
                        "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan",
                        "Jordan", "Kazakhstan", "Kenya", "Kiribati", "North Korea", "South Korea", "Kuwait", "Kyrgyzstan", "Laos", "Latvia",
                        "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Malaysia",
                        "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco",
                        "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand",
                        "Nicaragua", "Niger", "Nigeria", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea",
                        "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis",
                        "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal",
                        "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa",
                        "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan",
                        "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda",
                        "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City",
                        "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
                    ];
                    foreach ($countries as $country) {
                        echo "<option value=\"$country\">$country</option>";
                    }
                    ?>
                </select>
            </div>
            <input type="hidden" id="longitude" name="longitude">
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="operating_system" name="operating_system">
            <input type="hidden" id="browser" name="browser">
            <input type="hidden" id="timezone" name="timezone">
            <button type="submit" class="w-full bg-[#2a5298] text-white p-4 rounded-lg font-semibold hover:bg-[#1e3c72] transition-colors">
                Sign Up
            </button>
        </form>

        <!-- Success Modal -->
        <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full transform transition-all scale-0 animate-scale-in">
                <h2 class="text-2xl font-bold text-[#1e3c72] mb-4 text-center">Thank you. We'll be in touch!</h2>
                <p class="text-gray-600 text-center mb-6">We're excited to keep you updated on our launch. Stay tuned!</p>
                <button id="close-modal" class="w-full bg-[#2a5298] text-white p-3 rounded-lg font-semibold hover:bg-[#1e3c72] transition-colors">Close</button>
            </div>
        </div>

        <!-- Geolocation Retry Button (Hidden by Default) -->
        <div id="geolocation-retry" class="text-center mt-4 hidden">
            <p class="text-red-600 text-sm mb-2">Geolocation permission denied. Please allow location access to auto-fill fields.</p>
            <button id="retry-geolocation" class="bg-[#2a5298] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#1e3c72] transition-colors">
                Retry Geolocation
            </button>
        </div>

        <!-- Footer with YouTube link -->
        <footer class="mt-10 text-gray-600 text-center">
            <p>Follow us on social media:</p>
            <a href="#" onclick="handleYouTubeClick(event)" class="inline-block mt-2">
                <img src="public/youtube.svg" alt="YouTube" width="32" height="32">
            </a>
        </footer>
    </div>
</body>
</html>