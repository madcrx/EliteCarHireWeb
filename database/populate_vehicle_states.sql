-- Populate state data for existing vehicles
-- Distributes vehicles across Australian states for testing

-- Update vehicles with sample state data
-- This assigns states in a round-robin fashion to existing vehicles

UPDATE vehicles SET state = 'VIC' WHERE id % 8 = 0 AND status = 'approved';
UPDATE vehicles SET state = 'NSW' WHERE id % 8 = 1 AND status = 'approved';
UPDATE vehicles SET state = 'QLD' WHERE id % 8 = 2 AND status = 'approved';
UPDATE vehicles SET state = 'SA' WHERE id % 8 = 3 AND status = 'approved';
UPDATE vehicles SET state = 'WA' WHERE id % 8 = 4 AND status = 'approved';
UPDATE vehicles SET state = 'TAS' WHERE id % 8 = 5 AND status = 'approved';
UPDATE vehicles SET state = 'NT' WHERE id % 8 = 6 AND status = 'approved';
UPDATE vehicles SET state = 'ACT' WHERE id % 8 = 7 AND status = 'approved';

-- Ensure all vehicles have a state (default to VIC if NULL)
UPDATE vehicles SET state = 'VIC' WHERE state IS NULL OR state = '';
