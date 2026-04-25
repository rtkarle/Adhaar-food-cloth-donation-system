<?php
/**
 * Shared public footer — include at bottom of every public HTML page.
 * Usage: <?php require 'includes/footer.php'; ?>
 */
?>
<footer class="footer">
  <div class="footer-container">
    <div class="footer-about">
      <h3>Adhaar – The SoulServe</h3>
      <p>Connecting surplus food and clothing to communities in need, reducing waste while restoring dignity and trust.</p>
    </div>
    <div class="footer-links">
      <h4>Quick Links</h4>
      <a href="index.html">Home</a>
      <a href="about.html">About</a>
      <a href="impact.html">Impact</a>
      <a href="donate.html">Donate</a>
      <a href="contact.html">Contact</a>
    </div>
    <div class="footer-contact">
      <h4>Contact</h4>
      <p>📞 +91 82379 17354</p>
      <p>📧 adhaarsoulserve@gmail.com</p>
      <p>📍 Kopargaon, Maharashtra, India</p>
    </div>
  </div>
  <p class="footer-bottom">© 2026 Adhaar – The SoulServe. All Rights Reserved.</p>
</footer>

<script>
/* Mobile menu toggle — shared across all pages */
const _mt = document.getElementById('menuToggle');
const _mn = document.getElementById('mobileMenu');
if (_mt && _mn) {
  _mt.addEventListener('click', () => _mn.classList.toggle('show'));
  document.addEventListener('click', e => {
    if (!_mt.contains(e.target) && !_mn.contains(e.target)) {
      _mn.classList.remove('show');
    }
  });
}

/* Scroll reveal — shared */
const _ro = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('show','visible','active');
    }
  });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => _ro.observe(el));
</script>
