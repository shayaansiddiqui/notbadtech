"use client";

import { useEffect, useState } from "react";
import { parsePhoneNumberFromString, getCountryCallingCode } from "libphonenumber-js";

const countries = [
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

const usStates = [
  "Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia",
  "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland",
  "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey",
  "New Mexico", "New York", "North Carolina", "North Dakota", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina",
  "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"
];

// Map country names to ISO 3166-1 alpha-2 codes for libphonenumber-js
const countryCodeMap: { [key: string]: string } = {
  "United States": "US",
  "United Kingdom": "GB",
  "Canada": "CA",
  "Australia": "AU",
  "Germany": "DE",
  "France": "FR",
  "India": "IN",
  // Add more mappings as needed for other countries
  // Fallback to 'US' for unmapped countries
};

// Function to detect OS and browser from user-agent
const getUserAgentInfo = () => {
  const ua = navigator.userAgent;
  let os = "Unknown";
  let browser = "Unknown";

  // Detect OS
  if (ua.includes("Windows")) os = "Windows";
  else if (ua.includes("Mac OS")) os = "Mac OS";
  else if (ua.includes("Linux")) os = "Linux";
  else if (ua.includes("Android")) os = "Android";
  else if (ua.includes("iOS")) os = "iOS";

  // Detect Browser
  if (ua.includes("Chrome") && !ua.includes("Edge")) browser = "Chrome";
  else if (ua.includes("Safari") && !ua.includes("Chrome")) browser = "Safari";
  else if (ua.includes("Firefox")) browser = "Firefox";
  else if (ua.includes("Edge")) browser = "Edge";
  else if (ua.includes("Opera")) browser = "Opera";

  return { operating_system: os, browser };
};

export default function SignUpForNewsLetter() {
  const [formData, setFormData] = useState({
    firstName: "",
    lastName: "",
    email: "",
    phone: "",
    city: "",
    state: "",
    zip: "",
    country: "",
    longitude: null as number | null,
    latitude: null as number | null,
    operating_system: "",
    browser: "",
  });
  const [error, setError] = useState<string | null>(null);
  const [emailValid, setEmailValid] = useState(true);
  const [phoneValid, setPhoneValid] = useState(true);
  const [geolocationAvailable, setGeolocationAvailable] = useState(true);

  useEffect(() => {
    let countrySelect: any = null;
    let stateSelect: any = null;

    // Initialize Choices.js for dropdowns
    const initChoices = (selector: string) => {
      if (typeof window !== "undefined" && (window as any).Choices) {
        try {
          const select = new (window as any).Choices(selector, {
            searchEnabled: true,
            searchChoices: true,
            itemSelectText: "",
            shouldSort: true,
          });
          console.log(`Choices.js initialized for ${selector}`);
          return select;
        } catch (error) {
          console.error(`Error initializing Choices.js for ${selector}:`, error);
        }
      } else {
        console.warn(`Choices.js not loaded for ${selector}; using default select behavior.`);
      }
      return null;
    };

    countrySelect = initChoices("#country");
    if (formData.country === "United States" || !geolocationAvailable) {
      stateSelect = initChoices("#state");
    }

    console.log("Current formData.country:", formData.country);
    console.log("Geolocation available:", geolocationAvailable);

    // Get geolocation
    if (navigator.geolocation) {
      console.log("Requesting geolocation...");
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          console.log("Geolocation success:", position.coords);
          setGeolocationAvailable(true);
          try {
            const { latitude, longitude } = position.coords;
            setFormData((prev) => ({
              ...prev,
              longitude,
              latitude,
            }));
            const response = await fetch(
              `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`
            );
            if (!response.ok) {
              throw new Error(`Nominatim API error: ${response.statusText}`);
            }
            const data = await response.json();
            console.log("Nominatim response:", data);
            const address = data.address || {};

            const newFormData = {
              city: address.city || address.town || address.village || "",
              state: address.country === "United States" ? (address.state || "") : "",
              zip: address.postcode || "",
              country: address.country || "",
            };

            setFormData((prev) => ({
              ...prev,
              ...newFormData,
            }));

            if (address.country && countrySelect) {
              console.log("Setting country:", address.country);
              countrySelect.setChoiceByValue(address.country);
            }
            if (address.country === "United States" && address.state && stateSelect && usStates.includes(address.state)) {
              console.log("Setting state:", address.state);
              stateSelect.setChoiceByValue(address.state);
            }
          } catch (error) {
            console.error("Error fetching geolocation data:", error);
            setError("Failed to fetch location data. Please select your country (e.g., United States to include state) and enter details manually.");
            setGeolocationAvailable(false);
          }
        },
        (error) => {
          let errorMessage = "Unable to access location. Please select your country (e.g., United States to include state) and enter details manually.";
          switch (error.code) {
            case error.PERMISSION_DENIED:
              errorMessage = "Location access denied. Please allow location access in your browser settings or select your country (e.g., United States to include state) and enter details manually.";
              break;
            case error.POSITION_UNAVAILABLE:
              errorMessage = "Location information unavailable. Please select your country (e.g., United States to include state) and enter details manually.";
              break;
            case error.TIMEOUT:
              errorMessage = "Location request timed out. Please try again or select your country (e.g., United States to include state) and enter details manually.";
              break;
            default:
              errorMessage = `Geolocation error: ${error.message || "Unknown error"}. Please select your country (e.g., United States to include state) and enter details manually.`;
          }
          console.error(`Geolocation error: Code ${error.code}, Message: ${error.message}`);
          setError(errorMessage);
          setGeolocationAvailable(false);
        },
        { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
      );
    } else {
      console.warn("Geolocation not supported by browser.");
      setError("Geolocation not supported. Please select your country (e.g., United States to include state) and enter details manually.");
      setGeolocationAvailable(false);
    }

    // Set OS and browser from user-agent
    const { operating_system, browser } = getUserAgentInfo();
    setFormData((prev) => ({
      ...prev,
      operating_system,
      browser,
    }));

    return () => {
      if (countrySelect) countrySelect.destroy();
      if (stateSelect) stateSelect.destroy();
    };
  }, [formData.country, geolocationAvailable]);

  const validateEmail = (email: string) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  const validatePhone = (phone: string, country: string) => {
    if (!phone || !country) return false;
    const countryCode = countryCodeMap[country] || "US"; // Fallback to US
    try {
      const phoneNumber = parsePhoneNumberFromString(phone, countryCode as any);
      return phoneNumber ? phoneNumber.isValid() : false;
    } catch (error) {
      console.error("Phone validation error:", error);
      return false;
    }
  };

  const formatPhone = (phone: string, country: string) => {
    if (!phone || !country) return phone;
    const countryCode = countryCodeMap[country] || "US";
    try {
      const phoneNumber = parsePhoneNumberFromString(phone, countryCode as any);
      return phoneNumber ? phoneNumber.formatInternational() : phone;
    } catch (error) {
      console.error("Phone formatting error:", error);
      return phone;
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setError(null);

    if (name === "email") {
      setEmailValid(validateEmail(value));
    }

    if (name === "phone") {
      setPhoneValid(validatePhone(value, formData.country));
    }

    if (name === "state") {
      // Clear city and zip when state changes
      setFormData((prev) => ({
        ...prev,
        [name]: value,
        city: "",
        zip: "",
      }));
    } else if (name === "country" && value !== "United States") {
      // Clear state, city, and zip when country changes to non-U.S.
      setFormData((prev) => ({
        ...prev,
        [name]: value,
        state: "",
        city: "",
        zip: "",
        phone: formatPhone(formData.phone, value),
      }));
    } else {
      setFormData((prev) => ({
        ...prev,
        [name]: value,
        ...(name === "country" ? { phone: formatPhone(formData.phone, value) } : {}),
      }));
    }
    console.log(`Field changed: ${name} = ${value}`);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    if (!validateEmail(formData.email)) {
      setEmailValid(false);
      setError("Please enter a valid email address.");
      return;
    }

    if (!validatePhone(formData.phone, formData.country)) {
      setPhoneValid(false);
      setError("Please enter a valid phone number for the selected country.");
      return;
    }

    const formattedPhone = formatPhone(formData.phone, formData.country);

    try {
      const response = await fetch("/api/submit", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ...formData,
          phone: formattedPhone,
        }),
      });

      const result = await response.json();

      if (response.ok) {
        alert("Thank you for signing up!");
        setFormData({
          firstName: "",
          lastName: "",
          email: "",
          phone: "",
          city: "",
          state: "",
          zip: "",
          country: "",
          longitude: null,
          latitude: null,
          operating_system: "",
          browser: "",
        });
        setEmailValid(true);
        setPhoneValid(true);
        setGeolocationAvailable(true);
      } else {
        setError(result.error || "An error occurred. Please try again.");
      }
    } catch (error) {
      console.error("Submission error:", error);
      setError("An error occurred. Please try again.");
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-5">
      {error && (
        <div className="text-red-600 text-sm text-center">{error}</div>
      )}
      <div className="text-left">
        <label htmlFor="firstName" className="block text-sm font-bold text-gray-800 mb-1">
          First Name
        </label>
        <input
          id="firstName"
          name="firstName"
          type="text"
          value={formData.firstName}
          onChange={handleChange}
          required
          className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]"
        />
      </div>
      <div className="text-left">
        <label htmlFor="lastName" className="block text-sm font-bold text-gray-800 mb-1">
          Last Name
        </label>
        <input
          id="lastName"
          name="lastName"
          type="text"
          value={formData.lastName}
          onChange={handleChange}
          required
          className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]"
        />
      </div>
      <div className="text-left">
        <label htmlFor="email" className="block text-sm font-bold text-gray-800 mb-1">
          Email Address
        </label>
        <input
          id="email"
          name="email"
          type="email"
          value={formData.email}
          onChange={handleChange}
          required
          className={`w-full p-3 border rounded-lg focus:outline-none focus:border-[#2a5298] ${
            !emailValid ? "border-red-500 animate-pulse-red" : "border-gray-300"
          }`}
        />
      </div>
      <div className="text-left">
        <label htmlFor="phone" className="block text-sm font-bold text-gray-800 mb-1">
          Phone Number
        </label>
        <input
          id="phone"
          name="phone"
          type="tel"
          value={formData.phone}
          onChange={handleChange}
          required
          className={`w-full p-3 border rounded-lg focus:outline-none focus:border-[#2a5298] ${
            !phoneValid ? "border-red-500 animate-pulse-red" : "border-gray-300"
          }`}
        />
      </div>
      <div className="text-left">
        <label htmlFor="city" className="block text-sm font-bold text-gray-800 mb-1">
          City
        </label>
        <input
          id="city"
          name="city"
          type="text"
          value={formData.city}
          onChange={handleChange}
          required={!geolocationAvailable}
          className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]"
        />
      </div>
      {(formData.country === "United States" || !geolocationAvailable) && (
        <div className="text-left">
          <label htmlFor="state" className="block text-sm font-bold text-gray-800 mb-1">
            State
          </label>
          <select
            id="state"
            name="state"
            value={formData.state}
            onChange={handleChange}
            required={!geolocationAvailable && formData.country === "United States"}
            className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]"
          >
            <option value="" disabled>
              Select your state
            </option>
            {usStates.map((state) => (
              <option key={state} value={state}>
                {state}
              </option>
            ))}
          </select>
        </div>
      )}
      <div className="text-left">
        <label htmlFor="zip" className="block text-sm font-bold text-gray-800 mb-1">
          Zip
        </label>
        <input
          id="zip"
          name="zip"
          type="text"
          value={formData.zip}
          onChange={handleChange}
          required={!geolocationAvailable}
          className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]"
        />
      </div>
      <div className="text-left">
        <label htmlFor="country" className="block text-sm font-bold text-gray-800 mb-1">
          Country
        </label>
        <select
          id="country"
          name="country"
          value={formData.country}
          onChange={handleChange}
          required={!geolocationAvailable}
          className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2a5298]"
        >
          <option value="" disabled>
            Select your country
          </option>
          {countries.map((country) => (
            <option key={country} value={country}>
              {country}
            </option>
          ))}
        </select>
      </div>
      <button
        type="submit"
        className="w-full bg-[#2a5298] text-white p-4 rounded-lg font-semibold hover:bg-[#1e3c72] transition-colors"
      >
        Sign Up
      </button>
    </form>
  );
}