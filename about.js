/* ================= MOBILE MENU ================= */
const menuToggle = document.getElementById("menuToggle");
const mobileMenu = document.getElementById("mobileMenu");
if (menuToggle && mobileMenu) {
  menuToggle.addEventListener("click", () => {
    mobileMenu.classList.toggle("show");
  });
}

/* ================= SCROLL REVEAL ================= */
const reveals = document.querySelectorAll(".reveal");

const observer = new IntersectionObserver(entries=>{
  entries.forEach(entry=>{
    if(entry.isIntersecting){
      entry.target.classList.add("visible","show","active");
    }
  });
},{threshold:0.15});

reveals.forEach(r=>observer.observe(r));

/* ================= COUNT UP ================= */
const counters = document.querySelectorAll(".impact-card h3");

const countObserver = new IntersectionObserver(entries=>{
  entries.forEach(entry=>{
    if(entry.isIntersecting){
      const el = entry.target;
      const target = +el.dataset.count;
      let count = 0;
      const step = Math.ceil(target/60);

      const timer = setInterval(()=>{
        count += step;
        if(count>=target){
          el.textContent = target+"+";
          clearInterval(timer);
        }else{
          el.textContent = count;
        }
      },25);

      countObserver.unobserve(el);
    }
  });
},{threshold:0.6});

counters.forEach(c=>countObserver.observe(c));
// Magnetic hover for meta cards
document.querySelectorAll(".meta-box").forEach(card=>{
  card.addEventListener("mousemove", e=>{
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width/2;
    const y = e.clientY - rect.top - rect.height/2;

    card.style.transform = `translate(${x*0.05}px, ${y*0.05}px) translateY(-8px)`;
  });

  card.addEventListener("mouseleave", ()=>{
    card.style.transform = "translateY(0)";
  });
});
/* ================= INITIATIVES INTERACTION ================= */

const initiativeSection = document.querySelector(".initiatives");
const initiativeCards = document.querySelectorAll(".initiative-card");

const initiativeObserver = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        initiativeSection.classList.add("visible");
        initiativeObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.25 }
);

initiativeObserver.observe(initiativeSection);

/* subtle hover depth control */
initiativeCards.forEach(card => {
  card.addEventListener("mousemove", e => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const rotateX = ((y / rect.height) - 0.5) * 6;
    const rotateY = ((x / rect.width) - 0.5) * -6;

    card.style.transform =
      `translateY(-12px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
  });

  card.addEventListener("mouseleave", () => {
    card.style.transform = "translateY(0)";
  });
});
/* ================= PROCESSING CENTER ANIMATION ================= */

const processingSection = document.querySelector(".processing");
const steps = document.querySelectorAll(".processing-box li");

const processingObserver = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        processingSection.classList.add("visible");

        steps.forEach((step, i) => {
          setTimeout(() => {
            step.style.opacity = "1";
            step.style.transform = "translateX(0)";
          }, i * 180);
        });

        processingObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.3 }
);

processingObserver.observe(processingSection);
