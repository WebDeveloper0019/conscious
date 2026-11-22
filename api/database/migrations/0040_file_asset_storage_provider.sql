ALTER TABLE `file_assets`
  ADD COLUMN `storage_provider` VARCHAR(16) NOT NULL DEFAULT 'local' AFTER `purpose`,
  ADD INDEX `idx_file_storage_provider` (`storage_provider`, `scan_status`, `deleted_at`);
