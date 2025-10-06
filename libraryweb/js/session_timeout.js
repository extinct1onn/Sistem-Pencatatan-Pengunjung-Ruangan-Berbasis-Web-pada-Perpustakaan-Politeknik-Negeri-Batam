// File: js/session_timeout.js
// Letakkan file ini di folder js/ project Anda

class SessionManager {
    constructor(options = {}) {
        this.checkInterval = options.checkInterval || 10000; // Cek setiap 10 detik
        this.activityEvents = ['mousedown', 'keypress', 'scroll', 'touchstart'];
        this.isWarningShown = false;
        this.checkTimer = null;
        this.countdownTimer = null;
        this.lastActivity = Date.now();

        this.init();
    }

    init() {
        this.createWarningModal();
        this.bindActivityEvents();
        this.startSessionCheck();
    }

    // Buat modal peringatan
    createWarningModal() {
        const modalHTML = `
            <div id="sessionWarningModal" class="session-modal">
                <div class="session-modal-content">
                    <div class="session-modal-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>⚠️ Peringatan Session</h3>
                    <p>Session Anda akan berakhir dalam:</p>
                    <div class="session-countdown" id="sessionCountdown">60</div>
                    <p class="session-modal-text">Klik "Perpanjang" untuk melanjutkan atau Anda akan logout otomatis.</p>
                    <div class="session-modal-buttons">
                        <button id="extendSessionBtn" class="btn-extend">
                            <i class="fas fa-clock"></i> Perpanjang Session
                        </button>
                        <button id="logoutNowBtn" class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout Sekarang
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Bind tombol
        document.getElementById('extendSessionBtn').addEventListener('click', () => {
            this.extendSession();
        });

        document.getElementById('logoutNowBtn').addEventListener('click', () => {
            this.logout();
        });
    }

    // Deteksi aktivitas user (mouse, keyboard, scroll)
    bindActivityEvents() {
        this.activityEvents.forEach(event => {
            document.addEventListener(event, () => {
                this.onUserActivity();
            }, true);
        });
    }

    // Ketika user bergerak/ngetik
    onUserActivity() {
        const now = Date.now();
        // Hanya extend jika sudah lewat 30 detik sejak activity terakhir
        if (now - this.lastActivity > 30000) {
            this.lastActivity = now;
            this.extendSessionSilently();
        }
    }

    // Mulai cek session setiap 10 detik
    startSessionCheck() {
        this.checkTimer = setInterval(() => {
            this.checkSessionStatus();
        }, this.checkInterval);
    }

    // Cek status session ke server
    async checkSessionStatus() {
        try {
            const response = await fetch('check_session.php');
            const data = await response.json();

            switch (data.status) {
                case 'warning':
                    this.showWarning(data.remaining);
                    break;

                case 'timeout':
                case 'logged_out':
                    this.handleTimeout();
                    break;

                case 'active':
                    this.hideWarning();
                    break;
            }
        } catch (error) {
            console.error('Error checking session:', error);
        }
    }

    // Tampilkan modal warning dengan countdown
    showWarning(remainingSeconds) {
        const modal = document.getElementById('sessionWarningModal');
        const countdown = document.getElementById('sessionCountdown');

        if (!this.isWarningShown) {
            modal.style.display = 'flex';
            this.isWarningShown = true;
        }

        countdown.textContent = remainingSeconds;

        // Update countdown setiap detik
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }

        let remaining = remainingSeconds;
        this.countdownTimer = setInterval(() => {
            remaining--;
            countdown.textContent = remaining;

            if (remaining <= 0) {
                clearInterval(this.countdownTimer);
                this.handleTimeout();
            }
        }, 1000);
    }

    // Sembunyikan modal warning
    hideWarning() {
        const modal = document.getElementById('sessionWarningModal');
        modal.style.display = 'none';
        this.isWarningShown = false;

        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }
    }

    // Perpanjang session (dari tombol)
    async extendSession() {
        try {
            const response = await fetch('extend_session.php', {
                method: 'POST'
            });
            const data = await response.json();

            if (data.success) {
                this.hideWarning();
                this.showNotification('✅ Session berhasil diperpanjang', 'success');
                this.lastActivity = Date.now();
            } else {
                this.showNotification('❌ Gagal memperpanjang session', 'error');
            }
        } catch (error) {
            console.error('Error extending session:', error);
            this.showNotification('❌ Terjadi kesalahan', 'error');
        }
    }

    // Perpanjang session otomatis (dari aktivitas user)
    async extendSessionSilently() {
        try {
            await fetch('extend_session.php', {
                method: 'POST'
            });
        } catch (error) {
            console.error('Error extending session silently:', error);
        }
    }

    // Handle timeout - redirect ke logout
    handleTimeout() {
        clearInterval(this.checkTimer);
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }

        this.showNotification('⏱️ Session telah berakhir. Anda akan diarahkan ke halaman login.', 'error');

        setTimeout(() => {
            window.location.href = 'logout.php';
        }, 2000);
    }

    // Logout manual
    logout() {
        window.location.href = 'logout.php';
    }

    // Tampilkan notifikasi toast
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `session-notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
}

// Initialize ketika halaman load
document.addEventListener('DOMContentLoaded', function () {
    // Cek apakah di halaman admin
    const isAdminPage = document.body.classList.contains('admin-page') ||
        window.location.pathname.includes('admin');

    if (isAdminPage) {
        new SessionManager({
            checkInterval: 10000 // Cek setiap 10 detik
        });
        console.log('Session Manager aktif');
    }
});