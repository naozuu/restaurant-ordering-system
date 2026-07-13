USE db_restoran;

UPDATE categories
SET nama_kategori = 'Food'
WHERE nama_kategori = 'Makanan';

UPDATE categories
SET nama_kategori = 'Drinks'
WHERE nama_kategori = 'Minuman';

-- Common sample data from the original project.
UPDATE menu
SET
    nama_menu = 'Special Fried Rice',
    deskripsi = 'Fried rice with egg and chicken'
WHERE nama_menu = 'Nasi Goreng Spesial';

UPDATE menu
SET
    nama_menu = 'Fried Noodles',
    deskripsi = 'Fried noodles with vegetables and chicken'
WHERE nama_menu = 'Mie Goreng';

UPDATE menu
SET
    nama_menu = 'Iced Sweet Tea',
    deskripsi = 'Sweet tea served with ice'
WHERE nama_menu = 'Es Teh Manis';

UPDATE menu
SET
    nama_menu = 'Orange Juice',
    deskripsi = 'Fresh orange juice'
WHERE nama_menu = 'Jus Jeruk';

UPDATE menu
SET
    nama_menu = 'Chocolate Pudding',
    deskripsi = 'Soft chocolate-flavored pudding'
WHERE nama_menu = 'Puding Cokelat';
