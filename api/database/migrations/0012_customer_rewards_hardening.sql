-- Customer reward hardening: expiry + idempotent references.
ALTER TABLE customer_reward_ledger
  ADD COLUMN expires_at DATETIME NULL AFTER description,
  ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER expires_at,
  ADD UNIQUE KEY uniq_customer_reward_reference
    (user_id, entry_type, reference_type, reference_id),
  ADD INDEX idx_customer_reward_expiry (user_id, status, expires_at);

-- Existing rows remain active and non-expiring. New earned points receive an expiry date.
