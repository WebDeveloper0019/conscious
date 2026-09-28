'use strict';

const endpoint = 'http://127.0.0.1:9223';
const loginUrl = 'http://localhost/conscious/frontend/modules/customer/auth/login.php';
const registerUrl = 'http://localhost/conscious/frontend/modules/customer/auth/register.php';
const wait = (milliseconds) => new Promise((resolve) => setTimeout(resolve, milliseconds));

async function targetFor(fragment) {
    for (let attempt = 0; attempt < 20; attempt += 1) {
        const targets = await fetch(`${endpoint}/json/list`).then((response) => response.json());
        const target = targets.find((item) => item.type === 'page' && item.url.includes(fragment));
        if (target) return target;
        await wait(200);
    }
    throw new Error(`Browser target not found: ${fragment}`);
}

function cdp(webSocketUrl) {
    const socket = new WebSocket(webSocketUrl);
    const pending = new Map();
    let sequence = 0;
    socket.addEventListener('message', (event) => {
        const message = JSON.parse(event.data);
        if (!message.id || !pending.has(message.id)) return;
        const task = pending.get(message.id);
        pending.delete(message.id);
        if (message.error) task.reject(new Error(message.error.message));
        else task.resolve(message.result);
    });
    const ready = new Promise((resolve, reject) => {
        socket.addEventListener('open', resolve, { once: true });
        socket.addEventListener('error', reject, { once: true });
    });
    return {
        async send(method, params = {}) {
            await ready;
            const id = ++sequence;
            return new Promise((resolve, reject) => {
                pending.set(id, { resolve, reject });
                socket.send(JSON.stringify({ id, method, params }));
            });
        },
        close() { socket.close(); }
    };
}

async function evaluate(client, expression) {
    const result = await client.send('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true });
    if (result.exceptionDetails) throw new Error(result.exceptionDetails.text || 'Browser evaluation failed');
    return result.result.value;
}

async function run() {
    const loginTarget = await targetFor('auth/login.php');
    let client = cdp(loginTarget.webSocketDebuggerUrl);
    await client.send('Runtime.enable');
    const filled = await evaluate(client, `(() => { document.querySelector('[data-fill-demo]').click(); return { identifier: document.querySelector('[name=identifier]').value, password: document.querySelector('[name=password]').value }; })()`);
    if (filled.identifier !== 'demo@consciousindi.test' || filled.password !== 'Conscious@123') throw new Error('Demo credentials were not filled correctly.');
    await evaluate(client, `document.querySelector('[data-auth-form]').requestSubmit()`);
    await wait(1050);
    const loginState = await evaluate(client, `({ stored: Boolean(sessionStorage.getItem('ci_demo_customer')), success: Boolean(document.querySelector('.cc-toast.success')), busy: document.querySelector('[data-auth-form]').getAttribute('aria-busy') })`);
    if (!loginState.stored || !loginState.success || loginState.busy !== 'false') throw new Error(`Login demo failed: ${JSON.stringify(loginState)}`);
    client.close();

    const created = await fetch(`${endpoint}/json/new?${encodeURIComponent(registerUrl)}`, { method: 'PUT' }).then((response) => response.json());
    client = cdp(created.webSocketDebuggerUrl);
    await client.send('Runtime.enable');
    await wait(900);
    const registrationSetup = await evaluate(client, `(() => { const values={first_name:'Raj',last_name:'Kumar',email:'raj@example.com',phone:'9876543210',password:'Conscious@123',confirm_password:'Conscious@123'}; const form=document.querySelector('[data-auth-form]'); Object.entries(values).forEach(([name,value])=>{form.elements[name].value=value;form.elements[name].dispatchEvent(new Event('input',{bubbles:true}));}); form.elements.terms.checked=true; return {rules:document.querySelectorAll('.ca-password-rules .met').length}; })()`);
    if (registrationSetup.rules !== 4) throw new Error('Password-strength rules did not update.');
    await evaluate(client, `document.querySelector('[data-auth-form]').requestSubmit()`);
    await wait(1050);
    const registerState = await evaluate(client, `({ formHidden: document.querySelector('[data-auth-form]').hidden, successVisible: !document.querySelector('[data-register-success]').hidden, stored: Boolean(sessionStorage.getItem('ci_demo_customer')) })`);
    if (!registerState.formHidden || !registerState.successVisible || !registerState.stored) throw new Error(`Registration demo failed: ${JSON.stringify(registerState)}`);
    client.close();
    console.log('CUSTOMER_AUTH_BROWSER_TEST_OK');
}

run().catch((error) => { console.error(error.stack || error.message); process.exit(1); });
