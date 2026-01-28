## Panduan Singkat MVC (Model–View–Controller)

File ini pakai contoh Laravel (sesuai projek kamu), tapi konsepnya berlaku umum.

---

### 1. Konsep dasar

- **Model (M)**
    - Representasi tabel/data di database.
    - Menangani relasi, query, dan logika yang dekat dengan data.
    - Contoh: `App\Models\Tiket`, `App\Models\Event`.

- **View (V)**
    - File tampilan yang dikirim ke browser (Blade: `.blade.php`).
    - Hanya berisi HTML/CSS/JS + sedikit Blade (loop / kondisi), **tanpa** logika bisnis berat.

- **Controller (C)**
    - Penghubung HTTP request → Model → View.
    - Di sinilah fungsi seperti `index`, `store`, `update`, `destroy`, dll.

- **Route**
    - Pintu masuk URL → menentukan controller/fungsi mana yang dipanggil.
    - Di Laravel: `routes/web.php`.

Alur sederhananya:

`Request (URL) → Route → Controller → (Model) → View → Response ke browser`

---

### 2. Urutan kerja bikin fitur baru (versi singkat)

Misal mau bikin fitur **Manajemen Tiket**:

1. **Buat migration + model**

    ```bash
    php artisan make:model Tiket -m
    ```

    - Edit migration di `database/migrations/...create_tikets_table.php`.
    - Jalankan:
        ```bash
        php artisan migrate
        ```
    - Di `app/Models/Tiket.php` isi `$fillable` dan relasi.

2. **Buat controller**

    ```bash
    php artisan make:controller Admin/TiketController --resource
    ```

    - Tambah `use App\Models\Tiket;` di atas kelas.
    - Isi fungsi:
        - `index()` → list data
        - `create()` → tampilkan form tambah
        - `store()` → simpan data baru
        - `edit()` → form edit
        - `update()` → update data
        - `destroy()` → hapus data

3. **Daftarkan route**
   Di `routes/web.php` bagian admin:

    ```php
    use App\Http\Controllers\Admin\TiketController;

    Route::middleware('auth')->middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('tickets', TiketController::class);
    });
    ```

4. **Buat view (Blade)**
    - Contoh file:
        - `resources/views/admin/tickets/index.blade.php`
        - `resources/views/admin/tickets/create.blade.php`
        - `resources/views/admin/tickets/edit.blade.php`
    - Controller mengembalikan view:
        ```php
        public function index()
        {
            $tikets = Tiket::latest()->paginate(10);
            return view('admin.tickets.index', compact('tikets'));
        }
        ```

---

### 3. Contoh pola fungsi di Controller (CRUD)

```php
use App\Models\Tiket;

class TiketController extends Controller
{
    public function index()
    {
        $tikets = Tiket::latest()->paginate(10);
        return view('admin.tickets.index', compact('tikets'));
    }

    public function create()
    {
        return view('admin.tickets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id' => 'required|exists:events,id',
            'tipe'     => 'required|string|max:255',
            'harga'    => 'required|numeric|min:0',
            'stok'     => 'required|integer|min:0',
        ]);

        Tiket::create($data);

        return redirect()
            ->route('admin.events.show', $data['event_id'])
            ->with('success', 'Tiket berhasil ditambahkan.');
    }

    public function edit(Tiket $ticket)
    {
        return view('admin.tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Tiket $ticket)
    {
        $data = $request->validate([
            'tipe'  => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok'  => 'required|integer|min:0',
        ]);

        $ticket->update($data);

        return redirect()
            ->route('admin.events.show', $ticket->event_id)
            ->with('success', 'Tiket berhasil diperbarui.');
    }

    public function destroy(Tiket $ticket)
    {
        $eventId = $ticket->event_id;
        $ticket->delete();

        return redirect()
            ->route('admin.events.show', $eventId)
            ->with('success', 'Tiket berhasil dihapus.');
    }
}
```

Catatan:

- Mengetik parameter `Tiket $ticket` otomatis melakukan **route–model binding**.
- Hindari logika berat di view; taruh di controller/model.

---

### 4. Contoh struktur folder MVC (Laravel)

```text
app/
  Models/
    Event.php
    Tiket.php
  Http/
    Controllers/
      Admin/
        EventController.php
        TiketController.php

resources/
  views/
    events/
      index.blade.php
      show.blade.php
    admin/
      tickets/
        index.blade.php
        create.blade.php
        edit.blade.php

routes/
  web.php
```

---

### 5. Contoh prompt ke AI (seperti Cursor) untuk bantu MVC

Kamu bisa pakai template prompt seperti ini (tinggal ganti nama fitur/model):

- **Bikin model + migration**

    > Buatkan model Laravel bernama `Tiket` dengan field `event_id` (relasi ke `events`), `tipe` (string), `harga` (integer), `stok` (integer). Sertakan contoh isi `$fillable` dan relasi ke `Event` serta `DetailOrder`.

- **Bikin controller CRUD**

    > Buat controller Laravel `Admin/TiketController` resourceful (index, create, store, edit, update, destroy) untuk mengelola tiket per event. Gunakan validasi yang wajar dan redirect kembali ke `admin.events.show`.

- **Bikin route admin**

    > Tambahkan route resource `tickets` di grup route admin (prefix `admin`, name `admin.`) yang mengarah ke `Admin\TiketController`.

- **Bikin view index & form**

    > Buat view Blade `admin/tickets/index.blade.php` untuk menampilkan daftar tiket sebuah event (tipe, harga, stok) dan tombol tambah/ubah/hapus. Gunakan Tailwind + DaisyUI.

- **Refactor / perbaikan**
    > Lihat file `app/Http/Controllers/Admin/TiketController.php` dan rapikan kode agar mengikuti pola Laravel standar (route model binding, flash message, dan validasi Request).

Kamu bisa copy–paste prompt di atas dan menyesuaikan nama model/field sesuai kebutuhan fitur barumu.

---

### 6. Checklist saat membuat MVC baru

- **Model**
    - [ ] Ada file model di `app/Models/...`
    - [ ] `$fillable` sudah diisi
    - [ ] Relasi antar model sudah dibuat (belongsTo / hasMany / belongsToMany)

- **Migration**
    - [ ] Tabel dan kolom sudah benar
    - [ ] Sudah menjalankan `php artisan migrate`

- **Controller**
    - [ ] Namespace dan `use` sudah benar (`use App\Models\NamaModel;`)
    - [ ] Fungsi CRUD yang dibutuhkan sudah diisi

- **Route**
    - [ ] Route mengarah ke controller yang benar
    - [ ] Prefix + middleware (misal `auth`, `admin`) sudah sesuai

- **View**
    - [ ] File Blade ada dan path-nya cocok dengan `view('...')` di controller
    - [ ] Data yang dipakai di view memang dikirim dari controller (`compact(...)` / `with(...)`)

---

### 7. Ringkasan sintaks / perintah penting

Bagian ini khusus menjawab: **“kalau mau bikin database, model, controller, view itu sintaksnya apa?”**

#### 7.1. Bikin database + migration

- **1) Bikin database di MySQL (manual sekali saja)**

    Jalankan di MySQL (phpMyAdmin / CLI):

    ```sql
    CREATE DATABASE ticketing_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

    Lalu di `.env` Laravel:

    ```env
    DB_DATABASE=ticketing_app
    DB_USERNAME=root
    DB_PASSWORD=      # sesuaikan
    ```

- **2) Bikin migration via artisan**

    ```bash
    php artisan make:migration create_tikets_table
    ```

    Contoh isi di file `database/migrations/xxxx_xx_xx_xxxxxx_create_tikets_table.php`:

    ```php
    Schema::create('tikets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('event_id')->constrained()->cascadeOnDelete();
        $table->string('tipe');
        $table->unsignedInteger('harga')->default(0);
        $table->unsignedInteger('stok')->default(0);
        $table->timestamps();
    });
    ```

- **3) Jalankan semua migration**

    ```bash
    php artisan migrate
    ```

#### 7.2. Bikin Model

- **Perintah artisan model + migration (opsional)**

    ```bash
    php artisan make:model Tiket -m
    ```

    Contoh isi minimal di `app/Models/Tiket.php`:

    ```php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Tiket extends Model
    {
        protected $fillable = [
            'event_id',
            'tipe',
            'harga',
            'stok',
        ];

        public function event()
        {
            return $this->belongsTo(Event::class);
        }
    }
    ```

#### 7.3. Bikin Controller

- **Perintah artisan controller resource**

    ```bash
    php artisan make:controller Admin/TiketController --resource
    ```

    Di atas kelas tambahkan:

    ```php
    use App\Models\Tiket;
    ```

    Lalu isi method sesuai kebutuhan (lihat contoh di bagian 3).

#### 7.4. Daftarkan Route

Di `routes/web.php`:

```php
use App\Http\Controllers\Admin\TiketController;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('tickets', TiketController::class);
    });
```

#### 7.5. Bikin View (Blade)

- **Buat file Blade baru** (manual di folder `resources/views`), misalnya:
    - `resources/views/admin/tickets/index.blade.php`
    - `resources/views/admin/tickets/create.blade.php`

- **Controller memanggil view**:

    ```php
    public function index()
    {
        $tikets = Tiket::latest()->paginate(10);
        return view('admin.tickets.index', compact('tikets'));
    }
    ```

- **Contoh isi sangat sederhana di `admin/tickets/index.blade.php`**:

    ```php
    <x-layouts.admin title="Manajemen Tiket">
        <h1 class="text-2xl font-bold mb-4">Daftar Tiket</h1>

        <table class="table w-full">
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Harga</th>
                    <th>Stok</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tikets as $tiket)
                    <tr>
                        <td>{{ $tiket->tipe }}</td>
                        <td>{{ number_format($tiket->harga, 0, ',', '.') }}</td>
                        <td>{{ $tiket->stok }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-layouts.admin>
    ```

Dengan kombinasi perintah:

- `php artisan make:model ... -m`
- `php artisan make:controller ... --resource`
- `php artisan migrate`

serta penambahan route + view, kamu sudah punya alur MVC lengkap untuk fitur baru.

git clone https://github.com/Reagenn/projek.git
cd projek
composer install # Install semua library Laravel
cp .env.example .env # Duplikat file setting environment
php artisan key:generate # Generate kunci aplikasi

# (Edit file .env, sesuaikan nama database dengan soal ujian)

php artisan migrate --seed # Jalankan database + data dummy (penting!)
php artisan serve

php artisan db:seed
php artisan migrate:fresh --seed
php artisan db:seed --class=UserSeeder
