/* ================= REVEAL ON SCROLL ================= */
const reveals = document.querySelectorAll(".reveal");

const revealObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add("show");
      entry.target.classList.add("visible");
    }
  });
}, { threshold: 0.15 });

reveals.forEach(el => revealObserver.observe(el));

/* ================= COUNT UP ================= */
const counters = document.querySelectorAll("[data-count]");

const countObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const el = entry.target;
      const target = +el.dataset.count;
      let count = 0;
      const step = Math.max(1, Math.ceil(target / 60));

      const timer = setInterval(() => {
        count += step;
        if (count >= target) {
          el.textContent = target + "+";
          clearInterval(timer);
        } else {
          el.textContent = count;
        }
      }, 25);

      countObserver.unobserve(el);
    }
  });
}, { threshold: 0.5 });

counters.forEach(c => countObserver.observe(c));

/* ================= JAR FILL ================= */
const jar = document.querySelector(".jar-liquid");
if (jar) {
  const percent = jar.dataset.percent;
  setTimeout(() => {
    jar.style.height = percent + "%";
  }, 800);
}

/* ================= STEP REVEAL ================= */
const steps = document.querySelectorAll(".step");
const stepObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add("show");
    }
  });
}, { threshold: 0.25 });

steps.forEach(step => stepObserver.observe(step));
