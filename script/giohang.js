// Hàm định dạng số tiền
function formatCurrency(value) {
    if (typeof value === 'undefined' || value === null) {
        return '0'; // Giá trị mặc định
    }
    return value.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Hàm tải giỏ hàng từ localStorage và hiển thị các sản phẩm
function loadCart() {
    const cartItemsDiv = document.getElementById('cart-items');
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Kiểm tra xem giỏ hàng có sản phẩm nào không
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = "<p>Giỏ hàng của bạn hiện tại trống.</p>";
    } else {
        cartItemsDiv.innerHTML = ""; // Xóa nội dung trước đó
        cart.forEach((item, index) => {
            // Tính tổng tiền cho từng sản phẩm
            const itemTotal = item.price * item.quantity;

            // Thêm sản phẩm vào phần hiển thị giỏ hàng với hình ảnh
            cartItemsDiv.innerHTML += `
                <div class="cart-container">
                    <div class="cart-item">
                        <input type="checkbox" class="select-item" onchange="updateTotal()">
                        <div class="cart-item-info">
                            <img src="${item.image}" alt="${item.name}">
                            <span class="cart-item-name">${item.name}</span>
                        </div>
                        <span class="cart-item-price">${formatCurrency(item.price)}</span>
                        <div class="cart-controls">
                            <button onclick="decrementQuantity(${index})">-</button>
                            <input type="text" value="${item.quantity}" min="1" id="quantity-${index}" onchange="updateQuantity(${index}, this.value)">
                            <button onclick="incrementQuantity(${index})">+</button>
                        </div>
                        <span class="cart-item-total">${formatCurrency(itemTotal)}</span>
                        <button onclick="removeItem(${index})" class="cart-remove-btn">Xóa</button>
                    </div>
                </div>
            `;
        });
    }

    // Cập nhật tổng tiền
    updateTotal();
}

// Hàm cập nhật tổng tiền
function updateTotal() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let total = 0;
    const checkboxes = document.querySelectorAll('.select-item');
    checkboxes.forEach((checkbox, index) => {
        if (checkbox.checked) {
            total += cart[index].price * cart[index].quantity;
        }
    });
    const cartTotalDiv = document.getElementById('cart-total');
    cartTotalDiv.innerHTML = `<p>Tổng tiền: ${formatCurrency(total)}</p>`;
}

// Hàm tăng số lượng
function incrementQuantity(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Kiểm tra nếu số lượng trong kho còn đủ
    if (cart[index].quantity < cart[index].soluongton) {
        cart[index].quantity++;
        localStorage.setItem('cart', JSON.stringify(cart));
        document.getElementById(`quantity-${index}`).value = cart[index].quantity;
        loadCart(); // Cập nhật lại giỏ hàng hiển thị
    } else {
        alert(`Số lượng sản phẩm trong kho chỉ còn ${cart[index].soluongton}`);
    }
}

// Hàm giảm số lượng
function decrementQuantity(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    document.getElementById(`quantity-${index}`).value = cart[index].quantity;
    loadCart(); // Cập nhật lại giỏ hàng hiển thị
}

// Hàm cập nhật số lượng khi người dùng nhập trực tiếp
function updateQuantity(index, newQuantity) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    newQuantity = parseInt(newQuantity, 10);

    // Kiểm tra nếu số lượng nhập vượt quá số lượng tồn kho
    if (newQuantity > cart[index].soluongton) {
        alert(`Số lượng sản phẩm trong kho chỉ còn ${cart[index].soluongton}`);
        newQuantity = cart[index].soluongton; // Đặt số lượng tối đa bằng số lượng tồn kho
        document.getElementById(`quantity-${index}`).value = newQuantity; // Cập nhật lại giá trị hiển thị
    }

    if (newQuantity > 0) {
        cart[index].quantity = newQuantity; // Cập nhật số lượng mới
    } else {
        cart.splice(index, 1); // Xóa sản phẩm nếu số lượng là 0
    }

    // Cập nhật lại localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart(); // Cập nhật lại giỏ hàng hiển thị
}

// Hàm xóa sản phẩm khỏi giỏ hàng
function removeItem(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart.splice(index, 1);

    // Cập nhật lại localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart(); // Cập nhật lại giỏ hàng hiển thị
}

// Hàm xóa tất cả những sản phẩm đã tích
function removeSelected() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const checkboxes = document.querySelectorAll('.select-item');
    for (let i = checkboxes.length - 1; i >= 0; i--) {
        if (checkboxes[i].checked) {
            cart.splice(i, 1);
        }
    }

    // Cập nhật lại localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart(); // Cập nhật lại giỏ hàng hiển thị
}

// Hàm chọn tất cả các sản phẩm
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.select-item');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateTotal();
}

// Gọi hàm loadCart khi trang được tải
window.onload = loadCart;

// Hàm kiểm tra đơn hàng
function checkout() {
    alert("Bạn cần đăng nhập trước khi thanh toán!");
}
