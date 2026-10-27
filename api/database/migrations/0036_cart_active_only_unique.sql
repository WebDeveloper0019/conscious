-- A customer may have many completed carts, but only one active cart.
-- The old (user_id, status) unique key incorrectly prevented a second purchase.
ALTER TABLE carts
  DROP INDEX uniq_active_user_cart,
  ADD COLUMN active_user_id CHAR(36)
    GENERATED ALWAYS AS (CASE WHEN status = 'active' THEN user_id ELSE NULL END) STORED,
  ADD UNIQUE KEY uniq_active_user_cart (active_user_id),
  ADD KEY idx_cart_user_status (user_id, status);
