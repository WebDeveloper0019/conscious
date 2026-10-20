ALTER TABLE influencer_documents
  ADD COLUMN uploaded_at DATETIME(6) NULL AFTER created_at;

UPDATE influencer_documents
SET uploaded_at = created_at
WHERE uploaded_at IS NULL;

ALTER TABLE influencer_documents
  MODIFY COLUMN uploaded_at DATETIME(6) NOT NULL;

CREATE INDEX idx_influencer_document_latest
  ON influencer_documents (influencer_id, document_type, uploaded_at);
