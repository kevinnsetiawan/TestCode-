# Courier Master Data API (Laravel)

REST API untuk manajemen master data **Kurir (`couriers`)** yang dibangun menggunakan Laravel 13, lengkap dengan pagination, pencarian multi-kata, filter level, pengurutan dinamis, validasi input, serta unit & feature test.

## Struktur Tabel `couriers`

| Kolom | Tipe | Keterangan |
| :--- | :--- | :--- |
| `id` | `bigint` (PK) | Primary key |
| `code` | `varchar(32)` (Unique) | Kode unik kurir (contoh: `KUR-0001`) |
| `name` | `varchar(255)` (Index) | Nama lengkap kurir |
| `email` | `varchar(255)` (Unique) | Email kurir |
| `phone` | `varchar(25)` (Unique) | Nomor telepon kurir |
| `level` | `tinyint unsigned` (Index) | Level kurir (`1` sampai `5`, default `1`) |
| `vehicle_type` | `varchar(30)` | Jenis kendaraan (`bicycle`, `motorcycle`, `car`, `van`, `truck`) |
| `license_plate` | `varchar(20)` (Nullable) | Nomor polisi kendaraan |
| `service_area` | `varchar(100)` (Nullable, Index) | Area layanan kurir |
| `status` | `varchar(20)` (Index) | Status kurir (`active`, `inactive`, `on_leave`, default `active`) |
| `registered_at` | `timestamp` (Index) | Tanggal/waktu pendaftaran kurir |
| `created_at` | `timestamp` | Waktu pembuatan record |
| `updated_at` | `timestamp` | Waktu pembaruan record |

## Instalasi & Menjalankan Lokal

```bash
git clone https://github.com/kevinnsetiawan/TestCode-.git
cd TestCode-
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Dokumentasi API

Base URL: `/api/couriers`

### 1. List Couriers (`GET /api/couriers`)

Mengembalikan daftar kurir dengan **pagination** dan **default sort berdasarkan `name` (ascending)**.

Query parameters yang didukung:
- `page` (opsional): Nomor halaman (default `1`).
- `per_page` (opsional): Jumlah item per halaman, `1–100` (default `15`).
- `search` (opsional): Pencarian multi-kata case-insensitive. Contoh: `?search=budi+agung` akan cocok dengan `"Budiono Hadi Agung"`.
- `level` (opsional): Filter berdasarkan satu atau beberapa level (`1–5`), dipisahkan koma atau array. Contoh: `?level=2,3`.
- `sort_by` atau `sort` (opsional): Kolom pengurutan:
  - `name` (default, urutan `asc`)
  - `registered_at` (override default sort ke tanggal didaftarkan, default `desc`)
- `sort_dir` atau `order` (opsional): Arah pengurutan `asc` atau `desc`.

Contoh request:
```bash
curl "http://localhost:8000/api/couriers?search=budi+agung&level=2,3&sort_by=registered_at&sort_dir=asc&per_page=10"
```

### 2. Show Courier (`GET /api/couriers/{id}`)

Mengembalikan seluruh data pada kurir tertentu.

```bash
curl "http://localhost:8000/api/couriers/1"
```

### 3. Create Courier (`POST /api/couriers`)

Menyimpan kurir baru dengan validasi input lengkap (`code`, `name`, `email`, `phone`, `level` 1–5, `vehicle_type`, dll).

```bash
curl -X POST "http://localhost:8000/api/couriers" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "code": "KUR-1001",
    "name": "Budiono Hadi Agung",
    "email": "budiono.agung@example.com",
    "phone": "081234567890",
    "level": 3,
    "vehicle_type": "motorcycle",
    "license_plate": "B 4123 XYZ",
    "service_area": "Jakarta Selatan",
    "status": "active"
  }'
```

### 4. Update Courier (`PUT/PATCH /api/couriers/{id}`)

Memperbarui data kurir dengan validasi input lengkap (termasuk pengecualian unique untuk record yang sedang di-update).

```bash
curl -X PUT "http://localhost:8000/api/couriers/1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "level": 4,
    "service_area": "Jakarta Pusat"
  }'
```

### 5. Delete Courier (`DELETE /api/couriers/{id}`)

Menghapus data kurir dari database.

```bash
curl -X DELETE "http://localhost:8000/api/couriers/1" \
  -H "Accept: application/json"
```

## Menjalankan Test

```bash
php artisan test
```

Test mencakup:
- **Unit Tests** (`tests/Unit/CourierModelTest.php`): default `registered_at` & `status`, casts, `scopeSearch`, `scopeLevels`, dan `scopeSortedBy`.
- **Feature Tests** (`tests/Feature/CourierApiTest.php`): `index` (pagination, default sort by `name`, override sort `registered_at`, multi-word `?search=budi+agung`, filter `?level=2,3`), `show`, `store` (validasi + `assertDatabaseHas`), `update` (validasi + `assertDatabaseHas`), dan `destroy` (`assertDatabaseMissing`).
