window.ConsciousIndiAuthConfig = window.ConsciousIndiAuthConfig || {
    mode: 'demo',
    apiBaseUrl: '',
    endpoints: { login: '/api/v1/customer/auth/login', register: '/api/v1/customer/auth/register' },
    redirects: { afterLogin: '../index.php?demo=1', afterRegister: '../index.php?demo=1' },
    demo: { identifier: 'demo@consciousindi.test', password: 'Conscious@123' }
};
