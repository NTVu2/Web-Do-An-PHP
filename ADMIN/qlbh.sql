-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2024 at 02:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qlbh`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `tendangnhap` varchar(50) NOT NULL,
  `matkhau` varchar(255) NOT NULL,
  `quyen` varchar(255) DEFAULT NULL,
  `ten_taikhoan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `tendangnhap`, `matkhau`, `quyen`, `ten_taikhoan`) VALUES
(1, 'admin', '123123', 'sanpham,danhmuc,banner,donhang,hoadon,taikhoan,nhanvien', 'vu'),
(2, 'nv1', 'nv1', 'sanpham,danhmuc', 'cuong'),
(3, 'nv2', 'nv2', 'banner', 'long'),
(5, 'nv3', 'nv3', 'hoadon,donhang', 'Hưng'),
(6, 'nv4', 'nv4', 'taikhoan', 'quốc');

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `id` int(11) NOT NULL,
  `banner_type` varchar(50) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banner`
--

INSERT INTO `banner` (`id`, `banner_type`, `image_path`) VALUES
(1, 'main_banner_1', 'img/banner/a1.jpg'),
(2, 'main_banner_2', 'img/banner/a2.png'),
(3, 'main_banner_3', 'img/banner/a3.jpg'),
(4, 'main_banner_4', 'img/banner/a4.jpg'),
(5, 'side_banner_1', 'img/banner/phu1.png'),
(6, 'side_banner_2', 'img/banner/phu2.png');

-- --------------------------------------------------------

--
-- Table structure for table `chitietdh`
--

CREATE TABLE `chitietdh` (
  `Madonhang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Mahang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Soluong` int(11) DEFAULT NULL,
  `Thanhtien` decimal(10,2) DEFAULT NULL,
  `id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitietdh`
--

INSERT INTO `chitietdh` (`Madonhang`, `Mahang`, `Soluong`, `Thanhtien`, `id`) VALUES
('HD67061460f044c', 'H001', 2, 9798000.00, NULL),
('HD67061460f044c', 'H005', 1, 690000.00, NULL),
('HD67061460f044c', 'H014', 1, 9500000.00, NULL),
('HD67067498727be', 'H014', 1, 9500000.00, NULL),
('HD6706845ecca7f', 'H002', 2, 1498000.00, NULL),
('HD67072b6598066', 'H008', 1, 4690000.00, NULL),
('HD67072b79b6f88', 'H014', 1, 9500000.00, NULL),
('HD67073216d2d55', 'H009', 1, 195000.00, 31),
('HD67073a95c311d', 'H002', 1, 749000.00, 31),
('HD67073aee86739', 'H015', 1, 165000.00, NULL),
('HD6707431bbcdbb', 'H013', 1, 14900000.00, NULL),
('HD6707432e5841d', 'H013', 1, 14900000.00, NULL),
('HD670743470bc77', 'H013', 1, 14900000.00, NULL),
('HD6707435457e53', 'H013', 1, 14900000.00, NULL),
('HD6707bc3998df6', 'H003', 9, 60210000.00, NULL),
('HD670871ab754ad', 'H017', 1, 44000.00, 36),
('HD670875680c22d', 'H004', 1, 100000.00, NULL),
('HD670875680c22d', 'H013', 1, 14900000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chitiethd`
--

CREATE TABLE `chitiethd` (
  `SohieuHD` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Mahang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Soluong` int(11) DEFAULT NULL,
  `Thanhtien` decimal(10,2) DEFAULT NULL,
  `PTthanhtoan` varchar(255) DEFAULT NULL,
  `id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitiethd`
--

INSERT INTO `chitiethd` (`SohieuHD`, `Mahang`, `Soluong`, `Thanhtien`, `PTthanhtoan`, `id`) VALUES
('HD67061460f044c', 'H001', 2, 9798000.00, 'cod', 36),
('HD67061460f044c', 'H005', 1, 690000.00, 'cod', 36),
('HD67061460f044c', 'H014', 1, 9500000.00, 'cod', 36),
('HD67067498727be', 'H014', 1, 9500000.00, 'cod', 36),
('HD6706845ecca7f', 'H002', 2, 1498000.00, 'cod', 36),
('HD67072b6598066', 'H008', 1, 4690000.00, 'cod', 31),
('HD67072b79b6f88', 'H014', 1, 9500000.00, 'cod', 31),
('HD67073216d2d55', 'H009', 1, 195000.00, 'momo', 31),
('HD67073a95c311d', 'H002', 1, 749000.00, 'momo', 31),
('HD67073aee86739', 'H015', 1, 165000.00, 'cod', 31),
('HD6707431bbcdbb', 'H013', 1, 14900000.00, 'cod', 31),
('HD6707432e5841d', 'H013', 1, 14900000.00, 'cod', 31),
('HD670743470bc77', 'H013', 1, 14900000.00, 'cod', 31),
('HD6707435457e53', 'H013', 1, 14900000.00, 'cod', 31),
('HD6707bc3998df6', 'H003', 9, 60210000.00, 'cod', 31),
('HD670871ab754ad', 'H017', 1, 44000.00, 'momo', 36),
('HD670875680c22d', 'H004', 1, 100000.00, 'cod', 36),
('HD670875680c22d', 'H013', 1, 14900000.00, 'cod', 36);

-- --------------------------------------------------------

--
-- Table structure for table `chitiet_sanpham`
--

CREATE TABLE `chitiet_sanpham` (
  `id` int(11) NOT NULL,
  `Mahang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Thongso` text DEFAULT NULL,
  `baohanh` varchar(255) DEFAULT NULL,
  `giagoc` decimal(10,2) DEFAULT NULL,
  `voicher` char(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donhang`
--

CREATE TABLE `donhang` (
  `Madonhang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `id` int(11) DEFAULT NULL,
  `NgayDH` date DEFAULT NULL,
  `Tongtien` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donhang`
--

INSERT INTO `donhang` (`Madonhang`, `id`, `NgayDH`, `Tongtien`) VALUES
('DH001', 1, '2024-09-01', 25000.00),
('DH002', 2, '2024-09-02', 12000.50),
('DH003', 3, '2024-09-03', 17500.75),
('DH004', 4, '2024-09-04', 9000.25),
('DH005', 5, '2024-09-05', 4500.00),
('DH006', 6, '2024-09-06', 3301.00),
('DH007', 7, '2024-09-07', 14500.00),
('DH008', 8, '2024-09-08', 2500.00),
('DH009', 9, '2024-09-09', 6100.00),
('DH010', 10, '2024-09-10', 8501.00);

-- --------------------------------------------------------

--
-- Table structure for table `hang`
--

CREATE TABLE `hang` (
  `Mahang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Tenhang` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Donvido` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Mota` text DEFAULT NULL,
  `Maloaihang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Soluongton` int(11) DEFAULT 0,
  `anh` varchar(50) DEFAULT NULL,
  `Dongia` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hoadon`
--

CREATE TABLE `hoadon` (
  `SohieuHD` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `id` int(11) DEFAULT NULL,
  `NgayBH` date DEFAULT NULL,
  `Tongtien` varchar(255) DEFAULT NULL,
  `Trangthai` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoadon`
--

INSERT INTO `hoadon` (`SohieuHD`, `id`, `NgayBH`, `Tongtien`, `Trangthai`) VALUES
('HD67061460f044c', 36, '2024-10-09', '19988000', 'Đang xử lý'),
('HD67067498727be', 36, '2024-10-09', '9500000', 'Giao hàng thành công'),
('HD6706845ecca7f', 36, '2024-10-09', '1498000', 'Giao hàng thành công'),
('HD67072b79b6f88', 31, '2024-10-10', '9500000', 'Giao hàng thành công'),
('HD67073a95c311d', 31, '2024-10-10', '749000', 'Giao hàng thành công'),
('HD67073aee86739', 31, '2024-10-10', '165000', 'Ngày giao hàng dự kiến: 14-10-2024'),
('HD6707431bbcdbb', 31, '2024-10-10', '14900000', 'Đang xử lý'),
('HD6707432e5841d', 31, '2024-10-10', '14900000', 'Đã hủy'),
('HD670743470bc77', 31, '2024-10-10', '14900000', 'Đang xử lý'),
('HD6707435457e53', 31, '2024-10-10', '14900000', 'Đang xử lý'),
('HD6707bc3998df6', 31, '2024-10-10', '60210000', 'Đang xử lý'),
('HD670871ab754ad', 36, '2024-10-11', '44000', 'Đang xử lý'),
('HD670875680c22d', 36, '2024-10-11', '15000000', 'Đang xử lý');

-- --------------------------------------------------------

--
-- Table structure for table `khach`
--

CREATE TABLE `khach` (
  `id` int(11) NOT NULL,
  `Tenkhach` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Diachi` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Dienthoai` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `khach`
--

INSERT INTO `khach` (`id`, `Tenkhach`, `Diachi`, `Dienthoai`) VALUES
(31, 'Moi Nè ', 'Hà tịnh', '543543'),
(36, 'Duy Vương', '101 , Nguyễn Viết Xuân , Thành Phố Vinh , Nghệ An', '838 264 139'),
(38, 'vuong', 'Anh Sơn ', '848296146'),
(39, 'cuongdz', 'chiu', '09999999'),
(40, 'cuongtest', 'do luong', '0987654321'),
(43, 'Hưng', 'Hà Tĩnh', '974354185');

-- --------------------------------------------------------

--
-- Table structure for table `loaihang`
--

CREATE TABLE `loaihang` (
  `Maloaihang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Tenloaihang` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Nhacungcap` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `anh` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loaihang`
--

INSERT INTO `loaihang` (`Maloaihang`, `Tenloaihang`, `Nhacungcap`, `anh`) VALUES
('1', 'Laptop Gaming', 'Intel', 'laptop.jpg'),
('2', 'Case Gaming ', 'NVIDIA', 'pc.png'),
('3', 'Tai Nghe Gaming', 'Kingston', 'tainghe.jpg'),
('4', 'Màn hình Gaming', 'ASUS', 'manhinh.png'),
('5', 'Chuột Gaming', 'Samsung', 'chuot.jpg'),
('6', 'Bàn Phím Gaming', 'Seagate', 'banphim.jpg'),
('7', 'Loa Gaming', 'Corsair', 'loa.jpeg'),
('8', 'Lot chuột Gaming ', 'APIC', 'lotchuot.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Tên` varchar(255) NOT NULL,
  `Ngày tạo tk` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `Tên`, `Ngày tạo tk`) VALUES
(31, '123', '123', '123', '2024-09-28'),
(36, '789', '789', 'Không tên ', '2024-10-03'),
(38, 'vuong', 'vuong', 'Đỗ Duy Vương', '2024-10-04'),
(39, 'cuong', '12345678', 'Thái Mạnh Cường', '2024-10-04'),
(40, 'teodz', '123', 'cuongtest', '2024-10-08'),
(41, 'test123', '123', 'hung', '2024-10-09'),
(43, 'hung', 'hung', 'Hưng', '2024-10-09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tendangnhap` (`tendangnhap`);

--
-- Indexes for table `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `banner_type` (`banner_type`);

--
-- Indexes for table `chitietdh`
--
ALTER TABLE `chitietdh`
  ADD PRIMARY KEY (`Madonhang`,`Mahang`);

--
-- Indexes for table `chitiethd`
--
ALTER TABLE `chitiethd`
  ADD PRIMARY KEY (`SohieuHD`,`Mahang`);

--
-- Indexes for table `chitiet_sanpham`
--
ALTER TABLE `chitiet_sanpham`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Mahang` (`Mahang`);

--
-- Indexes for table `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`Madonhang`);

--
-- Indexes for table `hang`
--
ALTER TABLE `hang`
  ADD PRIMARY KEY (`Mahang`);

--
-- Indexes for table `hoadon`
--
ALTER TABLE `hoadon`
  ADD PRIMARY KEY (`SohieuHD`),
  ADD KEY `fk_hoadon_users` (`id`);

--
-- Indexes for table `khach`
--
ALTER TABLE `khach`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loaihang`
--
ALTER TABLE `loaihang`
  ADD PRIMARY KEY (`Maloaihang`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `chitiet_sanpham`
--
ALTER TABLE `chitiet_sanpham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitiet_sanpham`
--
ALTER TABLE `chitiet_sanpham`
  ADD CONSTRAINT `chitiet_sanpham_ibfk_1` FOREIGN KEY (`Mahang`) REFERENCES `hang` (`Mahang`) ON DELETE CASCADE;

--
-- Constraints for table `hoadon`
--
ALTER TABLE `hoadon`
  ADD CONSTRAINT `fk_hoadon_users` FOREIGN KEY (`id`) REFERENCES `users` (`id`);

--
-- Constraints for table `khach`
--
ALTER TABLE `khach`
  ADD CONSTRAINT `fk_khach_users` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
