window._ = require('lodash');

try {
    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

window.axios.defaults.withCredentials = true;
window.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
window.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error?.response?.status === 419) {
            window.location.reload();
        }

        return Promise.reject(error);
    }
);

const normalizeParams = value => {
    if (Array.isArray(value)) {
        const [first, second] = value;
        return { id: first, hash: second };
    }
    return value || {};
};

window.appRoutes = {
    landing: '/',
    register: '/register',
    'register.store': '/register',
    'register.save': '/register/save-exit',
    'customer.register': '/customer/register',
    'customer.register.store': '/customer/register',
    login: '/login',
    logout: '/logout',
    'password.request': '/forgot-password',
    'password.email': '/forgot-password',
    'password.reset': token => `/reset-password/${token}`,
    'password.update': '/reset-password',
    'verification.notice': '/verify-email',
    'verification.send': '/email/verification-notification',
    'verification.change': '/email/change',
    'verification.verify': params => {
        const { id, hash } = normalizeParams(params);
        return `/email/verify/${id}/${hash}`;
    },
    'invites.show': token => `/accept-invite/${token}`,
    'invites.accept': token => `/accept-invite/${token}`,
    'invites.decline': token => `/accept-invite/${token}/decline`,
    'invites.resend': '/invites/resend',
    'app.dashboard': '/app',
    'app.org.show': '/app/organisation',
    'app.org.update': '/app/organisation',
    'app.shops.index': '/app/shops',
    'app.shops.store': '/app/shops',
    'app.users.index': '/app/users',
    'app.users.store': '/app/users',
    'app.users.status': id => `/app/users/${id}/status`,
    'app.users.resend': id => `/app/users/${id}/resend-invite`,
    'app.staff.home': '/app/staff',
    'app.kiosks.index': '/app/kiosks',
    'app.kiosks.generate': shopId => `/app/shops/${shopId}/kiosks/pairing-code`,
    'app.devices.revoke': id => `/app/devices/${id}/revoke`,
    'kiosk.pair': '/kiosk/pair',
    'kiosk.pair.store': '/kiosk/pair',
    status: '/status',
    'platform.organisations.index': '/platform/organisations',
};

window.route = function route(name, params = null) {
    const entry = window.appRoutes[name];
    if (typeof entry === 'function') {
        return entry(params);
    }
    if (typeof entry === 'string') {
        return entry;
    }
    console.warn(`Missing route mapping for ${name}`);
    return '/';
};

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
