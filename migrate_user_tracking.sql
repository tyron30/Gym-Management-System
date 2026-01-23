-- Add user tracking columns to existing tables

-- Add created_by to members table
ALTER TABLE members ADD COLUMN created_by INT(11) DEFAULT NULL AFTER created_at;
ALTER TABLE members ADD CONSTRAINT fk_members_created_by FOREIGN KEY (created_by) REFERENCES users(id);

-- Add created_by to payments table
ALTER TABLE payments ADD COLUMN created_by INT(11) DEFAULT NULL AFTER reference_no;
ALTER TABLE payments ADD CONSTRAINT fk_payments_created_by FOREIGN KEY (created_by) REFERENCES users(id);

-- Add created_by to attendance table
ALTER TABLE attendance ADD COLUMN created_by INT(11) DEFAULT NULL AFTER notes;
ALTER TABLE attendance ADD CONSTRAINT fk_attendance_created_by FOREIGN KEY (created_by) REFERENCES users(id);

-- Update existing records to have a default user (admin) if needed
-- You may need to run this manually or adjust based on your data
-- UPDATE members SET created_by = 1 WHERE created_by IS NULL;
-- UPDATE payments SET created_by = 1 WHERE created_by IS NULL;
-- UPDATE attendance SET created_by = 1 WHERE created_by IS NULL;
