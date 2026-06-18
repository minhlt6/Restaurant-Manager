<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\DiningTable;
use App\Models\Employee;
use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Nhân viên ──────────────────────────────────────────────
        Employee::firstOrCreate(['username' => 'admin'], [
            'name'     => 'Nguyễn Văn Quản Lý',
            'gender'   => 'Nam',
            'address'  => '123 Lê Lợi, TP.HCM',
            'birthday' => '1985-03-15',
            'password' => Hash::make('password'),
            'role'     => 1,
        ]);

        $staffData = [
            ['name' => 'Trần Thị Mai', 'gender' => 'Nữ', 'username' => 'mai.tran', 'birthday' => '1999-07-20'],
            ['name' => 'Lê Văn Hùng', 'gender' => 'Nam', 'username' => 'hung.le', 'birthday' => '2000-11-05'],
            ['name' => 'Phạm Thị Lan', 'gender' => 'Nữ', 'username' => 'lan.pham', 'birthday' => '1998-04-12'],
            ['name' => 'Đỗ Minh Tuấn', 'gender' => 'Nam', 'username' => 'tuan.do', 'birthday' => '2001-09-25'],
        ];

        foreach ($staffData as $data) {
            Employee::firstOrCreate(['username' => $data['username']], array_merge($data, [
                'address'  => 'TP.HCM',
                'password' => Hash::make('password'),
                'role'     => 0,
            ]));
        }

        // ── Nhóm khách hàng ────────────────────────────────────────
        $retail  = CustomerGroup::firstOrCreate(['name' => 'Khách lẻ'],  ['description' => 'Khách hàng cá nhân, thanh toán theo giá gốc']);
        $vip     = CustomerGroup::firstOrCreate(['name' => 'Khách VIP'],  ['description' => 'Khách hàng thân thiết, ưu đãi đặc biệt']);
        $wholesale = CustomerGroup::firstOrCreate(['name' => 'Khách sỉ'], ['description' => 'Doanh nghiệp / đặt tiệc, tối thiểu 5 bàn, chiết khấu 10%']);

        // ── Khách hàng ─────────────────────────────────────────────
        $customers = [
            ['customer_group_id' => $retail->id,    'name' => 'Nguyễn Hoàng Nam',  'gender' => 'Nam', 'phone' => '0901234567', 'email' => 'nam.nguyen@email.com',  'address' => 'Q.1, TP.HCM'],
            ['customer_group_id' => $vip->id,       'name' => 'Trần Thị Hương',    'gender' => 'Nữ',  'phone' => '0912345678', 'email' => 'huong.tran@email.com',  'address' => 'Q.3, TP.HCM'],
            ['customer_group_id' => $wholesale->id, 'name' => 'Công ty ABC',        'gender' => 'Nam', 'phone' => '0923456789', 'email' => 'contact@abc.com.vn',    'address' => 'Q.Bình Thạnh'],
            ['customer_group_id' => $retail->id,    'name' => 'Lê Văn Bình',       'gender' => 'Nam', 'phone' => '0934567890', 'email' => 'binh.le@email.com',     'address' => 'Q.7, TP.HCM'],
            ['customer_group_id' => $vip->id,       'name' => 'Phạm Ngọc Ánh',    'gender' => 'Nữ',  'phone' => '0945678901', 'email' => 'anh.pham@email.com',    'address' => 'Q.Tân Bình'],
            ['customer_group_id' => $wholesale->id, 'name' => 'Tập đoàn XYZ',      'gender' => 'Nam', 'phone' => '0956789012', 'email' => 'info@xyz.vn',           'address' => 'Q.12, TP.HCM'],
        ];

        foreach ($customers as $c) {
            Customer::firstOrCreate(['phone' => $c['phone']], $c);
        }

        // ── Danh mục món ───────────────────────────────────────────
        $catMon   = Category::firstOrCreate(['name' => 'Món chính']);
        $catKhai  = Category::firstOrCreate(['name' => 'Khai vị']);
        $catTrang = Category::firstOrCreate(['name' => 'Tráng miệng']);
        $catNuoc  = Category::firstOrCreate(['name' => 'Đồ uống']);
        $catLau   = Category::firstOrCreate(['name' => 'Lẩu & Nướng']);

        // ── Món ăn ─────────────────────────────────────────────────
        $items = [
            // Món chính
            ['category_id' => $catMon->id,   'name' => 'Bò nướng lá lốt',     'price' => 85000,  'unit' => 'Phần', 'description' => 'Bò cuộn lá lốt nướng than hoa thơm ngon'],
            ['category_id' => $catMon->id,   'name' => 'Cơm chiên dương châu', 'price' => 65000,  'unit' => 'Phần', 'description' => 'Cơm chiên đặc biệt kiểu Dương Châu'],
            ['category_id' => $catMon->id,   'name' => 'Gà nướng mật ong',     'price' => 120000, 'unit' => 'Phần', 'description' => 'Gà ta nướng mật ong nguyên con 1kg'],
            ['category_id' => $catMon->id,   'name' => 'Heo quay giòn bì',     'price' => 95000,  'unit' => 'Phần', 'description' => 'Heo quay da giòn rụm truyền thống'],
            ['category_id' => $catMon->id,   'name' => 'Cá kho tộ',            'price' => 75000,  'unit' => 'Phần', 'description' => 'Cá lóc kho tộ đất thơm ngon'],
            // Khai vị
            ['category_id' => $catKhai->id,  'name' => 'Gỏi cuốn tôm thịt',   'price' => 45000,  'unit' => 'Phần', 'description' => '4 cuốn gỏi cuốn tươi kèm nước chấm'],
            ['category_id' => $catKhai->id,  'name' => 'Chả giò chiên giòn',   'price' => 50000,  'unit' => 'Phần', 'description' => '8 chiếc chả giò vàng giòn'],
            ['category_id' => $catKhai->id,  'name' => 'Súp bào ngư vi cá',    'price' => 75000,  'unit' => 'Phần', 'description' => 'Súp cao cấp bào ngư vi cá'],
            // Tráng miệng
            ['category_id' => $catTrang->id, 'name' => 'Chè ba màu',           'price' => 35000,  'unit' => 'Ly',   'description' => 'Chè ba màu truyền thống Nam Bộ'],
            ['category_id' => $catTrang->id, 'name' => 'Bánh flan caramel',    'price' => 30000,  'unit' => 'Cái',  'description' => 'Bánh flan mịn kèm caramel'],
            ['category_id' => $catTrang->id, 'name' => 'Trái cây tươi',        'price' => 55000,  'unit' => 'Đĩa',  'description' => 'Đĩa trái cây tươi theo mùa'],
            // Đồ uống
            ['category_id' => $catNuoc->id,  'name' => 'Nước ngọt có ga',      'price' => 20000,  'unit' => 'Lon',  'description' => 'Pepsi/7UP/Mirinda'],
            ['category_id' => $catNuoc->id,  'name' => 'Bia Tiger',             'price' => 30000,  'unit' => 'Lon',  'description' => 'Bia Tiger lon 330ml'],
            ['category_id' => $catNuoc->id,  'name' => 'Nước suối',             'price' => 15000,  'unit' => 'Chai', 'description' => 'Nước suối Aquafina 500ml'],
            ['category_id' => $catNuoc->id,  'name' => 'Trà đá',                'price' => 10000,  'unit' => 'Ly',   'description' => 'Trà đá miễn phí'],
            // Lẩu & nướng
            ['category_id' => $catLau->id,   'name' => 'Lẩu thái hải sản',     'price' => 280000, 'unit' => 'Nồi', 'description' => 'Lẩu thái chua cay đầy đủ hải sản (2-3 người)'],
            ['category_id' => $catLau->id,   'name' => 'Lẩu bò nhúng dấm',     'price' => 320000, 'unit' => 'Nồi', 'description' => 'Lẩu bò nhúng giấm truyền thống (3-4 người)'],
            ['category_id' => $catLau->id,   'name' => 'BBQ hải sản tổng hợp', 'price' => 250000, 'unit' => 'Set', 'description' => 'Set nướng hải sản tổng hợp (2 người)'],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(['name' => $item['name']], $item);
        }

        // ── Bàn ăn ─────────────────────────────────────────────────
        $tables = [
            ['name' => 'Bàn 01', 'capacity' => 2,  'status' => 0],
            ['name' => 'Bàn 02', 'capacity' => 4,  'status' => 0],
            ['name' => 'Bàn 03', 'capacity' => 4,  'status' => 0],
            ['name' => 'Bàn 04', 'capacity' => 6,  'status' => 0],
            ['name' => 'Bàn 05', 'capacity' => 6,  'status' => 0],
            ['name' => 'Bàn 06', 'capacity' => 8,  'status' => 0],
            ['name' => 'Bàn 07', 'capacity' => 8,  'status' => 0],
            ['name' => 'Bàn 08', 'capacity' => 10, 'status' => 0],
            ['name' => 'Bàn VIP 1', 'capacity' => 10, 'status' => 0],
            ['name' => 'Bàn VIP 2', 'capacity' => 12, 'status' => 0],
        ];

        foreach ($tables as $t) {
            DiningTable::firstOrCreate(['name' => $t['name']], $t);
        }
    }
}
