<?php
// Lấy tên trang hiện tại để highlight tab tương ứng
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
.tab-button.active {
    background-color: #858382 !important;
    color: white !important;
}
</style>

<header class="admin-header">
    <div class="header-container">
        <h1 class="admin-title">Bảng Điều Khiển Admin</h1>
        <ul class="user-actions">
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
                <li><a href="logout.php"><i class="fa fa-user"></i> <?php echo $_SESSION['admin_username']; ?></a></li>
            <?php else: ?>
                <li><a href="loginADMIN.php" class="dangnhap">Đăng Nhập</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<nav>
    <!-- Biểu tượng Ba Gạch để Mở Menu -->
    <div class="hamburger" onclick="toggleForms()"></div>
    <div class="tabs">
        <a href="trangchuadmin.php" class="tab-button <?php echo ($current_page == 'trangchuadmin.php') ? 'active' : ''; ?>">
            <i class="fa fa-home"></i> Trang chủ
        </a>   
        
        <!-- Sử dụng in_array để kiểm tra quyền trong mảng -->
        <?php if (in_array('sanpham', $_SESSION['quyen'])): ?>
            <a href="Nhap_SP.php" class="tab-button <?php echo ($current_page == 'Nhap_SP.php') ? 'active' : ''; ?>">
                <i class="fa fa-product-hunt"></i> Sản phẩm
            </a>
        <?php endif; ?>

        <?php if (in_array('danhmuc', $_SESSION['quyen'])): ?>
            <a href="Nhap_DM.php" class="tab-button <?php echo ($current_page == 'Nhap_DM.php') ? 'active' : ''; ?>">
                <i class="fa fa-list"></i> Danh mục
            </a>
        <?php endif; ?>

        <?php if (in_array('banner', $_SESSION['quyen'])): ?>
            <a href="Nhap_Banner.php" class="tab-button <?php echo ($current_page == 'Nhap_Banner.php') ? 'active' : ''; ?>">
                <i class="fa fa-image"></i> Banner
            </a>
        <?php endif; ?>

        <?php if (in_array('taikhoan', $_SESSION['quyen'])): ?>
            <a href="qltaikhoan.php" class="tab-button <?php echo ($current_page == 'qltaikhaon.php') ? 'active' : ''; ?>">
                <i class="fa fa-user"></i> Tài khoản
            </a>
        <?php endif; ?>

        <?php if (in_array('donhang', $_SESSION['quyen'])): ?>
            <a href="quanlydonhang.php" class="tab-button <?php echo ($current_page == 'quanlydonhang.php') ? 'active' : ''; ?>">
                <i class="fa fa-credit-card"></i> Đơn hàng
            </a>
        <?php endif; ?>

        <?php if (in_array('hoadon', $_SESSION['quyen'])): ?>
            <a href="xemhoadon.php" class="tab-button <?php echo ($current_page == 'xemhoadon.php') ? 'active' : ''; ?>">
                <i class="fa fa-clipboard-list"></i> Hóa đơn
            </a>
        <?php endif; ?>
        
        <?php if (in_array('nhanvien', $_SESSION['quyen'])): ?>
            <a href="qlnhanvien.php" class="tab-button <?php echo ($current_page == 'qlnhanvien.php') ? 'active' : ''; ?>">
                <i class="fa fa-user-tie"></i> Nhân viên
            </a>
        <?php endif; ?>

        <?php if (in_array('thongke', $_SESSION['quyen'])): ?>
            <a href="thongke.php" class="tab-button <?php echo ($current_page == 'thongke.php') ? 'active' : ''; ?>">
                <i class="fa fa-chart-bar"></i> Thống kê
            </a>
        <?php endif; ?>
    </div>
</nav>

<script>
function toggleForms() {
    const navFormContainer = document.querySelector('.nav-form-container');
    if (navFormContainer) {
        navFormContainer.classList.toggle('active');
    } else {
        console.error('Element .nav-form-container not found');
    }
}
</script> 