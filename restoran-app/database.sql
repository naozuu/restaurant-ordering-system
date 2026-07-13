CREATE DATABASE IF NOT EXISTS db_restoran
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE db_restoran;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    nama_menu VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(12,2) NOT NULL,
    gambar VARCHAR(255),
    status ENUM('tersedia', 'habis') NOT NULL DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_menu_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS restaurant_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_date DATE NOT NULL,
    opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    opened_by INT NULL,
    closed_by INT NULL,
    opening_note VARCHAR(255) NULL,
    closing_note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_restaurant_sessions_date (business_date),
    INDEX idx_restaurant_sessions_status (status),
    CONSTRAINT fk_session_opened_by
        FOREIGN KEY (opened_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_session_closed_by
        FOREIGN KEY (closed_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NULL,
    kode_pesanan VARCHAR(30) NOT NULL UNIQUE,
    nama_pelanggan VARCHAR(100) NOT NULL,
    nomor_meja VARCHAR(20) NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    status_pesanan ENUM(
        'menunggu',
        'diproses',
        'siap',
        'selesai',
        'dibatalkan'
    ) NOT NULL DEFAULT 'menunggu',
    status_pembayaran ENUM(
        'belum_bayar',
        'sudah_bayar'
    ) NOT NULL DEFAULT 'belum_bayar',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orders_session_id (session_id),
    CONSTRAINT fk_orders_session
        FOREIGN KEY (session_id) REFERENCES restaurant_sessions(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_detail_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_menu
        FOREIGN KEY (menu_id) REFERENCES menu(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO categories (nama_kategori)
SELECT 'Food'
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE nama_kategori = 'Food'
);

INSERT INTO categories (nama_kategori)
SELECT 'Drinks'
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE nama_kategori = 'Drinks'
);

INSERT INTO categories (nama_kategori)
SELECT 'Dessert'
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE nama_kategori = 'Dessert'
);
