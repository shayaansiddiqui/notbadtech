document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signup-form');
    const errorDiv = document.getElementById('form-error');
    const successModal = document.getElementById('success-modal');
    const closeModalButton = document.getElementById('close-modal');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const countrySelect = document.getElementById('country');
    const stateContainer = document.getElementById('state-container');
    const stateSelect = document.getElementById('state');
    const cityInput = document.getElementById('city');
    const zipInput = document.getElementById('zip');
    const longitudeInput = document.getElementById('longitude');
    const latitudeInput = document.getElementById('latitude');
    const osInput = document.getElementById('operating_system');
    const browserInput = document.getElementById('browser');
    const timezoneInput = document.getElementById('timezone');
    const geolocationRetryDiv = document.getElementById('geolocation-retry');
    const retryGeolocationButton = document.getElementById('retry-geolocation');

    let geolocationAvailable = true;
    let geolocationData = {
        latitude: null,
        longitude: null,
        country: null,
        city: null,
        state: null,
        zip: null
    };

    // Country code mapping for libphonenumber-js
    const countryCodeMap = {
        'United States': 'US',
        'United Kingdom': 'GB',
        'Canada': 'CA',
        'Australia': 'AU',
        'Germany': 'DE',
        'France': 'FR',
        'India': 'IN',
        // Add more as needed
    };

    // List of US states for validation
    const usStates = [
        "Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia",
        "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland",
        "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey",
        "New Mexico", "New York", "North Carolina", "North Dakota", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina",
        "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"
    ];

    // Initialize Choices.js
    const countryChoices = new Choices(countrySelect, {
        searchEnabled: true,
        searchChoices: true,
        itemSelectText: '',
        shouldSort: true,
    });

    const stateChoices = new Choices(stateSelect, {
        searchEnabled: true,
        searchChoices: true,
        itemSelectText: '',
        shouldSort: true,
    });

    // Validate email
    const validateEmail = (email) => {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    };

    // Validate phone
    const validatePhone = (phone, country) => {
        if (!phone || !country) return false;
        const countryCode = countryCodeMap[country] || 'US';
        try {
            const phoneNumber = libphonenumber.parsePhoneNumberFromString(phone, countryCode);
            return phoneNumber ? phoneNumber.isValid() : false;
        } catch (error) {
            console.error('Phone validation error:', error);
            return false;
        }
    };

    // Format phone
    const formatPhone = (phone, country) => {
        if (!phone || !country) return phone;
        const countryCode = countryCodeMap[country] || 'US';
        try {
            const phoneNumber = libphonenumber.parsePhoneNumberFromString(phone, countryCode);
            return phoneNumber ? phoneNumber.formatInternational() : phone;
        } catch (error) {
            console.error('Phone formatting error:', error);
            return phone;
        }
    };

    // Detect OS, browser, and browser version
    const getUserAgentInfo = () => {
        const ua = navigator.userAgent;
        let os = 'Unknown';
        let browser = 'Unknown';
        let browserVersion = 'Unknown';

        // Detect OS
        if (ua.includes('Windows')) os = 'Windows';
        else if (ua.includes('Mac OS')) os = 'Mac OS';
        else if (ua.includes('Linux')) os = 'Linux';
        else if (ua.includes('Android')) os = 'Android';
        else if (ua.includes('iOS')) os = 'iOS';

        // Detect browser and version
        const browserMatch = ua.match(/(Chrome|Firefox|Safari|Edge|Opera)\/([\d.]+)/);
        if (browserMatch) {
            browser = browserMatch[1];
            browserVersion = browserMatch[2];
        } else if (ua.includes('Safari') && !ua.includes('Chrome')) {
            browser = 'Safari';
            const versionMatch = ua.match(/Version\/([\d.]+)/);
            browserVersion = versionMatch ? versionMatch[1] : 'Unknown';
        }

        return { os, browser, browserVersion };
    };

    // Set OS and browser for signup form
    const { os, browser, browserVersion } = getUserAgentInfo();
    osInput.value = os;
    browserInput.value = `${browser}/${browserVersion}`;

    // Get time zone
    const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    timezoneInput.value = timeZone;

    // Handle country change
    countrySelect.addEventListener('change', () => {
        const country = countrySelect.value;
        if (country === 'United States' || !geolocationAvailable) {
            stateContainer.classList.remove('hidden');
            stateSelect.required = !geolocationAvailable && country === 'United States';
        } else {
            stateContainer.classList.add('hidden');
            stateSelect.value = '';
            cityInput.value = '';
            zipInput.value = '';
        }
        phoneInput.value = formatPhone(phoneInput.value, country);
    });

    // Handle state change
    stateSelect.addEventListener('change', () => {
        cityInput.value = '';
        zipInput.value = '';
    });

    // Geolocation function
    const requestGeolocation = () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const { latitude, longitude } = position.coords;
                    geolocationData.latitude = latitude;
                    geolocationData.longitude = longitude;

                    latitudeInput.value = latitude;
                    longitudeInput.value = longitude;

                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`
                        );
                        if (!response.ok) throw new Error('Nominatim API error');
                        const data = await response.json();
                        const address = data.address || {};

                        // Store original geolocation data
                        geolocationData.city = address.city || address.town || address.village || '';
                        geolocationData.state = address.country === 'United States' ? (address.state || '') : '';
                        geolocationData.zip = address.postcode || '';
                        geolocationData.country = address.country || null;

                        // Populate form fields
                        cityInput.value = geolocationData.city;
                        zipInput.value = geolocationData.zip;
                        countrySelect.value = address.country || '';
                        countryChoices.setChoiceByValue(address.country || '');

                        if (address.country === 'United States') {
                            stateContainer.classList.remove('hidden');
                            if (usStates.includes(address.state)) {
                                stateSelect.value = address.state;
                                stateChoices.setChoiceByValue(address.state);
                            }
                        }

                        // Hide retry button on success
                        geolocationRetryDiv.classList.add('hidden');
                        geolocationAvailable = true;

                        // Create hidden inputs for original geolocation data
                        const originalCityInput = document.createElement('input');
                        originalCityInput.type = 'hidden';
                        originalCityInput.name = 'original_city';
                        originalCityInput.value = geolocationData.city;
                        signupForm.appendChild(originalCityInput);

                        const originalStateInput = document.createElement('input');
                        originalStateInput.type = 'hidden';
                        originalStateInput.name = 'original_state';
                        originalStateInput.value = geolocationData.state;
                        signupForm.appendChild(originalStateInput);

                        const originalZipInput = document.createElement('input');
                        originalZipInput.type = 'hidden';
                        originalZipInput.name = 'original_zip';
                        originalZipInput.value = geolocationData.zip;
                        signupForm.appendChild(originalZipInput);
                    } catch (error) {
                        console.error('Geolocation data error:', error);
                        errorDiv.textContent = 'Failed to fetch location data. Please select your country (e.g., United States to include state) and enter details manually.';
                        errorDiv.classList.remove('hidden');
                        geolocationAvailable = false;
                        cityInput.required = true;
                        zipInput.required = true;
                        countrySelect.required = true;
                        if (countrySelect.value === 'United States') stateSelect.required = true;
                    }
                },
                (error) => {
                    let message = 'Unable to access location. Please select your country (e.g., United States to include state) and enter details manually.';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'Location access denied. Please allow location access or select your country (e.g., United States to include state) and enter details manually.';
                            geolocationRetryDiv.classList.remove('hidden');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'Location information unavailable. Please select your country (e.g., United States to include state) and enter details manually.';
                            break;
                        case error.TIMEOUT:
                            message = 'Location request timed out. Please try again or select your country (e.g., United States to include state) and enter details manually.';
                            break;
                    }
                    console.error(`Geolocation error: Code ${error.code}, Message: ${error.message}`);
                    errorDiv.textContent = message;
                    errorDiv.classList.remove('hidden');
                    geolocationAvailable = false;
                    cityInput.required = true;
                    zipInput.required = true;
                    countrySelect.required = true;
                    stateContainer.classList.remove('hidden');
                },
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
            );
        } else {
            errorDiv.textContent = 'Geolocation not supported. Please select your country (e.g., United States to include state) and enter details manually.';
            errorDiv.classList.remove('hidden');
            geolocationAvailable = false;
            cityInput.required = true;
            zipInput.required = true;
            countrySelect.required = true;
            stateContainer.classList.remove('hidden');
        }
    };

    // Initial geolocation request
    requestGeolocation();

    // Retry geolocation on button click
    retryGeolocationButton.addEventListener('click', () => {
        requestGeolocation();
    });

    // Close modal
    closeModalButton.addEventListener('click', () => {
        successModal.classList.add('hidden');
        // Reset form
        signupForm.reset();
        cityInput.required = geolocationAvailable;
        zipInput.required = geolocationAvailable;
        countrySelect.required = geolocationAvailable;
        stateContainer.classList.add('hidden');
        stateSelect.required = false;
        errorDiv.classList.add('hidden');
    });

    // Signup form submission
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validateEmail(emailInput.value)) {
            emailInput.classList.add('border-red-500', 'animate-pulse-red');
            errorDiv.textContent = 'Please enter a valid email address.';
            errorDiv.classList.remove('hidden');
            return;
        } else {
            emailInput.classList.remove('border-red-500', 'animate-pulse-red');
        }

        if (!validatePhone(phoneInput.value, countrySelect.value)) {
            phoneInput.classList.add('border-red-500', 'animate-pulse-red');
            errorDiv.textContent = 'Please enter a valid phone number for the selected country.';
            errorDiv.classList.remove('hidden');
            return;
        } else {
            phoneInput.classList.remove('border-red-500', 'animate-pulse-red');
            phoneInput.value = formatPhone(phoneInput.value, countrySelect.value);
        }

        // Submit form via fetch
        try {
            const formData = new FormData(signupForm);
            const response = await fetch('submit.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                // Show success modal
                successModal.classList.remove('hidden');
            } else {
                errorDiv.textContent = result.error || 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Submission error:', error);
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    });

    // Handle YouTube click
    window.handleYouTubeClick = (e) => {
        e.preventDefault();
        console.log('YouTube link clicked');

        // Open YouTube in a new tab immediately
        const youtubeWindow = window.open('https://www.youtube.com/@JavascriptMastery', '_blank');
        if (!youtubeWindow) {
            console.error('Failed to open YouTube window');
        }

        // Log click asynchronously
        const formData = new FormData();
        formData.append('time_zone', Intl.DateTimeFormat().resolvedOptions().timeZone);
        formData.append('latitude', geolocationData.latitude || '');
        formData.append('longitude', geolocationData.longitude || '');
        formData.append('country', geolocationData.country || '');

        fetch('youtube-click.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            console.log('YouTube click logged:', result);
        })
        .catch(error => {
            console.error('YouTube click logging error:', error);
        });
    };
});