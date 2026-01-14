// ============================================
// PRICING PUSLAPIO SNIPPET
// Automatiškai paima UID iš localStorage
// ============================================

add_action('wp_footer', 'tossee_pricing_auto_uid');

function tossee_pricing_auto_uid() {
    // Veikia TIK pricing puslapyje
    if (!is_page('pricing-plans')) return;
    ?>
    <script>
    (function(){
        // 1. Patikrinam ar URL jau turi uid
        const params = new URLSearchParams(window.location.search);
        let uid = params.get('uid');

        // 2. Jei URL neturi uid, paimam iš localStorage
        if (!uid) {
            uid = localStorage.getItem('tossee_id');

            // 3. Jei radome uid, perkraunam puslapį su uid URL
            if (uid) {
                console.log('✅ Found UID in localStorage, reloading with URL param:', uid);
                window.location.href = 'https://tossee.com/pricing-plans/?uid=' + uid;
                return;
            } else {
                console.warn('⚠️ No UID found in localStorage or URL');
            }
        }

        // 4. Jei URL jau turi uid, išsaugom į localStorage
        if (uid) {
            localStorage.setItem('tossee_id', uid);
            console.log('✅ UID in URL:', uid);
        }
    })();
    </script>
    <?php
}
