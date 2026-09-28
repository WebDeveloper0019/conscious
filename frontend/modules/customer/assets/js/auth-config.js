window.ConsciousIndiAuthConfig = window.ConsciousIndiAuthConfig || {
    mode: 'demo',
    apiBaseUrl: '',
    endpoints: {
        login: '../../../../api/auth/login.php',
        register: '../../../../api/auth/register.php'
    },
    redirects: { afterLogin: '../index.php?demo=1', afterRegister: '../index.php?demo=1' },
    demo: { identifier: 'demo@consciousindi.test', password: 'Conscious@123' }
};
