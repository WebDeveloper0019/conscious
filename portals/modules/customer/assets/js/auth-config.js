window.ConsciousIndiAuthConfig = window.ConsciousIndiAuthConfig || {
    mode: 'api',
    apiBaseUrl: '',
    endpoints: {
        login: '/conscious/api/auth/login.php',
        register: '/conscious/api/auth/register.php'
    },
    redirects: { afterLogin: '/conscious/account', afterRegister: '/conscious/account/verify-email' }
};
