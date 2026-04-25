
const menuToggle = document.getElementById("menuToggle");
const mobileMenu = document.getElementById("mobileMenu");

if(menuToggle && mobileMenu){
  menuToggle.addEventListener("click", () => {
    mobileMenu.classList.toggle("show"); // fixed: was .active, nav uses .show
  });
}

/* unified reveal — adds all three classes for compatibility */
const revealElements = document.querySelectorAll(".reveal");

const revealObserver = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible","active","show");
        revealObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.15 }
);

revealElements.forEach(el => revealObserver.observe(el));

/* =========================================
   COUNT-UP ANIMATION (IMPACT NUMBERS)
========================================= */
const countElements = document.querySelectorAll("[data-count]");

const countObserver = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCount(entry.target);
        countObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.6 }
);

countElements.forEach(el => countObserver.observe(el));

function animateCount(el) {
  const target = Number(el.dataset.count);
  let current = 0;

  const duration = 1500; // ms
  const startTime = performance.now();

  function update(now) {
    const progress = Math.min((now - startTime) / duration, 1);
    current = Math.floor(progress * target);
    el.textContent = current + "+";

    if (progress < 1) {
      requestAnimationFrame(update);
    } else {
      el.textContent = target + "+";
    }
  }

  requestAnimationFrame(update);
}