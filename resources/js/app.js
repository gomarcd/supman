import './bootstrap';
import 'alpinejs';
import './datepicker';
import '@fortawesome/fontawesome-free/css/all.min.css';

// Get the stored dark mode preference
const storedDarkMode = localStorage.getItem("darkMode");

function setDarkMode(enabled) {
  if (enabled) {
    document.documentElement.classList.add("dark-mode");
  } else {
    document.documentElement.classList.remove("dark-mode");
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
