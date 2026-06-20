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
