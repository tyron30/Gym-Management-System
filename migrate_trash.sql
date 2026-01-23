-- Migration to add soft delete functionality
-- Add deleted_at columns to tables

ALTER TABLE members ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE payments ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE attendance ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;

-- Create indexes for better performance on soft deletes
CREATE INDEX idx_members_deleted_at ON members(deleted_at);
CREATE INDEX idx_payments_deleted_at ON payments(deleted_at);
CREATE INDEX idx_attendance_deleted_at ON attendance(deleted_at);
