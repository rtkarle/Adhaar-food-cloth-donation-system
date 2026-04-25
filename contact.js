/* ================= MOBILE MENU ================= */
const menuToggle = document.getElementById("menuToggle");
const mobileMenu = document.getElementById("mobileMenu");
if (menuToggle && mobileMenu) {
  menuToggle.addEventListener("click", () => {
    mobileMenu.classList.toggle("show");
  });
}

/* ================= REVEAL ON SCROLL ================= */
const reveals = document.querySelectorAll(".reveal");

const observer = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("active","visible","show");
      }
    });
  },
  { threshold: 0.2 }
);

reveals.forEach(el => observer.observe(el));

/* ================= INPUT MICRO-INTERACTION ================= */
document.querySelectorAll(".input-group input, .input-group textarea")
  .forEach(input => {
    input.addEventListener("focus", () => {
      input.parentElement.style.transform = "scale(1.02)";
    });
    input.addEventListener("blur", () => {
      input.parentElement.style.transform = "scale(1)";
    });
  });

/* ================= FORM SUBMIT FEEDBACK ================= */
const form = document.getElementById("contactForm");
if (form) {
  form.addEventListener("submit", () => {
    const btn = form.querySelector("button");
    if (btn) btn.innerText = "Sending...";
  });
}
