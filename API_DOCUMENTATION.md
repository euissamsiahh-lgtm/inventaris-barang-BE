# Dokumentasi API - Inventaris Barang

Dokumen ini berisi panduan untuk Frontend Developer dalam melakukan integrasi dengan Backend API Inventaris Barang.

**Base URL (Ubah IP sesuai dengan IP Laptop Backend saat ini):**
`http://192.168.88.74/inventaris-barang-BE/public/api`

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
- **Query Params (Opsional):**
  - `?kategori=ATK` (Filter Kategori)
  - `?barang_id=1` (Filter Barang Spesifik)

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
}
```
