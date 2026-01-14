// ========================================
// ĮDĖK Į CHAT PUSLAPIO <script> SEKCIJĄ
// ========================================

// Perraši visus pricing linkus pridedant uid
document.addEventListener('DOMContentLoaded', function() {

  // Funkcija redirect su uid
  function goToPricingWithUid() {
    const uid = localStorage.getItem('tossee_id');
    if (!uid) {
      alert('Session expired. Please refresh.');
      return;
    }
    window.location.href = 'https://tossee.com/pricing-plans?uid=' + uid;
  }

  // 1. Perraši visus <a> linkus į pricing
  document.querySelectorAll('a[href*="pricing"]').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      goToPricingWithUid();
    });
  });

  // 2. Perraši visus mygtukus su data-action="pricing"
  document.querySelectorAll('[data-action="pricing"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      goToPricingWithUid();
    });
  });

  // 3. Jei yra tiesioginis redirect kodas, pakeisk jį
  // Pavyzdys: jei turi window.location.href = 'https://tossee.com/pricing-plans'
  // Pakeisk į:
  window.goToPricing = goToPricingWithUid;

});

// NAUDOJIMAS:
// Vietoj: window.location.href = 'https://tossee.com/pricing-plans';
// Naudok: window.goToPricing();
