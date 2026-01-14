// ========================================
// WORDPRESS SNIPPET - UID FIX
// Code Snippets plugin arba functions.php
// ========================================

add_action('wp_footer', 'tossee_add_uid_to_pricing_links');

function tossee_add_uid_to_pricing_links() {
    // Veikia tik chat puslapyje (pakeisk 'chat' į tavo puslapio slug)
    if (!is_page('chat')) return;
    ?>
    <script>
    (function(){
        function goToPricingWithUid() {
            const uid = localStorage.getItem('tossee_id');
            if (!uid) {
                alert('Session expired. Please refresh.');
                return;
            }
            window.location.href = 'https://tossee.com/pricing-plans?uid=' + uid;
        }

        // Auto-fix visiems pricing linkams
        document.addEventListener('DOMContentLoaded', function() {
            // Linkai
            document.querySelectorAll('a[href*="pricing"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    goToPricingWithUid();
                });
            });

            // Mygtukai
            document.querySelectorAll('[data-action="pricing"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    goToPricingWithUid();
                });
            });
        });

        // Globalus funkcija
        window.goToPricing = goToPricingWithUid;
    })();
    </script>
    <?php
}
