window.tailwind.config = {
    darkMode: ['class'],
    theme: {
        extend: {
            colors: {
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))'
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))'
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))'
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))'
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))'
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))'
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))'
                },
            },
        }
    }
};

// Khởi tạo giỏ hàng trong localStorage
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Hàm thêm sản phẩm vào giỏ hàng cùng với URL ảnh
function addToCart(productId, productName, productPrice, productImage) {
    const product = {
        id: productId,
        name: productName,
        price: productPrice,
        image: productImage, // Đảm bảo URL ảnh được truyền vào đúng
        quantity: 1
    };

    const productIndex = cart.findIndex(item => item.id === productId);
    if (productIndex > -1) {
        cart[productIndex].quantity += 1;
    } else {
        cart.push(product);
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    alert(productName + ' đã được thêm vào giỏ hàng!');
}

// Hàm để tải giỏ hàng vào trang giỏ hàng (giohang.html)
function loadCart() {
    const cartItemsDiv = document.getElementById('cart-items');
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    let total = 0;

    if (cart.length === 0) {
        cartItemsDiv.innerHTML = "<p>Giỏ hàng của bạn hiện tại trống.</p>";
    } else {
        cartItemsDiv.innerHTML = ""; // Xóa nội dung cũ
        cart.forEach(item => {
            // Thêm thẻ <img> để hiển thị ảnh sản phẩm
            cartItemsDiv.innerHTML += `
                <div class="flex items-center space-x-4">
                    <img src="${item.image}" alt="${item.name}" style="width: 100px; height: 100px;"> 
                    <div>
                        <h3>${item.name} - ${item.quantity} x ${item.price}₫</h3>
                    </div>
                </div>
            `;
            total += item.price * item.quantity;
        });
    }

    document.getElementById('cart-total').innerText = `Tổng tiền: ${total.toFixed(2)}₫`;
}

function orderNow(productId, productName, productPrice, productImage) {
    const product = {
        id: productId,
        name: productName,
        price: productPrice,
        image: productImage,
        quantity: 1
    };

    // Lưu sản phẩm vào giỏ hàng
    cart = [product];
    localStorage.setItem('cart', JSON.stringify(cart));

    // Điều hướng người dùng đến trang giỏ hàng hoặc thanh toán
    window.location.href = 'checkout.html'; // Bạn có thể điều hướng đến trang thanh toán
}
