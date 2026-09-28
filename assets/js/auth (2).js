/**
 * Conscious Indi — Sign In / Sign Up
 * Talks to the PHP + MySQL API in /api/auth/*.php
 *
 * Both forms on login.html and register.html use this same file.
 * If your API lives on a different domain than the frontend, change API_BASE below.
 */
(function () {
"use strict";

// Relative by default, so it works whether the site is served from the domain
// root (http://localhost/) or a subfolder (http://localhost/site/).
// If your backend is on a different domain, set e.g. "https://api.yourdomain.com/api"
const API_BASE = "api";

const TOKEN_KEY = "ci_token";
const USER_KEY = "ci_user";

function saveSession(token, user) {
localStorage.setItem(TOKEN_KEY, token);
localStorage.setItem(USER_KEY, JSON.stringify(user));
}

function getToken() {
return localStorage.getItem(TOKEN_KEY);
}

function clearSession() {
localStorage.removeItem(TOKEN_KEY);
localStorage.removeItem(USER_KEY);
}

async function apiPost(path, body) {
const res = await fetch(API_BASE + path, {
method: "POST",
headers: { "Content-Type": "application/json" },
body: JSON.stringify(body),
});
let data;
try {
data = await res.json();
} catch {
data = { success: false, message: "Unexpected server response." };
}
return { ok: res.ok, status: res.status, data };
}

function showAlert(container, message, type) {
if (!container) return;
container.textContent = message;
container.style.display = message ? "block" : "none";
container.className = "bc-auth-alert bc-auth-alert-" + (type || "error");
}

function clearFieldErrors(form) {
form.querySelectorAll(".bc-field-error").forEach((el) => (el.textContent = ""));
form
.querySelectorAll(".bc-auth-input")
.forEach((el) => el.classList.remove("bc-input-invalid"));
}

function showFieldErrors(form, errors) {
if (!errors) return;
Object.entries(errors).forEach(([field, message]) => {
const errorEl = form.querySelector('[data-error-for="' + field + '"]');
const inputEl = form.querySelector('[name="' + field + '"]');
if (errorEl) errorEl.textContent = message;
if (inputEl) inputEl.classList.add("bc-input-invalid");
});
}

function setLoading(button, isLoading, loadingText) {
if (!button) return;
if (isLoading) {
button.dataset.originalHtml = button.innerHTML;
button.disabled = true;
button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ' + loadingText;
} else {
button.disabled = false;
if (button.dataset.originalHtml) button.innerHTML = button.dataset.originalHtml;
}
}

// ---------------- LOGIN ----------------
const loginForm = document.getElementById("loginForm");
if (loginForm) {
const alertBox = document.getElementById("loginAlert");
const submitBtn = document.getElementById("loginSubmitBtn");

loginForm.addEventListener("submit", async function (e) {
e.preventDefault();
showAlert(alertBox, "");
clearFieldErrors(loginForm);

const email = loginForm.email.value.trim();
const password = loginForm.password.value;
const rememberMe = loginForm.rememberMe ? loginForm.rememberMe.checked : false;

if (!email || !password) {
showAlert(alertBox, "Please enter your email and password.", "error");
return;
}

setLoading(submitBtn, true, "Signing in…");
const { ok, status, data } = await apiPost("/auth/login.php", {
email,
password,
rememberMe,
});
setLoading(submitBtn, false);

if (ok && data.success) {
saveSession(data.token, data.user);
window.location.href = "customer/dashboard";
return;
}

if (status === 429) {
showAlert(
alertBox,
data.message || "Too many attempts. Please try again later.",
"error",
);
} else {
showAlert(
alertBox,
data.message || "Something went wrong. Please try again.",
"error",
);
}
});
}

// ---------------- REGISTER ----------------
const registerForm = document.getElementById("registerForm");
if (registerForm) {
const alertBox = document.getElementById("registerAlert");
const submitBtn = document.getElementById("registerSubmitBtn");

registerForm.addEventListener("submit", async function (e) {
e.preventDefault();
showAlert(alertBox, "");
clearFieldErrors(registerForm);

const payload = {
firstName: registerForm.firstName.value.trim(),
lastName: registerForm.lastName.value.trim(),
email: registerForm.email.value.trim(),
mobile: registerForm.mobile.value.trim(),
password: registerForm.password.value,
confirmPassword: registerForm.confirmPassword.value,
agreeTerms: registerForm.agreeTerms.checked,
};

setLoading(submitBtn, true, "Creating account…");
const { ok, status, data } = await apiPost("/auth/register.php", payload);
setLoading(submitBtn, false);

// New account → go to OTP verification
if (ok && data.success) {
sessionStorage.setItem("ci_verify_email", data.email || payload.email);
window.location.href = "/conscious-tarun-development1/otp-verification";
return;
}

// Existing account but email is not verified → go to OTP verification
if (status === 409 && data.code === "EMAIL_NOT_VERIFIED") {
sessionStorage.setItem("ci_verify_email", payload.email);
window.location.href = "/conscious-tarun-development1/otp-verification";
return;
}

// Normal validation/API errors
if (data.errors) {
showFieldErrors(registerForm, data.errors);
showAlert(alertBox, data.message || "Please fix the errors below.", "error");
} else {
showAlert(
alertBox,
data.message || "Something went wrong. Please try again.",
"error",
);
}
});
}

// Expose a couple of helpers globally in case other pages want to check auth state.
window.ConsciousAuth = {
getToken,
isLoggedIn: () => Boolean(getToken()),
logout: function () {
clearSession();
window.location.href = "login";
},
};
})();
