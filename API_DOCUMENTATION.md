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
