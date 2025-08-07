# 🌟 HƯỚNG DẪN TÙY CHỈNH SẢN PHẨM NỔI BẬT

## 📖 Tổng quan
File này hướng dẫn bạn cách tùy chỉnh số lượng sản phẩm nổi bật và layout hiển thị trong trang chủ (`index.php`).

## 🔧 Cách 1: Thay đổi CSS Variables (Layout)

### Trong file `index.php`, tìm phần `:root` và thay đổi:

```css
:root {
    /* TÙY CHỈNH SẢN PHẨM NỔI BẬT */
    --products-per-row: 5; /* Số sản phẩm trên 1 hàng (3-6 tốt nhất) */
    --product-min-width: 280px; /* Kích thước tối thiểu */
    --product-gap: 2rem; /* Khoảng cách giữa sản phẩm */
}
```

## 🔢 Cách 2: Thay đổi Số lượng Sản phẩm

### Trong file `index.php`, tìm dòng 991 và thay đổi:

```php
// TÙY CHỈNH SỐ LƯỢNG SẢN PHẨM NỔI BẬT HIỂN THỊ
$limit = 20; // Thay số này (khuyến nghị: 12-30)
```

## 📊 Layout Phổ biến

### 🔹 Layout Compact (6 sản phẩm/hàng):
```css
--products-per-row: 6;
--product-gap: 1.5rem;
--product-min-width: 200px;
```
```php
$limit = 24; // Chia hết cho 6
```

### 🔹 Layout Standard (4 sản phẩm/hàng):
```css
--products-per-row: 4;
--product-gap: 2rem;
--product-min-width: 280px;
```
```php
$limit = 20; // Chia hết cho 4
```

### 🔹 Layout Premium (3 sản phẩm/hàng):
```css
--products-per-row: 3;
--product-gap: 3rem;
--product-min-width: 350px;
```
```php
$limit = 15; // Chia hết cho 3
```

### 🔹 Layout Gallery (5 sản phẩm/hàng):
```css
--products-per-row: 5;
--product-gap: 2rem;
--product-min-width: 250px;
```
```php
$limit = 25; // Chia hết cho 5
```

## 🔧 Cách 3: Sử dụng Class Preset

### Trong file `index.php`, tìm dòng:
```html
<div class="product-grid">
```

### Thay thế bằng:

#### 3 cột sản phẩm:
```html
<div class="product-grid layout-3">
```

#### 4 cột sản phẩm:
```html
<div class="product-grid layout-4">
```

#### 5 cột sản phẩm:
```html
<div class="product-grid layout-5">
```

#### 6 cột sản phẩm:
```html
<div class="product-grid layout-6">
```

#### Responsive tự động:
```html
<div class="product-grid layout-auto">
```

## 📱 Responsive Design

Hệ thống responsive tự động:
- **Desktop**: Số cột bạn chọn
- **Tablet (768px-1024px)**: 2 cột
- **Mobile (<768px)**: 1 cột

### Tùy chỉnh responsive:
```css
/* Desktop */
--products-per-row: 5;

/* Tablet */
@media (max-width: 1024px) {
    .product-grid {
        --products-per-row: 3;
    }
}

/* Mobile */
@media (max-width: 768px) {
    .product-grid {
        --products-per-row: 2;
    }
}
```

## 🎯 Combo Số lượng + Layout

### Cửa hàng nhỏ (12 sản phẩm, 3 cột):
```css
--products-per-row: 3;
--product-gap: 2.5rem;
```
```php
$limit = 12;
```

### Cửa hàng trung bình (20 sản phẩm, 4 cột):
```css
--products-per-row: 4;
--product-gap: 2rem;
```
```php
$limit = 20;
```

### Cửa hàng lớn (30 sản phẩm, 5 cột):
```css
--products-per-row: 5;
--product-gap: 1.5rem;
```
```php
$limit = 30;
```

### Siêu thị (36 sản phẩm, 6 cột):
```css
--products-per-row: 6;
--product-gap: 1rem;
```
```php
$limit = 36;
```

## 🔍 Tùy chỉnh Khoảng cách

### Khoảng cách gọn:
```css
--product-gap: 1rem;
```

### Khoảng cách tiêu chuẩn:
```css
--product-gap: 2rem;
```

### Khoảng cách rộng:
```css
--product-gap: 3rem;
```

### Khoảng cách khác nhau theo hướng:
```css
.product-grid {
    gap: 3rem 1.5rem; /* 3rem dọc, 1.5rem ngang */
}
```

## 🛠️ Tùy chỉnh Nâng cao

### Layout không đều:
```css
.product-grid {
    grid-template-columns: 2fr 1fr 1fr; /* Sản phẩm đầu lớn hơn */
}
```

### Sản phẩm đặc biệt (featured):
```css
.product-brick:first-child {
    grid-column: span 2; /* Chiếm 2 cột */
}
```

### Density cao (nhiều sản phẩm):
```css
--products-per-row: 8;
--product-gap: 0.5rem;
--product-min-width: 150px;
```

## 📈 Gợi ý theo Loại Website

### 🎮 **Cửa hàng đồ chơi**:
```css
--products-per-row: 5;
--product-gap: 2rem;
```
```php
$limit = 25;
```

### 👔 **Cửa hàng thời trang**:
```css
--products-per-row: 4;
--product-gap: 2.5rem;
```
```php
$limit = 16;
```

### 💍 **Cửa hàng luxury**:
```css
--products-per-row: 3;
--product-gap: 4rem;
```
```php
$limit = 12;
```

### 🛒 **Marketplace**:
```css
--products-per-row: 6;
--product-gap: 1rem;
```
```php
$limit = 36;
```

## ⚡ Quick Setup Guide

### Muốn nhiều sản phẩm hơn trên 1 hàng?
1. Tăng `--products-per-row` (tối đa 6)
2. Giảm `--product-gap` (xuống 1rem)
3. Tăng `$limit` thành bội số của số cột

### Muốn ít sản phẩm hơn nhưng lớn?
1. Giảm `--products-per-row` (xuống 3)
2. Tăng `--product-gap` (lên 3rem)
3. Giảm `$limit` thành bội số của số cột

### Muốn hiển thị nhiều sản phẩm hơn?
1. Tăng `$limit` (từ 20 → 30 hoặc 40)
2. Đảm bảo số chia hết cho số cột

## ⚠️ Lưu ý Quan trọng

1. **Chia hết**: Số sản phẩm nên chia hết cho số cột để đẹp
2. **Performance**: Quá nhiều sản phẩm có thể chậm
3. **Mobile**: Test trên điện thoại để đảm bảo đẹp
4. **Loading**: Nhiều hình ảnh = thời gian tải lâu hơn

## 🎯 Recommended Settings

### **Balanced (Khuyến nghị)**:
```css
--products-per-row: 4;
--product-gap: 2rem;
```
```php
$limit = 20;
```

### **Performance Focus**:
```css
--products-per-row: 3;
--product-gap: 2.5rem;
```
```php
$limit = 15;
```

### **Content Rich**:
```css
--products-per-row: 5;
--product-gap: 1.5rem;
```
```php
$limit = 25;
```

---

🎉 **Chúc bạn tùy chỉnh thành công!** 

Giờ bạn có thể kiểm soát hoàn toàn cách hiển thị sản phẩm nổi bật!
