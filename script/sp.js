// Hàm giảm số lượng
function decreaseQuantity() {
    var quantityInput = document.getElementById('quantity');
    var currentValue = parseInt(quantityInput.value);
    if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
    }
}

// Hàm tăng số lượng
function increaseQuantity() {
    var quantityInput = document.getElementById('quantity');
    var currentValue = parseInt(quantityInput.value);
    quantityInput.value = currentValue + 1;
}

// Hàm xử lý khi nhấn nút "Mua ngay"
function orderNow(mahang, tenhang, dongia, anh, soluongton) {
    var quantity = document.getElementById('quantity').value;

    // Kiểm tra nếu sản phẩm tạm thời hết hàng (soluongton = 0)
    if (soluongton == 0) {
        alert("Sản phẩm tạm thời hết hàng!");
        return; // Không thực hiện tiếp nếu sản phẩm hết hàng
    }

    // Điều hướng đến trang thanh toán nếu sản phẩm còn hàng
    window.location.href = 'thanhtoan.php?mahang=' + mahang + 
        '&tenhang=' + encodeURIComponent(tenhang) + 
        '&dongia=' + dongia + 
        '&soluong=' + quantity +
        '&soluongton=' + soluongton +  // Thêm số lượng tồn kho
        '&anh=' + encodeURIComponent(anh);  // Thêm thông tin ảnh
}

// Hàm xử lý khi nhấn nút "Thêm vào giỏ hàng"
function addToCart(mahang, tenhang, dongia, anh, soluongton) {
    var quantity = document.getElementById('quantity').value;

    // Kiểm tra nếu sản phẩm tạm thời hết hàng (soluongton = 0)
    if (soluongton == 0) {
        alert("Sản phẩm tạm thời hết hàng!");
        return; // Không thực hiện tiếp nếu sản phẩm hết hàng
    }

    // Kiểm tra số lượng sản phẩm người dùng nhập có vượt quá số lượng tồn kho hay không
    if (quantity > soluongton) {
        alert("Số lượng không đủ, sản phẩm tồn kho chỉ còn " + soluongton + " sản phẩm.");
        return; // Không thực hiện tiếp nếu số lượng vượt quá tồn kho
    }

    // Điều hướng đến trang giỏ hàng và truyền đầy đủ thông tin sản phẩm
    window.location.href = 'giohang1.php?mahang=' + mahang + 
        '&tenhang=' + encodeURIComponent(tenhang) + 
        '&dongia=' + dongia + 
        '&soluong=' + quantity + 
        '&soluongton=' + soluongton +  // Thêm số lượng tồn kho
        '&anh=' + encodeURIComponent(anh);
}