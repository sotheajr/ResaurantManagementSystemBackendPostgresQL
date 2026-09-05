<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sample data for every business table in the system.
 * Idempotent: each table is only seeded when it is currently empty, so
 * re-running does not overwrite existing records.
 */
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedSubCategories();
        $this->seedMenuItems();
        $this->seedTables();
        $this->seedCustomers();
        $this->seedReservations();
        $this->seedSuppliersInventoryPurchases();
        $this->seedPartners();
        $this->seedAttendances();
        $this->seedPayrolls();
    }

    /**
     * Insert rows only if the table is empty.
     */
    private function seedIfEmpty(string $table, array $rows): void
    {
        if (DB::table($table)->count() > 0) {
            echo(strtolower("INFO") . "  Skipped [{$table}] (already has data).\n");
            return;
        }

        $stamped = [];
        foreach ($rows as $row) {
            $row['created_at'] = now();
            $row['updated_at'] = now();
            $stamped[] = $row;
        }

        DB::table($table)->insert($stamped);
        echo("Seeded [{$table}] with " . count($stamped) . " row(s).\n");
    }

    // [CATEGORIES]
    private function seedCategories(): void
    {
        $this->seedIfEmpty('categories', [
            ['category_name' => 'អាហារជ្រក', 'description' => 'Appetizers & Starters'],
            ['category_name' => 'ឨ្អាហារអាង', 'description' => 'Main Courses'],
            ['category_name' => 'អាហារអាងអាយ', 'description' => 'Noodles & Rice dishes'],
            ['category_name' => 'អាឌែលអាងអាយ', 'description' => 'Cold & hot beverages'],
            ['category_name' => 'អាហារអាងអាអ', 'description' => 'Desserts & sweets'],
        ]);
    }

    // [SUB-CATEGORIES]
    private function seedSubCategories(): void
    {
        $this->seedIfEmpty('sub_categories', [
            ['category_id' => 1, 'sub_category_name' => 'សាប់ត្រី', 'description' => 'Seafood Appetizers'],
            ['category_id' => 1, 'sub_category_name' => 'សាប់សាច់ជ្រក', 'description' => 'Meat Appetizers'],
            ['category_id' => 1, 'sub_category_name' => 'សាប់បន្លែ', 'description' => 'Vegetarian Appetizers'],
            ['category_id' => 2, 'sub_category_name' => 'សាច់ជ្រក', 'description' => 'Grilled Dishes'],
            ['category_id' => 2, 'sub_category_name' => 'ចំណី', 'description' => 'Stir-fried Dishes'],
            ['category_id' => 2, 'sub_category_name' => 'ស៊ុប', 'description' => 'Soups'],
            ['category_id' => 3, 'sub_category_name' => 'គុយទាវ', 'description' => 'Noodles'],
            ['category_id' => 3, 'sub_category_name' => 'បាយ', 'description' => 'Rice Dishes'],
            ['category_id' => 4, 'sub_category_name' => 'ទឹកផ្លែឈើ', 'description' => 'Fresh Fruit Juices'],
            ['category_id' => 4, 'sub_category_name' => 'ស្រាបៀនិងម្សៅ', 'description' => 'Beers & Cocktails'],
            ['category_id' => 5, 'sub_category_name' => 'ការ៉េម', 'description' => 'Ice Cream'],
            ['category_id' => 5, 'sub_category_name' => 'នំខេក', 'description' => 'Cakes & Pastries'],
        ]);
    }

    // [MENU]
    private function seedMenuItems(): void
    {
        $this->seedIfEmpty('menu_items', [
            // Appetizers (category 1)
            ['category_id' => 1, 'menu_name' => 'អាហារជ្រកត្រីអាជ្អាហ្វ', 'description' => 'Crispy Fish Spring Rolls', 'price' => 5.50, 'status' => 'Available', 'discount_percent' => 0],
            ['category_id' => 1, 'menu_name' => 'អាហ្វអាងអាជ្អាហ្វអាង', 'description' => 'Crispy Prawns with dip', 'price' => 8.00, 'status' => 'Available', 'discount_percent' => 10],
            ['category_id' => 1, 'menu_name' => 'អាគ្នុចងអាហ្វអាង', 'description' => 'Golden Chicken Wings', 'price' => 6.75, 'status' => 'Available', 'discount_percent' => 0],

            // Main Courses (category 2)
            ['category_id' => 2, 'menu_name' => 'អាហ្វអាងអាងអាហ្វអាង', 'description' => 'Charcoal Grilled Fish', 'price' => 14.00, 'status' => 'Available', 'discount_percent' => 0],
            ['category_id' => 2, 'menu_name' => 'អាហ្វអាងឨអាកអាងអាង', 'description' => 'Grilled Chicken Skewers', 'price' => 12.00, 'status' => 'Available', 'discount_percent' => 5],
            ['category_id' => 2, 'menu_name' => 'អាហារអាងអាកអាងឨអាង', 'description' => 'Beef Steak with rice', 'price' => 16.50, 'status' => 'Unavailable', 'discount_percent' => 0],

            // Noodles & Rice (category 3)
            ['category_id' => 3, 'menu_name' => 'ឨអាង្អយំោអាង', 'description' => 'Khmer Fried Noodles', 'price' => 7.50, 'status' => 'Available', 'discount_percent' => 0],
            ['category_id' => 3, 'menu_name' => 'អាយំលអាងអាហ្វអាង', 'description' => 'Seafood Fried Rice', 'price' => 8.25, 'status' => 'Available', 'discount_percent' => 0],
            ['category_id' => 3, 'menu_name' => 'ឨអាកអាងអាងអាឃអាង', 'description' => 'Beef Noodle Soup', 'price' => 8.50, 'status' => 'Available', 'discount_percent' => 0],

            // Beverages (category 4)
            ['category_id' => 4, 'menu_name' => 'អាឌែលអាងអាងអាឃ', 'description' => 'Khmer Lemonade', 'price' => 2.50, 'status' => 'Available', 'discount_percent' => 0],
            ['category_id' => 4, 'menu_name' => 'អាហ្វអាសអាកអាយ', 'description' => 'Fresh Coconut Water', 'price' => 2.00, 'status' => 'Available', 'discount_percent' => 0],
            ['category_id' => 4, 'menu_name' => 'អាហ្វអាងអាងអាប', 'description' => 'Green Tea (hot)', 'price' => 1.50, 'status' => 'Available', 'discount_percent' => 0],

            // Desserts (category 5)
            ['category_id' => 5, 'menu_name' => 'អាហារអាងអាជ្អាហ្វអាង', 'description' => 'Coconut Sticky Rice', 'price' => 4.00, 'status' => 'Available', 'discount_percent' => 0],
            ['category_id' => 5, 'menu_name' => 'អាហារអាជ្អាវអាងអាអ', 'description' => 'Banana Fritters with honey', 'price' => 3.50, 'status' => 'Available', 'discount_percent' => 0],
        ]);
    }

    // [TABLES]
    private function seedTables(): void
    {
        $this->seedIfEmpty('tables', [
            ['table_number' => 1, 'capacity' => 2, 'location' => 'Indoor', 'status' => 'Available'],
            ['table_number' => 2, 'capacity' => 4, 'location' => 'Indoor', 'status' => 'Available'],
            ['table_number' => 3, 'capacity' => 4, 'location' => 'Indoor', 'status' => 'Occupied'],
            ['table_number' => 4, 'capacity' => 6, 'location' => 'Outdoor', 'status' => 'Available'],
            ['table_number' => 5, 'capacity' => 6, 'location' => 'Outdoor', 'status' => 'Reserved'],
            ['table_number' => 6, 'capacity' => 8, 'location' => 'VIP', 'status' => 'Available'],
            ['table_number' => 7, 'capacity' => 8, 'location' => 'VIP', 'status' => 'Available'],
            ['table_number' => 8, 'capacity' => 10, 'location' => 'Rooftop', 'status' => 'Occupied'],
            ['table_number' => 9, 'capacity' => 2, 'location' => 'Indoor', 'status' => 'Available'],
            ['table_number' => 10, 'capacity' => 4, 'location' => 'Outdoor', 'status' => 'Available'],
        ]);
    }

    // [CUSTOMERS]
    private function seedCustomers(): void
    {
        $this->seedIfEmpty('customers', [
            ['customer_name' => 'សុខ អាហារអាជ្អាង', 'phone' => '0912345001', 'email' => 'sok@gmail.com', 'address' => 'Phnom Penh, Cambodia'],
            ['customer_name' => 'អាហ្វអាង អាកអាងអាហ្វ', 'phone' => '0912345002', 'email' => null, 'address' => 'Siem Reap'],
            ['customer_name' => 'ជអាង អាហ្វអាង', 'phone' => '0912345003', 'email' => 'chan@gmail.com', 'address' => 'Battambang'],
            ['customer_name' => 'ឨអាហ្វអាង អាហ្វអាងអាង', 'phone' => '0912345004', 'email' => null, 'address' => 'Phnom Penh, Cambodia'],
            ['customer_name' => 'អាហារអាងអាហ្វអាង អាជ្អាហ្វ', 'phone' => '0912345005', 'email' => 'dara@gmail.com', 'address' => 'Kampot'],
        ]);
    }

    // [RESERVATIONS]
    private function seedReservations(): void
    {
        $this->seedIfEmpty('reservations', [
            ['customer_id' => 1, 'table_id' => 3, 'reservation_date' => '2026-09-10 19:00:00', 'guest_number' => 4, 'status' => 'Confirmed'],
            ['customer_id' => 2, 'table_id' => 5, 'reservation_date' => '2026-09-11 18:30:00', 'guest_number' => 6, 'status' => 'Pending'],
            ['customer_id' => 3, 'table_id' => 6, 'reservation_date' => '2026-09-12 20:00:00', 'guest_number' => 8, 'status' => 'Confirmed'],
            ['customer_id' => 4, 'table_id' => 2, 'reservation_date' => '2026-09-05 12:00:00', 'guest_number' => 2, 'status' => 'Seated'],
            ['customer_id' => 5, 'table_id' => 8, 'reservation_date' => '2026-09-13 19:30:00', 'guest_number' => 10, 'status' => 'Cancelled'],
        ]);
    }

    // [INVENTORY]
    private function seedSuppliersInventoryPurchases(): void
    {
        // Suppliers
        $this->seedIfEmpty('suppliers', [
            ['supplier_name' => 'អាហារអាជ្អាហ្វអាង អាហារ', 'phone' => '0912111001', 'address' => 'Phnom Penh Market'],
            ['supplier_name' => 'អាហ្វអាងអាជ្អាហ្វ អាហារ', 'phone' => '0912111002', 'address' => 'Siem Reap'],
            ['supplier_name' => 'អាហ្វអាងអាកអាង អាហារ', 'phone' => '0912111003', 'address' => 'Battambang'],
            ['supplier_name' => 'អាឌែលអាងអាយ អាហារ', 'phone' => '0912111004', 'address' => 'Phnom Penh'],
        ]);

        // Inventory (ingredients)
        $this->seedIfEmpty('inventory', [
            ['ingredient_name' => 'អាហ្វអាង (Rice)', 'quantity' => 50.00, 'unit' => 'kg', 'minimum_stock' => 10.00],
            ['ingredient_name' => 'អាហ្វអាងអាជ្អាហ្វ (Noodles)', 'quantity' => 30.00, 'unit' => 'kg', 'minimum_stock' => 8.00],
            ['ingredient_name' => 'អាហ្វអាងអាបអាង (Chicken)', 'quantity' => 20.00, 'unit' => 'kg', 'minimum_stock' => 5.00],
            ['ingredient_name' => 'អាហ្វអាងអាជ្អាហ្វ (Fish)', 'quantity' => 15.00, 'unit' => 'kg', 'minimum_stock' => 5.00],
            ['ingredient_name' => 'អាហ្វអាងអាង (Vegetables)', 'quantity' => 40.00, 'unit' => 'kg', 'minimum_stock' => 10.00],
            ['ingredient_name' => 'អាហ្វអាងអាកអាង (Beef)', 'quantity' => 3.00, 'unit' => 'kg', 'minimum_stock' => 4.00],
            ['ingredient_name' => 'អាហ្វអាងអាជ្អាហ្វ (Cooking Oil)', 'quantity' => 25.00, 'unit' => 'L', 'minimum_stock' => 6.00],
            ['ingredient_name' => 'អាហ្វអាងអាបអាងអាង (Eggs)', 'quantity' => 20.00, 'unit' => 'pcs', 'minimum_stock' => 30.00],
        ]);

        // Purchases + their line items
        $this->seedPurchases([
            ['supplier_id' => 1, 'purchase_date' => '2026-08-20', 'items' => [
                ['inventory_id' => 1, 'quantity' => 10.00, 'unit_price' => 2.00], // Rice 20.00
                ['inventory_id' => 5, 'quantity' => 15.00, 'unit_price' => 1.00], // Veg 15.00
            ]],
            ['supplier_id' => 2, 'purchase_date' => '2026-08-22', 'items' => [
                ['inventory_id' => 4, 'quantity' => 8.00, 'unit_price' => 4.00], // Fish 32.00
            ]],
            ['supplier_id' => 3, 'purchase_date' => '2026-08-25', 'items' => [
                ['inventory_id' => 3, 'quantity' => 10.00, 'unit_price' => 3.00], // Chicken 30.00
                ['inventory_id' => 6, 'quantity' => 5.00, 'unit_price' => 6.00], // Beef 30.00
            ]],
        ]);
    }

    private function seedPurchases(array $purchases): void
    {
        if (DB::table('purchases')->count() > 0) {
            echo("INFO  Skipped [purchases] (already has data).\n");
            return;
        }

        foreach ($purchases as $purchase) {
            $total = 0.00;
            $items = [];

            foreach ($purchase['items'] as $item) {
                $subtotal = round($item['quantity'] * $item['unit_price'], 2);
                $total += $subtotal;
                $items[] = array_merge($item, ['subtotal' => $subtotal]);
            }

            // Use the model so the custom PK (purchase_id) is handled correctly
            $record = \App\Models\Purchase::create([
                'supplier_id' => $purchase['supplier_id'],
                'purchase_date' => $purchase['purchase_date'],
                'total' => round($total, 2),
            ]);

            foreach ($items as $item) {
                \App\Models\PurchaseItem::create([
                    'purchase_id' => $record->purchase_id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
            echo("Seeded purchase #{$record->purchase_id} (total " . number_format($total, 2) . ").\n");
        }
    }

    // [PARTNERS]
    private function seedPartners(): void
    {
        $this->seedIfEmpty('partners', [
            ['partner_name' => 'Grab Food Cambodia', 'contact_name' => 'Ratha', 'phone' => '0912777001', 'email' => 'partner@grab.com', 'address' => 'Phnom Penh', 'partnership_type' => 'Delivery', 'status' => 'Active'],
            ['partner_name' => 'Food Panda Cambodia', 'contact_name' => 'Sok', 'phone' => '0912777002', 'email' => 'partner@foodpanda.com', 'address' => 'Phnom Penh', 'partnership_type' => 'Delivery', 'status' => 'Active'],
            ['partner_name' => '១អាជ្អាហ្វអាង ឨអាហ្វអាង (Royal Hotel)', 'contact_name' => '១អាជ្អាហ្វអាង', 'phone' => '0912777003', 'email' => 'catering@royal.com', 'address' => 'Siem Reap', 'partnership_type' => 'Corporate', 'status' => 'Inactive'],
            ['partner_name' => 'ឨអាហ្វអាងអាជ្អាហ្វអាង អាហារ', 'contact_name' => 'ឨអាហ្វអាង', 'phone' => '0912777004', 'email' => null, 'address' => 'Kampot', 'partnership_type' => 'Supplier', 'status' => 'Active'],
        ]);
    }

    // [ATTENDANCE]
    private function seedAttendances(): void
    {
        $this->seedIfEmpty('attendances', [
            // Admin (user 1)
            ['user_id' => 1, 'date' => '2026-09-01', 'clock_in' => '08:00:00', 'clock_out' => '17:00:00', 'total_hours' => 9.00, 'status' => 'Present', 'notes' => null],
            ['user_id' => 1, 'date' => '2026-09-02', 'clock_in' => '08:45:00', 'clock_out' => '17:00:00', 'total_hours' => 8.25, 'status' => 'Late', 'notes' => 'Traffic'],
            ['user_id' => 1, 'date' => '2026-09-03', 'clock_in' => '08:05:00', 'clock_out' => '17:30:00', 'total_hours' => 9.42, 'status' => 'Present', 'notes' => null],
            ['user_id' => 1, 'date' => '2026-09-04', 'clock_in' => '08:10:00', 'clock_out' => '17:00:00', 'total_hours' => 8.83, 'status' => 'Present', 'notes' => null],

            // Waiter (user 2)
            ['user_id' => 2, 'date' => '2026-09-01', 'clock_in' => '07:55:00', 'clock_out' => '17:00:00', 'total_hours' => 9.08, 'status' => 'Present', 'notes' => null],
            ['user_id' => 2, 'date' => '2026-09-02', 'clock_in' => '08:10:00', 'clock_out' => '18:00:00', 'total_hours' => 9.83, 'status' => 'Present', 'notes' => null],
            ['user_id' => 2, 'date' => '2026-09-03', 'clock_in' => '09:00:00', 'clock_out' => '17:00:00', 'total_hours' => 8.00, 'status' => 'Late', 'notes' => 'Late arrival'],
            ['user_id' => 2, 'date' => '2026-09-04', 'clock_in' => '08:15:00', 'clock_out' => '17:00:00', 'total_hours' => 8.75, 'status' => 'Present', 'notes' => null],

            // Cashier (user 3)
            ['user_id' => 3, 'date' => '2026-09-01', 'clock_in' => '08:00:00', 'clock_out' => '17:00:00', 'total_hours' => 9.00, 'status' => 'Present', 'notes' => null],
            ['user_id' => 3, 'date' => '2026-09-03', 'clock_in' => '08:05:00', 'clock_out' => '16:30:00', 'total_hours' => 8.42, 'status' => 'Half Day', 'notes' => 'Half day leave'],
            ['user_id' => 3, 'date' => '2026-09-04', 'clock_in' => '08:50:00', 'clock_out' => '17:00:00', 'total_hours' => 8.17, 'status' => 'Late', 'notes' => null],
        ]);
    }

    // [PAYROLL]
    private function seedPayrolls(): void
    {
        $this->seedIfEmpty('payrolls', [
            // Admin (salary 1200)
            ['user_id' => 1, 'month_year' => '2026-09', 'base_salary' => 1200.00, 'total_present_days' => 4, 'total_absent_days' => 0, 'total_ot_hours' => 3.50, 'ot_amount' => 30.29, 'bonus' => 0, 'deductions' => 2.00, 'net_salary' => 1228.29, 'payment_status' => 'Pending', 'payment_date' => null],
            // Waiter (salary 450)
            ['user_id' => 2, 'month_year' => '2026-09', 'base_salary' => 450.00, 'total_present_days' => 4, 'total_absent_days' => 0, 'total_ot_hours' => 3.66, 'ot_amount' => 11.88, 'bonus' => 0, 'deductions' => 2.00, 'net_salary' => 459.88, 'payment_status' => 'Pending', 'payment_date' => null],
            // Cashier (salary 400)
            ['user_id' => 3, 'month_year' => '2026-09', 'base_salary' => 400.00, 'total_present_days' => 2, 'total_absent_days' => 0, 'total_ot_hours' => 1.59, 'ot_amount' => 4.59, 'bonus' => 0, 'deductions' => 2.00, 'net_salary' => 402.59, 'payment_status' => 'Paid', 'payment_date' => '2026-09-04'],
        ]);
    }
}