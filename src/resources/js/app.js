import './bootstrap';
import 'alpinejs';
import './datepicker';
import '@fortawesome/fontawesome-free/css/all.min.css';
import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

// Get the stored dark mode preference
const storedDarkMode = localStorage.getItem("darkMode");

// Different toggle icons for dark and light mode
const sunIcon = document.querySelector("#dark-mode-toggle .fa-sun");
const moonIcon = document.querySelector("#dark-mode-toggle .fa-moon");

function setDarkMode(enabled) {
  const darkModeToggle = document.getElementById("dark-mode-toggle");
  const sunIcon = darkModeToggle.querySelector(".fa-sun");
  const moonIcon = darkModeToggle.querySelector(".fa-moon");
  if (enabled) {
    document.documentElement.classList.add("dark-mode");
    sunIcon.style.display = "inline-block";
    moonIcon.style.display = "none";
  } else {
    document.documentElement.classList.remove("dark-mode");
    sunIcon.style.display = "none";
    moonIcon.style.display = "inline-block";    
  }
}

// If the preference is found and set to "enabled", apply the dark mode class
if (storedDarkMode === "enabled") {
  setDarkMode(true);
} else if (storedDarkMode === "disabled") {
  setDarkMode(false);
} else {
  // If no stored preference is found, check the system preference
  const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  setDarkMode(prefersDarkMode);
  localStorage.setItem("darkMode", prefersDarkMode ? "enabled" : "disabled");
}

// Darkmode toggle
document.getElementById("dark-mode-toggle").addEventListener("click", () => {
  const darkModeEnabled = !document.documentElement.classList.contains("dark-mode");
  setDarkMode(darkModeEnabled);
  
  // Save the preference to localStorage
  localStorage.setItem("darkMode", darkModeEnabled ? "enabled" : "disabled");
});


