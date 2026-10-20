(function () {
  'use strict';

  const API = '../api/user/addresses.php';
  const ME_API = '../api/auth/me.php';

  const form = document.getElementById('address-form');
  const formCard = document.getElementById('new-addr-form');
  const addressList = document.getElementById('address-list');
  const message = document.getElementById('address-message');

  let csrfToken = '';

  function showMessage(text, success = false) {
    if (!message) return;

    message.textContent = text;
    message.style.color = success ? '#287a46' : '#b42318';
    message.style.marginTop = '12px';
    message.style.fontWeight = '600';
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  async function getCsrfToken() {
    const response = await fetch(ME_API, {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data.message || 'Unable to verify your session.');
    }

    csrfToken =
      data.csrf_token ||
      data.csrfToken ||
      data.user?.csrf_token ||
      '';

    if (!csrfToken) {
      throw new Error('CSRF token was not returned by the server.');
    }

    return csrfToken;
  }

  async function apiRequest(method, body = null, id = null) {
    if (!csrfToken) {
      await getCsrfToken();
    }

    let url = API;

    if (id) {
      url += '?id=' + encodeURIComponent(id);
    }

    const headers = {
      'Accept': 'application/json',
      'X-CSRF-Token': csrfToken
    };

    const options = {
      method,
      credentials: 'include',
      headers
    };

    if (body !== null) {
      headers['Content-Type'] = 'application/json';

      headers['Idempotency-Key'] =
        'address-' +
        Date.now() +
        '-' +
        Math.random().toString(36).slice(2);

      options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      if (response.status === 401) {
        throw new Error('Please log in again.');
      }

      if (response.status === 419) {
        csrfToken = '';
        throw new Error('Your security session expired. Please refresh the page.');
      }

      throw new Error(
        data.message ||
        data.error ||
        'Address request failed.'
      );
    }

    return data;
  }

  function addressText(address) {
    const lines = [];

    if (address.addressLine1) {
      lines.push(escapeHtml(address.addressLine1));
    }

    if (address.addressLine2) {
      lines.push(escapeHtml(address.addressLine2));
    }

    const location = [
      address.city,
      address.state,
      address.pincode
    ].filter(Boolean).map(escapeHtml).join(', ');

    if (location) {
      lines.push(location);
    }

    if (address.landmark) {
      lines.push(
        'Near ' + escapeHtml(address.landmark)
      );
    }

    if (address.phone) {
      lines.push(
        escapeHtml(address.phone)
      );
    }

    return lines.join('<br>');
  }

  function renderAddresses(addresses) {
    if (!addressList) return;

    if (!Array.isArray(addresses) || addresses.length === 0) {
      addressList.innerHTML = `
        <div class="col-12">
          <div style="
            padding:32px;
            border:1.5px dashed var(--border-warm);
            border-radius:var(--r-xl);
            background:var(--bg-cream);
            text-align:center;
          ">
            <i class="fa-solid fa-location-dot"
               style="font-size:2rem;color:var(--saffron);margin-bottom:12px;"></i>

            <div style="font-weight:700;color:var(--text-primary);">
              No saved addresses
            </div>

            <div style="font-size:.875rem;color:var(--text-muted);margin-top:5px;">
              Add an address to make checkout faster.
            </div>
          </div>
        </div>
      `;

      return;
    }

    addressList.innerHTML = addresses.map(address => {
      const isDefault =
        address.isDefault === true ||
        address.isDefault === 1 ||
        address.is_default === true ||
        address.is_default === 1;

      const label = address.label || 'Address';

      return `
        <div class="col-lg-4">
          <div style="
            padding:24px;
            border:${isDefault ? '2px solid var(--saffron)' : '1.5px solid var(--border-warm)'};
            border-radius:var(--r-xl);
            background:${isDefault ? 'var(--saffron-pale)' : 'var(--bg-primary)'};
            position:relative;
          ">

            ${
              isDefault
                ? `
                  <span style="
                    position:absolute;
                    top:14px;
                    right:14px;
                    font-size:.6875rem;
                    background:var(--saffron);
                    color:white;
                    padding:3px 10px;
                    border-radius:var(--r-full);
                    font-weight:700;
                  ">
                    DEFAULT
                  </span>
                `
                : ''
            }

            <div style="
              font-size:.8125rem;
              font-weight:700;
              color:var(--saffron-dark);
              margin-bottom:8px;
            ">
              <i class="fa-solid fa-location-dot"></i>
              ${escapeHtml(label)}
            </div>

            <div style="
              font-size:.9rem;
              color:var(--text-primary);
              font-weight:600;
              margin-bottom:4px;
            ">
              ${escapeHtml(address.fullName || '')}
            </div>

            <div style="
              font-size:.875rem;
              color:var(--text-secondary);
              line-height:1.7;
            ">
              ${addressText(address)}
            </div>

            <div class="d-flex gap-2 mt-3">

              ${
                !isDefault
                  ? `
                    <button
                      type="button"
                      class="bc-btn bc-btn-outline bc-btn-sm"
                      data-action="default"
                      data-id="${escapeHtml(address.id)}">
                      <i class="fa-solid fa-check"></i>
                      Set Default
                    </button>
                  `
                  : ''
              }

              <button
                type="button"
                class="bc-btn bc-btn-outline bc-btn-sm"
                data-action="delete"
                data-id="${escapeHtml(address.id)}"
                style="color:#E53E3E;border-color:#E53E3E;">
                <i class="fa-solid fa-trash-can"></i>
              </button>

            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  async function loadAddresses() {
    try {
      const data = await apiRequest('GET');

      const addresses =
        data.addresses ||
        data.data ||
        [];

      renderAddresses(addresses);

    } catch (error) {
      if (addressList) {
        addressList.innerHTML = `
          <div class="col-12">
            <div style="
              padding:20px;
              border:1px solid #f0b4b4;
              border-radius:var(--r-xl);
              color:#b42318;
              background:#fff5f5;
            ">
              ${escapeHtml(error.message)}
            </div>
          </div>
        `;
      }
    }
  }

  function openForm() {
    if (!formCard) return;

    formCard.style.display = 'block';

    formCard.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
  }

  function closeForm() {
    if (formCard) {
      formCard.style.display = 'none';
    }

    if (form) {
      form.reset();
    }

    showMessage('');
  }

  async function saveAddress(event) {
    event.preventDefault();

    if (!form) return;

    const button = form.querySelector('button[type="submit"]');

    const data = {
      label: form.elements.label.value.trim(),
      fullName: form.elements.fullName.value.trim(),
      phone: form.elements.phone.value.replace(/\D/g, ''),
      pincode: form.elements.pincode.value.trim(),
      addressLine1: form.elements.addressLine1.value.trim(),
      addressLine2: form.elements.addressLine2.value.trim(),
      city: form.elements.city.value.trim(),
      state: form.elements.state.value.trim(),
      landmark: form.elements.landmark.value.trim(),
      isDefault: form.elements.isDefault.checked
    };

    if (!data.fullName) {
      showMessage('Full name is required.');
      return;
    }

    if (!/^[6-9]\d{9}$/.test(data.phone)) {
      showMessage('Enter a valid 10-digit Indian mobile number.');
      return;
    }

    if (!/^\d{6}$/.test(data.pincode)) {
      showMessage('Enter a valid 6-digit pincode.');
      return;
    }

    if (!data.addressLine1) {
      showMessage('Address Line 1 is required.');
      return;
    }

    if (!data.city) {
      showMessage('City is required.');
      return;
    }

    if (!data.state) {
      showMessage('State is required.');
      return;
    }

    try {
      button.disabled = true;
      showMessage('Saving address...', true);

      await apiRequest('POST', data);

      showMessage('Address saved successfully.', true);

      await loadAddresses();

      setTimeout(() => {
        closeForm();
      }, 500);

    } catch (error) {
      showMessage(error.message || 'Unable to save address.');
    } finally {
      button.disabled = false;
    }
  }

  async function handleAddressAction(action, id) {
    if (!id) return;

    try {
      if (action === 'delete') {
        if (!confirm('Delete this address?')) {
          return;
        }

        await apiRequest('DELETE', null, id);

        await loadAddresses();

        return;
      }

      if (action === 'default') {
        await apiRequest(
          'PATCH',
          {
            id: id,
            isDefault: true
          }
        );

        await loadAddresses();
      }

    } catch (error) {
      alert(error.message || 'Unable to update address.');
    }
  }

  document.addEventListener('click', function (event) {
    const actionButton =
      event.target.closest('[data-action]');

    if (actionButton) {
      handleAddressAction(
        actionButton.dataset.action,
        actionButton.dataset.id
      );

      return;
    }

    const addButton =
      event.target.closest('[data-add-address]');

    if (addButton) {
      event.preventDefault();
      openForm();
    }
  });

  if (form) {
    form.addEventListener('submit', saveAddress);
  }

  getCsrfToken()
    .then(loadAddresses)
    .catch(error => {
      showMessage(error.message);
    });

})();
