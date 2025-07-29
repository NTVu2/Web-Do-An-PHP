const carouselSlide = document.querySelector('.carousel-slide');
const carouselContainer = document.querySelector('.carousel-container');
const images = document.querySelectorAll('.carousel-slide img');
let counter = 0;

// Cập nhật chiều rộng của carousel-slide dựa trên số lượng ảnh
carouselSlide.style.width = `${images.length * 100}%`;

// Đảm bảo tất cả các ảnh chiếm cùng kích thước dựa trên số lượng ảnh
images.forEach(image => {
    image.style.width = `${100 / images.length}%`;
});

function updateSlidePosition() {
    const size = carouselContainer.offsetWidth;
    carouselSlide.style.transform = `translateX(${-counter * size}px)`;
}

function autoSlide() {
    counter++;
    if (counter >= images.length) {
        counter = 0;
    }
    updateSlidePosition();
}

function prevSlide() {
    counter--;
    if (counter < 0) {
        counter = images.length - 1;
    }
    updateSlidePosition();
}

function nextSlide() {
    counter++;
    if (counter >= images.length) {
        counter = 0;
    }
    updateSlidePosition();
}

// Tự động chuyển ảnh sau 4 giây
setInterval(autoSlide, 10000);

// Cập nhật vị trí khi kích thước cửa sổ thay đổi
window.addEventListener('resize', updateSlidePosition);