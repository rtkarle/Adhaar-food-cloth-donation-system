
const choice = document.getElementById("donateChoice");
const food = document.getElementById("foodForm");
const cloth = document.getElementById("clothForm");

function openFood(){
  choice.classList.add("hidden");
  food.classList.remove("hidden");
  cloth.classList.add("hidden");
}

function openCloth(){
  choice.classList.add("hidden");
  food.classList.add("hidden");
  cloth.classList.remove("hidden");
}

function backToChoice(){
  choice.classList.remove("hidden");
  food.classList.add("hidden");
  cloth.classList.add("hidden");
}

/* MOBILE MENU */
const menuToggle = document.getElementById("menuToggle");
const mobileMenu = document.getElementById("mobileMenu");
if (menuToggle && mobileMenu) {
  menuToggle.addEventListener("click", () => {
    mobileMenu.classList.toggle("show");
  });
}

/* REVEAL */
document.querySelectorAll(".reveal").forEach(el => {
  new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) e.target.classList.add("visible","show","active");
    });
  }, { threshold: 0.15 }).observe(el);
});
