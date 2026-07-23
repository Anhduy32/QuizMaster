/**
 * ============================================================
 * PROFILE MANAGER LOGIC
 * Xử lý chuyển tab, popup xác nhận lưu và thông báo SweetAlert2
 * ============================================================
 */

const openTab = (evt, tabName) => {
    // 1. Ẩn tất cả nội dung tab
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
        tab.style.display = 'none';
    });
    
    // 2. Gỡ class active khỏi tất cả các nút tab
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // 3. Hiển thị tab được chọn (có thêm độ trễ nhỏ để CSS Transition hoạt động mượt)
    const activeTab = document.getElementById(tabName);
    if (activeTab) {
        activeTab.style.display = 'block';
        setTimeout(() => activeTab.classList.add('active'), 10);
    }
    
    // 4. Kích hoạt trạng thái active cho nút bấm tương ứng
    if (evt && evt.currentTarget) {
        evt.currentTarget.classList.add('active');
    } else {
        // Tìm nút tương ứng nếu hàm được gọi gián tiếp qua URL hoặc JS
        const targetBtn = document.querySelector(`.tab-btn[onclick*="${tabName}"]`);
        if (targetBtn) targetBtn.classList.add('active');
    }
};

// Khởi chạy toàn bộ sự kiện khi trang vừa tải xong
document.addEventListener('DOMContentLoaded', () => {
    // --- 1. XỬ LÝ CHUYỂN TAB MẶC ĐỊNH HOẶC THEO URL (?tab=...) ---
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    
    if (tab && document.getElementById(tab)) {
        openTab(null, tab);
    } else {
        const defaultBtn = document.querySelector('.tab-btn.active');
        if (defaultBtn) defaultBtn.click();
    }

    // --- 2. XỬ LÝ HIỂN THỊ KẾT QUẢ THÀNH CÔNG / LỖI TỪ PHP (SWEETALERT2) ---
    if (window.PROFILE_STATUS) {
        // Nếu PHP trả về cờ thành công
        if (window.PROFILE_STATUS.success) {
            Swal.fire({
                title: 'Thành công!',
                text: 'Hồ sơ của bạn đã được cập nhật.',
                icon: 'success',
                confirmButtonColor: 'var(--primary-teal)',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Giữ nguyên tab cập nhật sau khi load lại trang
                    window.location.href = window.location.pathname + '?tab=update';
                }
            });
        }

        // Nếu PHP trả về lỗi từ Database
        if (window.PROFILE_STATUS.error) {
            Swal.fire({
                title: 'Có lỗi xảy ra!',
                text: window.PROFILE_STATUS.error,
                icon: 'error',
                confirmButtonColor: '#e53e3e'
            });
        }
    }

    // --- 3. XỬ LÝ SỰ KIỆN XÁC NHẬN KHI BẤM NÚT LƯU THÔNG TIN ---
    const updateForm = document.getElementById('updateProfileForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            // Chặn hành vi submit mặc định để bật popup hỏi trước
            e.preventDefault(); 
            
            Swal.fire({
                title: 'Xác nhận lưu?',
                text: "Bạn có chắc chắn với các thông tin đã thay đổi?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--primary-teal)',
                cancelButtonColor: '#e53e3e',
                confirmButtonText: 'Có, lưu ngay!',
                cancelButtonText: 'Hủy bỏ'
            }).then((result) => {
                // Nếu người dùng đồng ý bấm "Có, lưu ngay!"
                if (result.isConfirmed) {
                    updateForm.submit(); // Cho phép form tiếp tục gửi dữ liệu lên server
                }
            });
        });
    }
});