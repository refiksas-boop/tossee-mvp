/**
 * CHAT PUSLAPIO FIX - UID į pricing puslapį
 *
 * Įdėk šį kodą į chat.tossee.com HTML failo <script> sekciją
 * arba į atskirą .js failą
 */

(function() {
  'use strict';

  const PRICING_URL = 'https://tossee.com/pricing-plans';
  const CHAT_URL = 'https://chat.tossee.com';

  /**
   * Nukreipia į pricing puslapį SU uid parametru
   */
  window.redirectToPricingWithUID = function() {
    const uid = getUserIdForRedirect();

    if (!uid) {
      console.error('❌ redirectToPricing: No UID found');
      alert('⚠️ Session error. Please refresh and login again.');
      return;
    }

    // Sudarom URL su uid parametru
    const pricingUrlWithUid = PRICING_URL + '?uid=' + encodeURIComponent(uid);

    console.log('✅ Redirecting to pricing with UID:', pricingUrlWithUid);
    window.location.href = pricingUrlWithUid;
  };

  /**
   * Gauna UID iš localStorage arba URL
   */
  function getUserIdForRedirect() {
    // 1. Pirmiausia iš localStorage
    let uid = localStorage.getItem('tossee_id');
    if (uid) {
      console.log('✅ Found UID in localStorage:', uid);
      return uid;
    }

    // 2. Jei nėra localStorage, bandome iš URL
    const params = new URLSearchParams(window.location.search);
    uid = params.get('uid');
    if (uid) {
      console.log('✅ Found UID in URL:', uid);
      // Išsaugom į localStorage
      localStorage.setItem('tossee_id', uid);
      return uid;
    }

    // 3. Jei niekur nėra
    console.error('❌ No UID found anywhere');
    return null;
  }

  /**
   * USAGE EXAMPLES:
   *
   * 1. Button click:
   *    <button onclick="redirectToPricingWithUID()">Upgrade</button>
   *
   * 2. Programmatically:
   *    if (timeExpired) {
   *      redirectToPricingWithUID();
   *    }
   *
   * 3. Link replacement:
   *    document.querySelectorAll('a[href*="pricing"]').forEach(link => {
   *      link.addEventListener('click', (e) => {
   *        e.preventDefault();
   *        redirectToPricingWithUID();
   *      });
   *    });
   */

  // AUTO-FIX: Automatiškai pataiso visus pricing linkus
  document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Auto-fixing pricing links with UID...');

    // Randa visus pricing linkus
    const pricingLinks = document.querySelectorAll(
      'a[href*="pricing-plans"], a[href*="pricing"], button[data-action="upgrade"]'
    );

    pricingLinks.forEach(function(element) {
      element.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('🔗 Pricing link clicked, redirecting with UID...');
        redirectToPricingWithUID();
      });
    });

    console.log(`✅ Fixed ${pricingLinks.length} pricing links`);
  });

  // Debug info
  console.log('=== CHAT UID DEBUG ===');
  console.log('Current UID:', localStorage.getItem('tossee_id'));
  console.log('URL UID:', new URLSearchParams(window.location.search).get('uid'));
  console.log('redirectToPricingWithUID function available:', typeof window.redirectToPricingWithUID);
  console.log('======================');

})();
