# Dokumentasi API - Inventaris Barang

Dokumen ini berisi panduan untuk Frontend Developer dalam melakukan integrasi dengan Backend API Inventaris Barang.

**Base URL (Ubah IP sesuai dengan IP Laptop Backend saat ini):**
`http://192.168.1.71/inventaris-barang-BE/public/api`

---

## 1. Otentikasi (Authentication)

### 1.1. Login
Digunakan untuk mendapatkan token akses (JWT) yang wajib disertakan pada permintaan API selanjutnya.

- **URL:** `/login`
- **Method:** `POST`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`

**Request Body:**
```json
{
    "email": "petugas@stok.ku",
    "password": "password123"
}
```

**Response Sukses (200 OK):**
```json
{
    "message": "Login berhasil",
    "access_token": "eyJ0eXAi... (token JWT panjang)",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
        "id": 2,
        "name": "Petugas Gudang",
        "email": "petugas@stok.ku",
        "role": "petugas"
    }
}
```

**Response Gagal (401 Unauthorized - Kredensial Salah):**
```json
{
    "message": "Kredensial tidak valid"
}
```

---

### 1.2. Logout
Digunakan untuk menghancurkan token (invalidasi JWT) agar tidak bisa digunakan lagi.

- **URL:** `/logout`
- **Method:** `POST`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <isi_dengan_access_token>`

**Response Sukses (200 OK):**
```json
{
    "message": "Logout berhasil"
}
```

---

## Aturan Umum
1. Setiap endpoint selain `/login` **WAJIB** menyertakan header `Authorization: Bearer <token_jwt>`.
2. Jika token kadaluarsa atau tidak valid, server akan mengembalikan HTTP Status `401 Unauthorized`.
3. Format pengiriman dan penerimaan data selalu menggunakan standar `JSON`.

---

## 2. Dashboard

### 2.1. Get Data Dashboard
Mengambil semua data ringkasan, grafik 30 hari, barang stok menipis, dan aktivitas terbaru. Sangat cocok untuk digambar di halaman utama Dashboard.

- **URL:** `/dashboard`
- **Method:** `GET`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Bearer <isi_dengan_access_token>`

**Response Sukses (200 OK):**
```json
{
    "message": "Berhasil mengambil data dashboard",
    "data": {
        "summary": {
            "total_jenis_barang": 4,
            "total_stok_keseluruhan": 180,
            "barang_masuk_bulan_ini": 250,
            "barang_keluar_bulan_ini": 30,
            "jumlah_barang_stok_minimum": 1
        },
        "mutasi": {
            "masuk": 10,
            "keluar": 5,
            "total_mutasi": 15
        },
        "stok_menipis": [
            {
                "id": 1,
                "nama_barang": "Tinta Printer",
                "stok": 5,
                "stok_minimum": 10,
                "satuan": "Botol"
            }
        ],
        "aktivitas_terbaru": [
            {
                "id": 15,
                "tanggal": "20/06/26",
                "jenis": "Keluar",
                "nama_barang": "Kertas A4",
                "jumlah": 5,
                "oleh": "Petugas Gudang"
            }
        ],
        "grafik_30_hari": {
            "labels": ["20 May", "21 May", "..."],
            "data_masuk": [0, 15, "..."],
            "data_keluar": [5, 0, "..."]
        }
    }
}
```

---

## 3. Data Barang (CRUD)
Manajemen daftar inventaris barang. Seluruh endpoint wajib menyertakan token `Authorization: Bearer <token_jwt>`.

### 3.1. Daftar Barang (GET)
Mengambil daftar barang. Anda bisa mengirim parameter untuk melakukan pencarian dan filter sesuai form di UI.

- **URL:** `/barangs`
- **Method:** `GET`
- **Query Params (Opsional):**
  - `?search=kertas` (Pencarian teks kode/nama)
  - `?kategori=ATK` (Filter Kategori)
  - `?satuan=Rim` (Filter Satuan)

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil data barang",
    "data": [
        {
            "id": 1,
            "kode_barang": "BRG-01",
            "nama_barang": "Kertas HVS",
            "kategori": "ATK",
            "stok": 350,
            "stok_minimum": 50,
            "satuan": "Rim",
            "harga_satuan": 50000
        }
    ]
}
```

### 3.2. Tambah Barang Baru (POST)
- **URL:** `/barangs`
- **Method:** `POST`

**Request Body:**
```json
{
    "kode_barang": "BRG-06",
    "nama_barang": "Laptop Bekas",
    "kategori": "Elektronik",
    "stok": 10,
    "stok_minimum": 2,
    "satuan": "Unit",
    "harga_satuan": 3000000
}
```

**Response Sukses (201 Created):**
Mengembalikan data yang baru saja dimasukkan.

### 3.3. Detail Barang (GET)
Digunakan saat klik tombol Edit untuk memuat data ke dalam form modal.
- **URL:** `/barangs/{id}` (Contoh: `/barangs/1`)
- **Method:** `GET`

### 3.4. Update/Edit Barang (PUT)
Digunakan untuk menyimpan perubahan dari Modal Form Edit.
- **URL:** `/barangs/{id}` (Contoh: `/barangs/1`)
- **Method:** `PUT`

**Request Body:** Sama persis seperti form Tambah Barang (POST).

### 3.5. Hapus Barang (DELETE)
Digunakan saat klik tombol tempat sampah.
- **URL:** `/barangs/{id}` (Contoh: `/barangs/1`)
- **Method:** `DELETE`

**Response Sukses:**
```json
{
    "message": "Berhasil menghapus barang"
}
```

---

## 4. Laporan Stok
Endpoint khusus untuk halaman Laporan Stok yang menampilkan ringkasan berserta status stok tiap barang. Seluruh endpoint wajib menyertakan token `Authorization: Bearer <token_jwt>`.

### 4.1. Get Data Laporan Stok (GET)
Mengambil ringkasan beserta tabel laporan. Mendukung pencarian berdasar kategori dan ID barang (dropdown pilih barang).

- **URL:** `/laporan/stok`
- **Method:** `GET`
- **Query Params (Opsional - Bisa dipakai untuk form Filter):**
  - `?kategori=ATK` (Filter Kategori - Pencarian teks)
  - `?nama_barang=Kertas` (Filter Nama Barang - Pencarian teks)
  - `?kode_barang=BRG` (Filter Kode Barang - Pencarian teks)
  - `?filter_stok=>10` (Untuk radio button Tampilkan Stok. Value yang didukung: `>10` atau `=0`)
  - `?barang_id=1` (Filter Barang Spesifik via ID)

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil data laporan stok",
    "data": {
        "summary": {
            "total_barang": 1,
            "total_stok": 350,
            "barang_masuk": 200,
            "barang_keluar": 130
        },
        "laporan": [
            {
                "id": 1,
                "kode_barang": "BRG-01",
                "nama_barang": "Kertas HVS",
                "kategori": "ATK",
                "satuan": "Rim",
                "stok_tersedia": 350,
                "stok_minimum": 50,
                "harga_satuan": 50000,
                "harga_stok": 17500000,
                "status": "Aman"
            }
        ]
    }
### 4.2. Get Laporan Barang Masuk (GET)
Endpoint khusus untuk halaman Laporan Barang Masuk. Menampilkan transaksi yang hanya berjenis 'masuk'.

- **URL:** `/laporan/barang-masuk`
- **Method:** `GET`
- **Query Params (Opsional):**
  - `?start_date=YYYY-MM-DD`
  - `?end_date=YYYY-MM-DD`
  - `?supplier=P.T Sumber` (Pencarian nama supplier)
  - `?barang=HVS` (Pencarian nama atau kode barang)

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil laporan barang masuk",
    "data": {
        "summary": {
            "total_barang": 2,
            "total_stok": 233,
            "barang_masuk": 250,
            "barang_keluar": 17
        },
        "laporan": [
            {
                "id": 1,
                "tanggal_masuk": "20/06/2026",
                "no_referensi": "IN/20260620/001",
                "supplier": "CV. Sukses Jaya",
                "barang": "Kertas HVS",
                "satuan": "Rim",
                "jumlah": 100,
                "harga_satuan": 50000,
                "total": 5000000,
                "keterangan": "Pembelian Awal"
            }
        ]
    }
}
```

### 4.3. Get Laporan Barang Keluar (GET)
Endpoint khusus untuk halaman Laporan Barang Keluar. Menampilkan transaksi yang hanya berjenis 'keluar'.

- **URL:** `/laporan/barang-keluar`
- **Method:** `GET`
- **Query Params (Opsional):**
  - `?start_date=2024-05-01`
  - `?end_date=2024-05-20`
  - `?satuan=Rim` (Filter Satuan)
  - `?barang=Kertas` (Filter Nama/Kode Barang)

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil data laporan barang keluar",
    "data": {
        "summary": {
            "total_barang": 2,
            "total_stok": 233,
            "barang_masuk": 250,
            "barang_keluar": 17
        },
        "laporan": [
            {
                "id": 1,
                "tanggal_keluar": "22/09/2024",
                "no_referensi": "OUT/00/88",
                "tujuan": "HRD",
                "barang": "Kertas A4",
                "kode_barang": "BRG-00",
                "satuan": "Rim",
                "jumlah": 10,
                "harga_satuan": 50000,
                "total": 500000,
                "keterangan": "Keperluan Cetak"
            }
        ]
    }
}
```

---

## 5. Mutasi Barang (Transaksi)
Mencatat dan melihat riwayat keluar-masuk barang. Seluruh endpoint wajib menyertakan token `Authorization: Bearer <token_jwt>`.

### 5.1. Riwayat Mutasi (GET)
Menampilkan daftar mutasi. Mendukung filter rentang tanggal, nama barang, dan jenis transaksi.

- **URL:** `/mutasi`
- **Method:** `GET`
- **Query Params (Opsional):**
  - `?start_date=2026-09-01`
  - `?end_date=2026-09-30`
  - `?barang=Kertas` (Nama / Kode)
  - `?jenis=masuk` (Atau 'keluar')

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil data mutasi",
    "data": {
        "summary": {
            "total_masuk": 150,
            "total_keluar": 50,
            "total_mutasi": 15
        },
        "mutasi": [
            {
                "id": 1,
                "tanggal": "22/09/26",
                "no_referensi": "IN/20260922/223",
                "nama_barang": "Kertas HVS",
                "kode_barang": "BRG-01",
                "jenis": "Masuk",
                "jumlah": 50,
                "satuan": "Rim",
                "keterangan": "Pembelian CV. A",
                "petugas": "Petugas Gudang"
            }
        ]
    }
}
```

### 5.2. Tambah Mutasi (POST)
Mencatat barang masuk atau keluar. **Catatan Penting:** Endpoint ini otomatis akan menambah atau mengurangi tabel Stok Barang!

- **URL:** `/mutasi`
- **Method:** `POST`

**Request Body:**
```json
{
    "barang_id": 1,
    "jenis": "masuk", 
    "jumlah": 50,
    "tanggal": "2026-09-22",
    "keterangan": "Pembelian CV. A",
    "no_referensi": "IN/000/223" 
}
```
*(Catatan: `no_referensi` sifatnya opsional. Jika Anda tidak mengirimnya dari Frontend, Backend akan membuatkan nomor otomatis untuk Anda).*

**Response Sukses (201 Created):**
Mengembalikan data yang baru saja dimasukkan.

**Response Gagal - Stok Kurang (422 Unprocessable Entity):**
Muncul jika jenis "keluar" namun stok tidak cukup.
```json
{
    "message": "Gagal menyimpan mutasi",
    "errors": {
        "jumlah": ["Stok barang tidak mencukupi untuk dikeluarkan."]
    }
}
```

---

## 6. Master Kategori Barang (CRUD)
Mengelola daftar kategori barang (seperti Alat Tulis Kantor, Elektronik). Seluruh endpoint wajib menyertakan token `Authorization: Bearer <token_jwt>`.

### 6.1. Daftar Kategori (GET)
- **URL:** `/kategoris`
- **Method:** `GET`
- **Query Params:** `?search=Alat` (Opsional)

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil daftar kategori",
    "data": [
        {
            "id": 1,
            "nama_kategori": "Alat Tulis Kantor",
            "jumlah": 45
        }
    ]
}
```

### 6.2. Tambah Kategori (POST)
- **URL:** `/kategoris`
- **Method:** `POST`

**Request Body:**
```json
{
    "nama_kategori": "Perabotan",
    "jumlah": 10
}
```

### 6.3. Edit Kategori (PUT)
- **URL:** `/kategoris/{id}`
- **Method:** `PUT`

**Request Body:**
```json
{
    "nama_kategori": "Perabotan Kayu",
    "jumlah": 12
}
```

### 6.4. Hapus Kategori (DELETE)
- **URL:** `/kategoris/{id}`
- **Method:** `DELETE`

**Response Sukses:**
```json
{
    "message": "Berhasil menghapus kategori"
}
```

---

## 7. Master Satuan Barang (CRUD)
Mengelola daftar satuan barang (seperti Pcs, Dus, Rim). Seluruh endpoint wajib menyertakan token `Authorization: Bearer <token_jwt>`.

### 7.1. Daftar Satuan (GET)
- **URL:** `/satuans`
- **Method:** `GET`
- **Query Params:** `?search=Pcs` (Opsional, mencari berdasarkan nama atau kode)

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil daftar satuan",
    "data": [
        {
            "id": 1,
            "nama_satuan": "Pcs",
            "kode_satuan": "PCS",
            "keterangan": "Pieces"
        }
    ]
}
```

### 7.2. Tambah Satuan (POST)
- **URL:** `/satuans`
- **Method:** `POST`

**Request Body:**
```json
{
    "nama_satuan": "Box",
    "kode_satuan": "BOX",
    "keterangan": "Satu box isi 10"
}
```

### 7.3. Edit Satuan (PUT)
- **URL:** `/satuans/{id}`
- **Method:** `PUT`

**Request Body:**
```json
{
    "nama_satuan": "Kotak",
    "kode_satuan": "KTK",
    "keterangan": "Kotak kecil"
}
```

### 7.4. Hapus Satuan (DELETE)
- **URL:** `/satuans/{id}`
- **Method:** `DELETE`

**Response Sukses:**
```json
{
    "message": "Berhasil menghapus satuan"
}
```

---

## 8. Master Role (Hak Akses)
Mengelola daftar role pengguna. Seluruh endpoint wajib menyertakan token `Authorization: Bearer <token_jwt>`.

### 8.1. Daftar Role (GET)
- **URL:** `/roles`
- **Method:** `GET`

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil daftar role",
    "data": [
        {
            "id": 1,
            "nama_role": "Administrator",
            "deskripsi": "Memiliki akses penuh ke semua fitur."
        }
    ]
}
```

### 8.2. Tambah Role (POST)
- **URL:** `/roles`
- **Method:** `POST`

**Request Body:**
```json
{
    "nama_role": "Manager",
    "deskripsi": "Melihat laporan saja"
}
```

---

## 9. Master User
Mengelola daftar pengguna aplikasi. Seluruh endpoint wajib menyertakan token `Authorization: Bearer <token_jwt>`.

### 9.1. Daftar User (GET)
- **URL:** `/users`
- **Method:** `GET`
- **Query Params:** `?search=Admin` (Opsional, mencari berdasarkan nama, email, atau nama_role)

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil daftar user",
    "data": [
        {
            "id": 1,
            "name": "Administrator",
            "email": "admin@stok.ku",
            "role": "Administrator",
            "role_id": 1,
            "status": "Aktif"
        }
    ]
}
```

### 9.2. Tambah User (POST)
- **URL:** `/users`
- **Method:** `POST`

**Request Body:**
```json
{
    "name": "Budi",
    "email": "budi@stok.ku",
    "password": "password123",
    "role_id": 2,
    "status": "Aktif"
}
```

### 9.3. Edit User (PUT)
- **URL:** `/users/{id}`
- **Method:** `PUT`

**Request Body:**
```json
{
    "name": "Budi Santoso",
    "email": "budi@stok.ku",
    "password": "newpassword123",
    "role_id": 2,
    "status": "Tidak Aktif"
}
```

*(Catatan: `password` opsional saat Edit)*

### 9.4. Hapus User (DELETE)
- **URL:** `/users/{id}`
- **Method:** `DELETE`

**Response Sukses:**
```json
{
    "message": "Berhasil menghapus user"
}
```

---

## 10. Master Supplier (CRUD)
Mengelola daftar supplier (pemasok barang). Seluruh endpoint wajib menyertakan token `Authorization: Bearer <token_jwt>`.

### 10.1. Daftar Supplier (GET)
- **URL:** `/suppliers`
- **Method:** `GET`
- **Query Params:** `?search=Sukses` (Opsional, mencari berdasarkan nama, alamat, email, atau no_hp)

**Response Sukses:**
```json
{
    "message": "Berhasil mengambil daftar supplier",
    "data": [
        {
            "id": 1,
            "nama_supplier": "CV. Sukses Jaya",
            "alamat": "Jl. Merdeka No. 0, Jakarta",
            "no_hp": "081234567890",
            "email": "suksesjaya@gmail.com"
        }
    ]
}
```

### 10.2. Tambah Supplier (POST)
- **URL:** `/suppliers`
- **Method:** `POST`

**Request Body:**
```json
{
    "nama_supplier": "PT. Sumber Maju",
    "alamat": "Jl. Gajah Mada No. 1",
    "no_hp": "08111222333",
    "email": "sumbermaju@gmail.com"
}
```

### 10.3. Edit Supplier (PUT)
- **URL:** `/suppliers/{id}`
- **Method:** `PUT`

**Request Body:**
```json
{
    "nama_supplier": "CV. Sukses Selalu",
    "alamat": "Jl. Merdeka No. 1, Jakarta",
    "no_hp": "081234567891",
    "email": "suksesselalu@gmail.com"
}
```

### 10.4. Hapus Supplier (DELETE)
- **URL:** `/suppliers/{id}`
- **Method:** `DELETE`

**Response Sukses:**
```json
{
    "message": "Berhasil menghapus supplier"
}
```
