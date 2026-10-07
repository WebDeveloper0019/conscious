window.ConsciousIndiAuthConfig = window.ConsciousIndiAuthConfig || {
    mode: 'api',
    apiBaseUrl: '',
    endpoints: {
        login: '../../../../api/auth/login.php',
        register: '../../../../api/auth/register.php'
    },
    redirects: { afterLogin: '../index.php', afterRegister: 'verify-email.php' },
    demo: { identifier: 'demo@consciousindi.test', password: 'Conscious@123' }
};
