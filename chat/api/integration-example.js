/**
 * Tossee Chat API Integration Example
 * Frontend JavaScript integration for access control and payment
 */

class TosseePaymentAPI {
    constructor(apiUrl = 'https://tossee.com/chat/api') {
        this.apiUrl = apiUrl;
        this.uid = null;
        this.accessData = null;
        this.checkInterval = null;
    }

    /**
     * Initialize with user ID
     */
    init(uid) {
        if (!uid || !uid.startsWith('tossee_')) {
            throw new Error('Invalid tossee_id format');
        }
        this.uid = uid;
        return this;
    }

    /**
     * Check user access
     */
    async checkAccess() {
        try {
            const response = await fetch(`${this.apiUrl}/access.php?uid=${this.uid}`);
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            this.accessData = data;
            return data;

        } catch (error) {
            console.error('Access check failed:', error);
            throw error;
        }
    }

    /**
     * Check if user has access
     */
    hasAccess() {
        return this.accessData && this.accessData.allowed;
    }

    /**
     * Get remaining time in seconds
     */
    getRemainingSeconds() {
        return this.accessData ? this.accessData.remaining_seconds : 0;
    }

    /**
     * Format remaining time as MM:SS
     */
    getFormattedTime() {
        const seconds = this.getRemainingSeconds();
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    /**
     * Is user on unlimited plan?
     */
    isUnlimited() {
        return this.accessData && this.accessData.unlimited;
    }

    /**
     * Create payment checkout session
     */
    async createCheckout(planKey) {
        try {
            const response = await fetch(`${this.apiUrl}/create-checkout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    uid: this.uid,
                    plan: planKey
                })
            });

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            return data;

        } catch (error) {
            console.error('Checkout creation failed:', error);
            throw error;
        }
    }

    /**
     * Redirect to payment page
     */
    redirectToPayment() {
        window.location.href = `https://tossee.com/pricing?uid=${this.uid}`;
    }

    /**
     * Start periodic access checks (every 30 seconds)
     */
    startPeriodicCheck(callback, interval = 30000) {
        this.checkInterval = setInterval(async () => {
            try {
                await this.checkAccess();
                if (callback) callback(this.accessData);
            } catch (error) {
                console.error('Periodic check failed:', error);
            }
        }, interval);
    }

    /**
     * Stop periodic checks
     */
    stopPeriodicCheck() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }

    /**
     * Update UI with access status
     */
    updateUI(elementId) {
        const element = document.getElementById(elementId);
        if (!element || !this.accessData) return;

        if (this.isUnlimited()) {
            element.innerHTML = `
                <div class="access-status unlimited">
                    <span class="status-icon">∞</span>
                    <span class="status-text">Unlimited Access</span>
                    <small>Until ${new Date(this.accessData.unlimited_until).toLocaleString()}</small>
                </div>
            `;
        } else {
            const timeLeft = this.getFormattedTime();
            const minutes = Math.floor(this.getRemainingSeconds() / 60);

            element.innerHTML = `
                <div class="access-status ${minutes < 5 ? 'warning' : ''}">
                    <span class="status-icon">⏱</span>
                    <span class="status-text">${timeLeft} remaining</span>
                    ${minutes < 5 ? '<button onclick="tosseeAPI.redirectToPayment()">Add Time</button>' : ''}
                </div>
            `;
        }
    }
}

// Example usage:

/*

// 1. Initialize API
const tosseeAPI = new TosseePaymentAPI();
tosseeAPI.init('tossee_yTrDnR7y');

// 2. Check access before starting chat
async function startChat() {
    try {
        const access = await tosseeAPI.checkAccess();

        if (!access.allowed) {
            // No access - redirect to pricing
            console.log('No access. Redirecting to pricing...');
            tosseeAPI.redirectToPayment();
            return;
        }

        if (access.unlimited) {
            console.log('User has unlimited access');
        } else {
            console.log(`User has ${access.remaining_seconds} seconds left`);
        }

        // Start chat logic here...
        initializeVideoChat();

        // Update UI with remaining time
        tosseeAPI.updateUI('time-display');

        // Start periodic checks
        tosseeAPI.startPeriodicCheck((data) => {
            console.log('Access check:', data);
            tosseeAPI.updateUI('time-display');

            // If access expired during chat
            if (!data.allowed) {
                endChat();
                tosseeAPI.redirectToPayment();
            }
        });

    } catch (error) {
        console.error('Failed to start chat:', error);
    }
}

// 3. Buy a plan from frontend
async function buyPlan(planKey) {
    try {
        const checkout = await tosseeAPI.createCheckout(planKey);

        if (checkout.url) {
            // Redirect to Stripe Checkout
            window.location.href = checkout.url;
        }

    } catch (error) {
        console.error('Payment failed:', error);
        alert('Payment failed. Please try again.');
    }
}

// 4. Stop checks when chat ends
function endChat() {
    tosseeAPI.stopPeriodicCheck();
    console.log('Chat ended');
}

// 5. Handle payment return
window.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const uid = params.get('uid');
    const payment = params.get('payment');

    if (uid) {
        tosseeAPI.init(uid);
    }

    if (payment === 'success') {
        console.log('Payment successful!');
        // Refresh access and continue
        startChat();
    }
});

*/

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TosseePaymentAPI;
}
