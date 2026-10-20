
document.addEventListener('DOMContentLoaded', () => {
    const wishlistBody = document.getElementById('ci-wishlist-body');

    if (!wishlistBody) return;

    const apiUrl = '/conscious-main-new/api/user/wishlist.php';

    async function loadWishlist() {
        wishlistBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; padding:24px;">
                    Loading wishlist...
                </td>
            </tr>
        `;

        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok || data.success !== true) {
                throw new Error(data.message || 'Could not load wishlist.');
            }

            const items = Array.isArray(data.items) ? data.items : [];


const countElement = document.getElementById('ci-wishlist-count');
const totalElement = document.getElementById('ci-wishlist-total-value');
const savingsElement = document.getElementById('ci-wishlist-savings');

const totalValue = items.reduce((sum, item) => {
    return sum + Math.max(0, Number(item.productPrice) || 0);
}, 0);

if (countElement) {
    countElement.textContent = items.length;
}

if (totalElement) {
    totalElement.textContent = '₹' + totalValue.toLocaleString('en-IN');
}

if (savingsElement) {
    savingsElement.textContent = '₹0';
}


            if (items.length === 0) {
                wishlistBody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align:center; padding:24px;">
                            Your wishlist is empty.
                        </td>
                    </tr>
                `;
                return;
            }

            wishlistBody.innerHTML = items.map(item => {
                const name = escapeHtml(item.productName || 'Product');
                const vendor = escapeHtml(item.vendorName || 'Seller');
                const price = Number(item.productPrice) || 0;
                const date = item.createdAt
                    ? escapeHtml(new Date(item.createdAt).toLocaleDateString('en-IN'))
                    : '—';
                const productId = escapeHtml(String(item.productId || ''));

                return `
                    <tr>
                        <td>
                            <div class="ci-table-product-cell">
                                <div class="ci-table-product-img">🛍️</div>
                                <div>
                                    <div class="ci-table-product-name">${name}</div>
                                    <div class="ci-table-product-sku">By ${vendor}</div>
                                </div>
                            </div>
                        </td>
                        <td><strong>₹${price.toLocaleString('en-IN')}</strong></td>
                        <td><span class="ci-status ci-status-pending">Check availability</span></td>
                        <td>${date}</td>
                        <td>
                            <div class="ci-table-actions">
                                <button class="ci-table-action-btn danger"
                                    type="button"
                                    data-remove-wishlist="${productId}"
                                    title="Remove from wishlist"
                                    aria-label="Remove ${name}">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

        } catch (error) {
            console.error('Wishlist loading error:', error);

            wishlistBody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align:center; padding:24px;">
                        Could not load wishlist. Please log in and try again.
                    </td>
                </tr>
            `;
        }
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[char]);
    }
wishlistBody.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-remove-wishlist]');

    if (!button) return;

    const productId = button.dataset.removeWishlist;

    if (!productId) {
        alert('Product ID missing.');
        return;
    }

    if (!confirm('Remove this product from your wishlist?')) {
        return;
    }

    button.disabled = true;

    try {
        const response = await fetch(apiUrl, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                productId: productId
            })
        });

        const result = await response.json();

        if (!response.ok || result.success !== true) {
            throw new Error(result.message || 'Could not remove product.');
        }

        await loadWishlist();

    } catch (error) {
        console.error('Wishlist remove error:', error);
        alert(error.message || 'Could not remove product. Please try again.');
        button.disabled = false;
    }
});

    loadWishlist();
});
