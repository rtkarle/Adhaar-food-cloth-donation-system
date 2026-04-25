/* ================= MOBILE MENU ================= */
const menuToggle = document.getElementById("menuToggle");
const nav = document.getElementById("mobileMenu");

if (menuToggle && nav) {
  menuToggle.addEventListener("click", () => {
    nav.classList.toggle("show");
  });
}

/* ================= SCROLL REVEAL ================= */
const revealElements = document.querySelectorAll(".reveal");

const revealObserver = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("show","visible","active");
        revealObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.15 }
);

revealElements.forEach(el => revealObserver.observe(el));

/* ================= COUNT-UP IMPACT ================= */
const counters = document.querySelectorAll(".impact-card h3");

const countObserver = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.getAttribute("data-count")); // SAFE
        let count = 0;
        const step = Math.max(1, Math.floor(target / 80));

        const timer = setInterval(() => {
          count += step;
          if (count >= target) {
            el.innerText = target + "+";
            clearInterval(timer);
          } else {
            el.innerText = count;
          }
        }, 20);

        countObserver.unobserve(el);
      }
    });
  },
  { threshold: 0.6 }
);

counters.forEach(c => countObserver.observe(c));
const donateTop = document.querySelector(".donate-top");
const donateChoice = document.getElementById("donateChoice");
const foodForm = document.getElementById("foodForm");
const clothForm = document.getElementById("clothForm");

function openChoice(){
  donateTop.classList.add("hide");

  donateChoice.classList.remove("hidden");
  foodForm.classList.add("hidden");
  clothForm.classList.add("hidden");
}

function openFood(){
  donateChoice.classList.add("hidden");
  foodForm.classList.remove("hidden");
  clothForm.classList.add("hidden");
}

function openCloth(){
  donateChoice.classList.add("hidden");
  foodForm.classList.add("hidden");
  clothForm.classList.remove("hidden");
}

/* INITIAL STATE */
document.addEventListener("DOMContentLoaded", () => {
  donateTop.classList.remove("hide");
  donateChoice.classList.add("hidden");
  foodForm.classList.add("hidden");
  clothForm.classList.add("hidden");
});
