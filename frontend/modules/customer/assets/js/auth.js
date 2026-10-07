(function () {
    'use strict';

    var config = window.ConsciousIndiAuthConfig || {};
    var form = document.querySelector('[data-auth-form]');
    var toastWrap = document.getElementById('cc-toast-wrap');

    if (!form) {
        return;
    }

    /* =========================
       Toast
    ========================= */

    function toast(message, type) {
        var el = document.createElement('div');

        el.className = 'cc-toast ' + (type || '');
        el.textContent = message;

        if (toastWrap) {
            toastWrap.appendChild(el);
        }

        window.setTimeout(function () {
            el.remove();
        }, 4200);
    }


    /* =========================
       Busy State
    ========================= */

    function setBusy(busy) {
        form.setAttribute(
            'aria-busy',
            busy ? 'true' : 'false'
        );

        var button = form.querySelector(
            '[data-submit-button]'
        );

        if (button) {
            button.disabled = busy;
        }
    }


    /* =========================
       Clear Errors
    ========================= */

    function clearErrors() {

        form.querySelectorAll(
            '.ca-field.invalid'
        ).forEach(function (field) {
            field.classList.remove('invalid');
        });

        form.querySelectorAll(
            '[data-error-for]'
        ).forEach(function (error) {
            error.textContent = '';
        });

        var alert = form.querySelector(
            '[data-form-alert]'
        );

        if (alert) {
            alert.hidden = true;
            alert.textContent = '';
        }

        form.querySelectorAll(
            'input'
        ).forEach(function (input) {
            input.setCustomValidity('');
        });
    }


    /* =========================
       Field Error
    ========================= */

    function fieldError(name, message) {

        var input = form.elements.namedItem(name);

        var error = form.querySelector(
            '[data-error-for="' +
            CSS.escape(name) +
            '"]'
        );

        if (
            input &&
            input.closest('.ca-field')
        ) {
            input
                .closest('.ca-field')
                .classList.add('invalid');
        }

        if (error) {
            error.textContent = message;
        }
    }


    /* =========================
       Form Error
    ========================= */

    function formError(message) {

        var alert = form.querySelector(
            '[data-form-alert]'
        );

        if (alert) {
            alert.textContent = message;
            alert.hidden = false;
            alert.focus();
        }
    }


    /* =========================
       Get Form Field
    ========================= */

    function getField(names) {

        for (var i = 0; i < names.length; i++) {

            var field = form.elements.namedItem(
                names[i]
            );

            if (field) {
                return field;
            }
        }

        return null;
    }


    /* =========================
       Validate
    ========================= */

    function validate() {

        clearErrors();

        var valid = true;


        /* =========================
           LOGIN
        ========================= */

        if (
            form.dataset.authForm === 'login'
        ) {

            var identifierField =
                getField([
                    'identifier',
                    'email',
                    'phone',
                    'mobile'
                ]);

            var identifier =
                identifierField
                    ? String(
                        identifierField.value || ''
                    ).trim()
                    : '';

            var emailPattern =
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            var phonePattern =
                /^[6-9][0-9]{9}$/;

            var normalizedIdentifier =
                identifier.replace(/\D/g, '');

            if (
                !emailPattern.test(identifier) &&
                !phonePattern.test(
                    normalizedIdentifier
                )
            ) {

                fieldError(
                    identifierField
                        ? identifierField.name
                        : 'identifier',
                    'Enter a valid email address or 10-digit Indian mobile number.'
                );

                valid = false;
            }
        }


        /* =========================
           REGISTER
        ========================= */

        if (
            form.dataset.authForm === 'register'
        ) {

            var firstNameField =
                getField(['first_name']);

            var lastNameField =
                getField(['last_name']);

            var emailField =
                getField(['email']);

            /*
             * IMPORTANT:
             * Support all possible phone field names.
             */
            var phoneInput =
                getField([
                    'phone',
                    'phone_number',
                    'mobile',
                    'mobile_number'
                ]);

            var passwordField =
                getField(['password']);

            var confirmPasswordField =
                getField([
                    'confirm_password',
                    'password_confirmation'
                ]);

            var terms =
                getField(['terms']);


            /* =========================
               Values
            ========================= */

            var firstName =
                firstNameField
                    ? String(
                        firstNameField.value || ''
                    ).trim()
                    : '';

            var lastName =
                lastNameField
                    ? String(
                        lastNameField.value || ''
                    ).trim()
                    : '';

            var emailValue =
                emailField
                    ? String(
                        emailField.value || ''
                    ).trim()
                    : '';

            var password =
                passwordField
                    ? String(
                        passwordField.value || ''
                    )
                    : '';

            var confirmPassword =
                confirmPasswordField
                    ? String(
                        confirmPasswordField.value || ''
                    )
                    : '';


            /* =========================
               First Name
            ========================= */

            if (firstName.length < 2) {

                fieldError(
                    'first_name',
                    'Enter your first name.'
                );

                valid = false;
            }


            /* =========================
               Last Name
            ========================= */

            if (lastName.length < 2) {

                fieldError(
                    'last_name',
                    'Enter your last name.'
                );

                valid = false;
            }


            /* =========================
               Email
            ========================= */

            var emailPattern =
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (
                !emailPattern.test(emailValue)
            ) {

                fieldError(
                    'email',
                    'Enter a valid email address.'
                );

                valid = false;
            }


            /* =========================
               PHONE
            ========================= */

            if (!phoneInput) {

                formError(
                    'Phone field was not found in the registration form. Please check the phone input name.'
                );

                valid = false;

            } else {

                /*
                 * Remove:
                 * spaces
                 * +
                 * -
                 * brackets
                 * country code characters
                 *
                 * Example:
                 * +91 98765-43210
                 * becomes:
                 * 919876543210
                 */

                var phoneValue =
                    String(
                        phoneInput.value || ''
                    ).replace(/\D/g, '');


                /*
                 * If user enters +91XXXXXXXXXX
                 * or 91XXXXXXXXXX,
                 * remove Indian country code.
                 */

                if (
                    phoneValue.length === 12 &&
                    phoneValue.substring(0, 2) === '91'
                ) {
                    phoneValue =
                        phoneValue.substring(2);
                }


                /*
                 * Keep only 10 digits.
                 */

                phoneValue =
                    phoneValue.slice(0, 10);


                /*
                 * Put cleaned number back
                 * into the actual input.
                 */

                phoneInput.value = phoneValue;


                /*
                 * Indian mobile number:
                 * 6XXXXXXXXX
                 * 7XXXXXXXXX
                 * 8XXXXXXXXX
                 * 9XXXXXXXXX
                 */

                if (
                    !/^[6-9][0-9]{9}$/.test(
                        phoneValue
                    )
                ) {

                    fieldError(
                        phoneInput.name || 'phone',
                        'Enter a valid 10-digit Indian mobile number.'
                    );

                    valid = false;
                }
            }


            /* =========================
               Password
            ========================= */

            if (password.length < 12) {

                fieldError(
                    'password',
                    'Password must contain at least 12 characters.'
                );

                valid = false;

            } else if (
                !/[a-z]/.test(password)
            ) {

                fieldError(
                    'password',
                    'Password must contain a lowercase letter.'
                );

                valid = false;

            } else if (
                !/[A-Z]/.test(password)
            ) {

                fieldError(
                    'password',
                    'Password must contain an uppercase letter.'
                );

                valid = false;

            } else if (
                !/[0-9]/.test(password)
            ) {

                fieldError(
                    'password',
                    'Password must contain a number.'
                );

                valid = false;

            } else if (
                !/[^A-Za-z0-9]/.test(password)
            ) {

                fieldError(
                    'password',
                    'Password must contain a special character.'
                );

                valid = false;
            }


            /* =========================
               Confirm Password
            ========================= */

            if (
                password !== confirmPassword
            ) {

                fieldError(
                    confirmPasswordField
                        ? confirmPasswordField.name
                        : 'confirm_password',
                    'Passwords do not match.'
                );

                valid = false;
            }


            /* =========================
               Terms
            ========================= */

            if (
                !terms ||
                !terms.checked
            ) {

                formError(
                    'Please accept the Terms of Use and Privacy Notice.'
                );

                valid = false;
            }
        }


        return valid;
    }


    /* =========================
       Payload
    ========================= */

    function payloadFromForm() {

        var payload = {};

        new FormData(form).forEach(
            function (value, key) {

                if (
                    key !== 'confirm_password' &&
                    key !== 'password_confirmation'
                ) {
                    payload[key] = value;
                }
            }
        );


        /*
         * Normalize phone field so backend
         * receives "phone".
         */

        var phoneInput =
            getField([
                'phone',
                'phone_number',
                'mobile',
                'mobile_number'
            ]);

        if (phoneInput) {

            var phoneValue =
                String(
                    phoneInput.value || ''
                ).replace(/\D/g, '');

            if (
                phoneValue.length === 12 &&
                phoneValue.substring(0, 2) === '91'
            ) {
                phoneValue =
                    phoneValue.substring(2);
            }

            phoneValue =
                phoneValue.slice(0, 10);

            payload.phone = phoneValue;
        }


        payload.remember =
            form.elements.remember
                ? form.elements.remember.checked
                : false;

        payload.marketing =
            form.elements.marketing
                ? form.elements.marketing.checked
                : false;

        payload.terms =
            form.elements.terms
                ? form.elements.terms.checked
                : false;


        return payload;
    }


    /* =========================
       Demo Session
    ========================= */

    function saveDemoSession(
        user,
        persistent
    ) {

        var storage =
            persistent
                ? window.localStorage
                : window.sessionStorage;

        storage.setItem(
            'ci_demo_customer',
            JSON.stringify(user)
        );
    }


    /* =========================
       Demo Request
    ========================= */

    function demoRequest(
        type,
        payload
    ) {

        return new Promise(
            function (resolve, reject) {

                window.setTimeout(
                    function () {

                        if (
                            type === 'login'
                        ) {

                            if (
                                payload.identifier !==
                                config.demo.identifier ||
                                payload.password !==
                                config.demo.password
                            ) {

                                reject({
                                    message:
                                        'Those demo details do not match.',
                                    errors: {
                                        identifier:
                                            'Check the demo email.',
                                        password:
                                            'Check the demo password.'
                                    }
                                });

                                return;
                            }


                            resolve({
                                user: {
                                    id:
                                        'demo-customer-1',
                                    first_name:
                                        'Rahul',
                                    email:
                                        payload.identifier
                                },
                                redirect:
                                    config.redirects.afterLogin
                            });

                            return;
                        }


                        resolve({
                            user: {
                                id:
                                    'demo-customer-new',
                                first_name:
                                    payload.first_name,
                                last_name:
                                    payload.last_name,
                                email:
                                    payload.email,
                                phone:
                                    payload.phone
                            },
                            redirect:
                                config.redirects.afterRegister
                        });

                    },
                    850
                );
            }
        );
    }


    /* =========================
       API Endpoint
    ========================= */

    function resolveApiEndpoint(type) {

        var configured =
            (
                config.endpoints &&
                config.endpoints[type]
            ) ||
            form.dataset.apiEndpoint ||
            '';

        var endpoint =
            (config.apiBaseUrl || '') +
            configured;


        if (
            /^https?:\/\//i.test(endpoint)
        ) {
            return endpoint;
        }


        if (
            endpoint.charAt(0) !== '/'
        ) {
            return new URL(
                endpoint,
                window.location.href
            ).toString();
        }


        /*
         * Handle project installed inside
         * XAMPP subfolder.
         */

        var marker = '/frontend/';
        var path =
            window.location.pathname;

        var markerIndex =
            path.indexOf(marker);

        var projectRoot =
            markerIndex >= 0
                ? path.slice(
                    0,
                    markerIndex
                )
                : '';


        return projectRoot + endpoint;
    }


    /* =========================
       API Request
    ========================= */

    async function apiRequest(
        type,
        payload
    ) {

        var csrf =
            sessionStorage.getItem(
                'ci_csrf_token'
            ) ||
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.content ||
            '';


        var endpoint =
            resolveApiEndpoint(type);

        var response;


        try {

            response = await fetch(
                endpoint,
                {
                    method: 'POST',

                    credentials: 'include',

                    headers: {
                        'Accept':
                            'application/json',

                        'Content-Type':
                            'application/json',

                        'X-CSRF-Token':
                            csrf
                    },

                    body:
                        JSON.stringify(
                            payload
                        )
                }
            );

        } catch (networkError) {

            throw {
                message:
                    'Could not connect to the registration server. Make sure Apache is running and open this page through http://localhost/, not as a local file.',
                errors: {}
            };
        }


        var data =
            await response
                .json()
                .catch(function () {
                    return {};
                });


        if (!response.ok) {

            throw {
                message:
                    data.message ||
                    (
                        'Registration failed (HTTP ' +
                        response.status +
                        ').'
                    ),

                errors:
                    data.errors || {}
            };
        }


        return data;
    }


    /* =========================
       Submit
    ========================= */

    form.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();


            /*
             * Stop here if validation fails.
             */

            if (!validate()) {
                return;
            }


            setBusy(true);


            var type =
                form.dataset.authForm;

            var payload =
                payloadFromForm();


            try {

                var result =
                    config.mode === 'demo'
                        ? await demoRequest(
                            type,
                            payload
                        )
                        : await apiRequest(
                            type,
                            payload
                        );


                /*
                 * Save demo session.
                 */

                if (
                    config.mode === 'demo' &&
                    result.user
                ) {

                    saveDemoSession(
                        result.user,
                        payload.remember
                    );
                }


                /*
                 * Success.
                 */

                toast(
                    result.message ||
                    (
                        type === 'register'
                            ? 'Account created successfully.'
                            : 'Signed in successfully.'
                    ),
                    'success'
                );


                /*
                 * Redirect.
                 */

                if (
                    result.redirect
                ) {

                    window.setTimeout(
                        function () {

                            window.location.href =
                                result.redirect;

                        },
                        500
                    );
                }


            } catch (error) {

                console.error(
                    'Authentication error:',
                    error
                );


                /*
                 * Backend field errors.
                 */

                if (
                    error.errors
                ) {

                    Object.keys(
                        error.errors
                    ).forEach(
                        function (key) {

                            fieldError(
                                key,
                                error.errors[key]
                            );
                        }
                    );
                }


                formError(
                    error.message ||
                    'Something went wrong. Please try again.'
                );


                setBusy(false);

                return;
            }


            setBusy(false);
        }
    );


    /* =========================
       Phone Input
    ========================= */

    var phone =
        form.querySelector(
            '[data-indian-phone]'
        );


    if (phone) {

        phone.addEventListener(
            'input',
            function () {

                var value =
                    phone.value
                        .replace(/\D/g, '');


                /*
                 * Support +91XXXXXXXXXX
                 */

                if (
                    value.length === 12 &&
                    value.substring(0, 2) === '91'
                ) {

                    value =
                        value.substring(2);
                }


                phone.value =
                    value.slice(0, 10);
            }
        );
    }


    /* =========================
       Password Meter
    ========================= */

    var passwordInput =
        document.getElementById(
            'register-password'
        );


    if (passwordInput) {

        passwordInput.addEventListener(
            'input',
            function () {

                var value =
                    passwordInput.value;


                var checks = {
                    length:
                        value.length >= 12,

                    case:
                        /[a-z]/.test(value) &&
                        /[A-Z]/.test(value),

                    number:
                        /[0-9]/.test(value),

                    symbol:
                        /[^A-Za-z0-9]/.test(value)
                };


                var score =
                    Object.keys(checks)
                        .filter(
                            function (key) {
                                return checks[key];
                            }
                        )
                        .length;


                Object.keys(checks)
                    .forEach(
                        function (key) {

                            var rule =
                                document.querySelector(
                                    '[data-rule="' +
                                    key +
                                    '"]'
                                );

                            if (rule) {

                                rule.classList.toggle(
                                    'met',
                                    checks[key]
                                );
                            }
                        }
                    );


                var meter =
                    document.querySelector(
                        '[data-password-meter]'
                    );


                if (meter) {

                    meter.dataset.score =
                        score;


                    var bar =
                        meter.querySelector('b');

                    if (bar) {

                        bar.style.width =
                            (
                                score * 25
                            ) + '%';
                    }


                    var strong =
                        meter.querySelector(
                            'strong'
                        );

                    if (strong) {

                        strong.textContent =
                            [
                                'Not entered',
                                'Weak',
                                'Fair',
                                'Good',
                                'Strong'
                            ][score];
                    }
                }
            }
        );
    }


    /* =========================
       Demo Fill
    ========================= */

    var demoButton =
        document.querySelector(
            '[data-fill-demo]'
        );


    if (demoButton) {

        demoButton.addEventListener(
            'click',
            function () {

                if (
                    form.elements.identifier
                ) {

                    form.elements.identifier.value =
                        config.demo.identifier;
                }


                if (
                    form.elements.password
                ) {

                    form.elements.password.value =
                        config.demo.password;
                }


                clearErrors();


                toast(
                    'Demo details added. Select “Sign in securely” to continue.',
                    'success'
                );
            }
        );
    }


    /* =========================
       Success Animation
    ========================= */

    function animateSuccess() {

        if (window.gsap) {

            window.gsap.from(
                '[data-register-success] > *',
                {
                    y: 14,
                    opacity: 0,
                    duration: 0.45,
                    stagger: 0.08,
                    ease: 'power2.out'
                }
            );
        }
    }


    /* =========================
       Page Animation
    ========================= */

    if (
        window.gsap &&
        !window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches
    ) {

        window.gsap.from(
            '[data-auth-card]',
            {
                y: 22,
                opacity: 0,
                duration: 0.65,
                ease: 'power3.out'
            }
        );


        window.gsap.from(
            '.ca-story-content > *',
            {
                x: -18,
                opacity: 0,
                duration: 0.55,
                stagger: 0.09,
                delay: 0.12,
                ease: 'power3.out'
            }
        );


        window.gsap.to(
            '.ca-story-orb-one',
            {
                x: 18,
                y: -12,
                duration: 7,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut'
            }
        );


        window.gsap.to(
            '.ca-story-orb-two',
            {
                x: -14,
                y: 16,
                duration: 8,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut'
            }
        );
    }

})();