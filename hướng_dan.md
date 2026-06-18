1. Nền tảng và Kiến trúc hệ thống
Công nghệ sử dụng: Ứng dụng phải được xây dựng bằng ngôn ngữ PHP, sử dụng Framework Laravel và tuân thủ nghiêm ngặt mô hình kiến trúc MVC (Model - View - Controller)
.
Cơ sở dữ liệu (Database): Cần viết các tệp Migration để khởi tạo cấu trúc 8 bảng và dùng Seeder (kết hợp Faker) sinh dữ liệu mẫu
,
. 8 bảng bao gồm: employees (nhân viên), customer_groups (nhóm khách), customers (khách hàng), categories (nhóm món), items (món ăn), dining_tables (bàn ăn), orders (hóa đơn chung), order_details (chi tiết gọi món).
Bảo mật Form: Mọi request dùng phương thức POST bắt buộc phải có mã chống giả mạo CSRF (dùng @csrf hoặc _token) và toàn bộ dữ liệu từ form phải được Validate (kiểm tra hợp lệ) tại Controller trước khi xử lý
,
,
.
2. Nghiệp vụ Xác thực và Phân quyền (Authentication & Authorization)
Đăng nhập duy nhất: Chỉ sử dụng duy nhất 1 form đăng nhập cho toàn bộ hệ thống
. Sau khi đăng nhập, trạng thái của người dùng phải được lưu vào Session
,
.
Phân quyền theo Role: Dựa vào cột role trong bảng employees (1 = Quản lý, 0 = Nhân viên) để phân quyền
.
Phân quyền Giao diện (View): Sử dụng cấu trúc điều khiển @if của Blade Template. Quản lý sẽ nhìn thấy toàn bộ các nút Thêm/Sửa/Xóa. Nhân viên phục vụ chỉ nhìn thấy giao diện quản lý khách hàng và form đặt món
,
.
Bảo vệ đường dẫn (Middleware): Viết Middleware hoạt động như "bức tường lửa" để chặn nhân viên tự ý gõ URL truy cập vào các chức năng Thêm/Sửa/Xóa của quản lý
.
3. Nghiệp vụ Quản lý Dữ liệu danh mục (Master Data)
Quản lý Nhân sự: Tính năng thêm, sửa, xóa hồ sơ nhân viên và tạo tài khoản (cấp username, mã hóa password, cấp role)
.
Quản lý Thực đơn (AJAX): Xây dựng các tính năng Thêm/Sửa/Xóa danh mục (categories) và món ăn (items) thông qua các form dạng cửa sổ hiển thị (Modal)
. Bắt buộc phải sử dụng RESTful API và AJAX để dữ liệu được cập nhật ngay lập tức mà không bị tải lại (refresh) trang
,
,
.
Quản lý Khách hàng: Nhân viên lưu hồ sơ khách tới ăn và phân loại khách hàng bằng SelectBox để chọn nhóm khách (customer_groups như khách lẻ, khách sỉ, khách VIP)
.
4. Nghiệp vụ Lõi: Đặt bàn, Gọi món và Thanh toán (Transaction Data)
Mở bàn và Tạo hóa đơn: Khi khách vào, nhân viên chọn bàn ăn (dining_tables). Hệ thống tự động tạo một hóa đơn mới (orders), cập nhật trạng thái bàn thành "Đang phục vụ", tự động gán giờ vào (time_in), lưu lại thông tin nhân viên phục vụ và khách hàng (nếu có).
Gọi món: Sử dụng AJAX để nhân viên chọn món, nhập số lượng, sau đó dữ liệu dạng mảng (Array) được đẩy lên máy chủ và lưu thành nhiều dòng chi tiết vào bảng order_details
,
.
Luồng xử lý Khách sỉ (Doanh nghiệp/Đặt tiệc): Nếu khách thuộc nhóm "khách sỉ", hệ thống sẽ Validate để bắt buộc kiểm tra số lượng tối thiểu (ví dụ yêu cầu trên 5 bàn)
. Bắt buộc xuất hiện form yêu cầu nhập số tiền đặt cọc (deposit) trước khi gọi món
.
Luồng xử lý Khách lẻ: Gọi món tự do không yêu cầu số lượng tối thiểu và dùng bữa xong mới thanh toán 1 lần (không cần đặt cọc)
,
.
Tính tiền và Trả bàn: Khi thanh toán, hệ thống thực hiện rẽ nhánh (if...else) để tính chiết khấu (giảm giá %) nếu là nhóm khách sỉ, hoặc tính giá gốc nếu là khách lẻ
,
. Cập nhật giờ ra (time_out), cập nhật tổng tiền (total_price) vào bảng hóa đơn, đổi trạng thái thanh toán và giải phóng bàn trở lại trạng thái "Trống".