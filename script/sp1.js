

// Hàm xử lý khi nhấn nút "Mua ngay"
function orderNow(mahang, tenhang, dongia, anh, soluongton) {
    if (!loggedIn) {
        alert("Bạn cần đăng nhập trước khi mua hàng!");
        window.location.href = 'Login_singup/login.php';
        return;
    }

    var quantity = document.getElementById('quantity').value;
    // Kiểm tra nếu sản phẩm tạm thời hết hàng (soluongton = 0)
    if (soluongton == 0) {
        showNotification("Sản phẩm tạm thời hết hàng!", false);
        return; // Không thực hiện tiếp nếu sản phẩm hết hàng
    }

    // Kiểm tra số lượng sản phẩm người dùng nhập có vượt quá số lượng tồn kho hay không
    if (quantity > soluongton) {
        showNotification("Số lượng không đủ, sản phẩm tồn kho chỉ còn " + soluongton + " sản phẩm.", false);
        return; // Không thực hiện tiếp nếu số lượng vượt quá tồn kho
    }


    // Tạo form ẩn
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'thanhtoan1.php';

    // Thêm các input ẩn vào form
    function addInput(name, value) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    addInput('mahang', mahang);
    addInput('tenhang', tenhang);
    addInput('dongia', dongia);
    addInput('anh', anh); // Thêm trường ảnh
    addInput('soluong', quantity);
    addInput('soluongton', soluongton);

    // Thêm form vào body và submit
    document.body.appendChild(form);
    form.submit();
}


// Hàm xử lý khi nhấn nút "Thêm vào giỏ hàng"
function addToCart(mahang, tenhang, dongia, anh, soluongton) {
    var quantity = document.getElementById('quantity').value;

    // Kiểm tra nếu sản phẩm tạm thời hết hàng (soluongton = 0)
    if (soluongton == 0) {
        showNotification("Sản phẩm tạm thời hết hàng!", false);
        return; // Không thực hiện tiếp nếu sản phẩm hết hàng
    }

    // Kiểm tra số lượng sản phẩm người dùng nhập có vượt quá số lượng tồn kho hay không
    if (quantity > soluongton) {
        showNotification("Số lượng không đủ, sản phẩm tồn kho chỉ còn " + soluongton + " sản phẩm.", false);
        return; // Không thực hiện tiếp nếu số lượng vượt quá tồn kho
    }

    // Lấy giỏ hàng từ localStorage
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let found = false;

    // Kiểm tra xem sản phẩm đã tồn tại trong giỏ hàng chưa
    cart.forEach(function(item) {
        if (item.mahang === mahang) {
            item.quantity += parseInt(quantity); // Tăng số lượng nếu sản phẩm đã có trong giỏ hàng
            found = true;
        }
    });

    // Nếu sản phẩm chưa tồn tại trong giỏ hàng thì thêm mới
    if (!found) {
        cart.push({
            mahang: mahang,
            name: tenhang,
            price: dongia,
            quantity: parseInt(quantity),
            image: anh,
            soluongton: soluongton
        });
    }

    // Cập nhật lại giỏ hàng trong localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Hiển thị thông báo thành công
    showNotification(" Thêm vào giỏ hàng thành công!", true);
}

// Hàm để hiển thị thông báo
function showNotification(message, success) {
    var notification = document.getElementById('notification');
    var notificationText = document.getElementById('notification-text');

    notificationText.textContent = message;
    notification.style.backgroundColor = success ? '#4CAF50' : '#f44336'; // Màu xanh cho thành công, đỏ cho lỗi
    notification.classList.remove('hidden');
    notification.classList.add('show');

    // Sau 3 giây, ẩn thông báo
    setTimeout(function() {
        notification.classList.remove('show');
        notification.classList.add('hidden');
    }, 2000);
}
// Lấy phần tử ảnh và container
const zoomImage = document.querySelector('.zoom-image');
let isZoomed = false; // Trạng thái ảnh có đang phóng to hay không

// Thêm sự kiện khi nhấn vào ảnh
zoomImage.addEventListener('click', function(e) {
    if (!isZoomed) {
        // Nếu chưa phóng to, thực hiện phóng to ảnh
        zoomImage.classList.add('zoomed');
        isZoomed = true; // Cập nhật trạng thái phóng to
        moveImage(e); // Di chuyển ảnh theo vị trí chuột khi phóng to
    } else {
        // Nếu đang phóng to, thu nhỏ lại ảnh về trạng thái ban đầu
        zoomImage.classList.remove('zoomed');
        isZoomed = false; // Cập nhật trạng thái không phóng to
        zoomImage.style.transformOrigin = 'center'; // Đặt lại vị trí trung tâm khi ảnh thu nhỏ
    }
});

// Hàm xử lý việc di chuyển ảnh theo vị trí chuột khi phóng to
function moveImage(e) {
    const containerRect = zoomImage.getBoundingClientRect();
    const x = e.clientX - containerRect.left;
    const y = e.clientY - containerRect.top;

    // Tính toán tỷ lệ phần trăm vị trí chuột so với ảnh
    const xPercent = (x / containerRect.width) * 100;
    const yPercent = (y / containerRect.height) * 100;

    // Cập nhật vị trí phóng to dựa trên tỷ lệ
    zoomImage.style.transformOrigin = `${xPercent}% ${yPercent}%`;
}

// Thêm sự kiện di chuột để di chuyển ảnh theo vị trí chuột (chỉ khi ảnh đã phóng to)
zoomImage.addEventListener('mousemove', function(e) {
    if (isZoomed) {
        moveImage(e);
    }
});