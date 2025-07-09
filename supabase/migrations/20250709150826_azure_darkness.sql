/*
  # Item Report System Database Schema

  1. New Tables
    - `items`
      - `id` (uuid, primary key)
      - `name` (text, required) - Item name
      - `category` (text, required) - Item category
      - `serial_number` (text, optional) - Serial number
      - `purchase_date` (date, optional) - When item was purchased
      - `purchase_price` (decimal, optional) - Original purchase price
      - `current_value` (decimal, optional) - Current estimated value
      - `location` (text, optional) - Where item is stored
      - `status` (enum) - available, checked_out, damaged, lost, maintenance
      - `condition` (enum) - excellent, good, fair, poor, damaged
      - `notes` (text, optional) - Additional notes
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `checkouts`
      - `id` (uuid, primary key)
      - `item_id` (uuid, foreign key to items)
      - `user_name` (text, required) - Name of person who checked out item
      - `user_email` (text, required) - Email of person who checked out item
      - `checkout_date` (date, required) - When item was checked out
      - `due_date` (date, required) - When item should be returned
      - `return_date` (date, optional) - When item was actually returned
      - `status` (enum) - active, returned, overdue
      - `notes` (text, optional) - Checkout notes
      - `created_at` (timestamp)
      - `updated_at` (timestamp)

  2. Security
    - Enable RLS on both tables
    - Add policies for authenticated users to read/write data
    - Add policies for public read access (for demo purposes)

  3. Sample Data
    - Insert sample items with various statuses
    - Insert sample checkouts including overdue items
*/

-- Create custom types
CREATE TYPE item_status AS ENUM ('available', 'checked_out', 'damaged', 'lost', 'maintenance');
CREATE TYPE item_condition AS ENUM ('excellent', 'good', 'fair', 'poor', 'damaged');
CREATE TYPE checkout_status AS ENUM ('active', 'returned', 'overdue');

-- Create items table
CREATE TABLE IF NOT EXISTS items (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name text NOT NULL,
  category text NOT NULL,
  serial_number text,
  purchase_date date,
  purchase_price decimal(10,2),
  current_value decimal(10,2),
  location text,
  status item_status DEFAULT 'available',
  condition item_condition DEFAULT 'good',
  notes text,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Create checkouts table
CREATE TABLE IF NOT EXISTS checkouts (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  item_id uuid REFERENCES items(id) ON DELETE CASCADE,
  user_name text NOT NULL,
  user_email text NOT NULL,
  checkout_date date NOT NULL,
  due_date date NOT NULL,
  return_date date,
  status checkout_status DEFAULT 'active',
  notes text,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Enable RLS
ALTER TABLE items ENABLE ROW LEVEL SECURITY;
ALTER TABLE checkouts ENABLE ROW LEVEL SECURITY;

-- Create policies for public access (for demo purposes)
CREATE POLICY "Allow public read access to items"
  ON items
  FOR SELECT
  TO public
  USING (true);

CREATE POLICY "Allow public write access to items"
  ON items
  FOR ALL
  TO public
  USING (true);

CREATE POLICY "Allow public read access to checkouts"
  ON checkouts
  FOR SELECT
  TO public
  USING (true);

CREATE POLICY "Allow public write access to checkouts"
  ON checkouts
  FOR ALL
  TO public
  USING (true);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_items_status ON items(status);
CREATE INDEX IF NOT EXISTS idx_items_condition ON items(condition);
CREATE INDEX IF NOT EXISTS idx_checkouts_item_id ON checkouts(item_id);
CREATE INDEX IF NOT EXISTS idx_checkouts_status ON checkouts(status);
CREATE INDEX IF NOT EXISTS idx_checkouts_due_date ON checkouts(due_date);

-- Create function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at
CREATE TRIGGER update_items_updated_at
  BEFORE UPDATE ON items
  FOR EACH ROW
  EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_checkouts_updated_at
  BEFORE UPDATE ON checkouts
  FOR EACH ROW
  EXECUTE FUNCTION update_updated_at_column();

-- Insert sample data
INSERT INTO items (name, category, serial_number, purchase_date, purchase_price, current_value, location, status, condition, notes) VALUES
  ('MacBook Pro 16"', 'Laptops', 'MBP2023001', '2023-01-15', 2499.00, 2000.00, 'IT Storage Room A', 'available', 'excellent', 'Latest model with M2 chip'),
  ('Dell Monitor 27"', 'Monitors', 'DM27001', '2023-02-20', 299.99, 250.00, 'IT Storage Room A', 'checked_out', 'good', '4K display'),
  ('Damaged Keyboard', 'Peripherals', 'KB001', '2022-06-10', 89.99, 0.00, 'IT Storage Room B', 'damaged', 'damaged', 'Coffee spill damage - keys not working'),
  ('Lost Tablet', 'Tablets', 'TAB001', '2023-03-01', 599.99, 599.99, 'Unknown', 'lost', 'good', 'Last seen in conference room B'),
  ('Canon Camera', 'Equipment', 'CAM2023001', '2023-04-12', 1299.00, 1100.00, 'Media Room', 'checked_out', 'excellent', 'Professional DSLR camera'),
  ('Projector', 'Equipment', 'PROJ001', '2022-09-15', 799.99, 600.00, 'Conference Room A', 'available', 'good', 'HD projector for presentations'),
  ('Broken Printer', 'Printers', 'HP001', '2022-01-20', 199.99, 0.00, 'IT Storage Room C', 'damaged', 'poor', 'Paper jam mechanism broken'),
  ('iPhone 14', 'Mobile', 'IP14001', '2023-05-01', 999.00, 800.00, 'Unknown', 'lost', 'excellent', 'Company phone - reported missing'),
  ('Standing Desk', 'Furniture', 'SD001', '2023-06-15', 499.99, 450.00, 'Office Floor 2', 'available', 'excellent', 'Electric height adjustable'),
  ('Wireless Mouse', 'Peripherals', 'WM001', '2023-07-01', 49.99, 40.00, 'IT Storage Room A', 'checked_out', 'good', 'Ergonomic design');

-- Insert sample checkouts (including overdue items)
INSERT INTO checkouts (item_id, user_name, user_email, checkout_date, due_date, status, notes) VALUES
  ((SELECT id FROM items WHERE name = 'Dell Monitor 27"'), 'John Smith', 'john.smith@company.com', '2024-01-01', '2024-01-15', 'active', 'For home office setup'),
  ((SELECT id FROM items WHERE name = 'Canon Camera'), 'Sarah Johnson', 'sarah.johnson@company.com', '2023-12-15', '2024-01-05', 'active', 'Marketing photoshoot'),
  ((SELECT id FROM items WHERE name = 'Wireless Mouse'), 'Mike Davis', 'mike.davis@company.com', '2023-12-20', '2024-01-03', 'active', 'Temporary replacement');

-- Update checkout status to overdue for items past due date
UPDATE checkouts 
SET status = 'overdue' 
WHERE status = 'active' AND due_date < CURRENT_DATE;