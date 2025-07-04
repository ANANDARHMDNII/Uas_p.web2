# 📚 UAS Pemrograman Website 2

Repositori ini adalah hasil pengerjaan **UAS Pemrograman Website 2** menggunakan framework **CodeIgniter 4**.  
Berisi pengembangan fitur artikel dengan kategori, dilengkapi relasi tabel One-to-Many di database.

---

## 👤 Data Mahasiswa

| Atribut     | Keterangan                          |
|-------------|-------------------------------------|
| Nama        | Ananda Rahmadani                    |
| NIM         | 312310461                           |
| Kelas       | TI.23.A.5                           |
| Mata Kuliah | Pemrograman Website 2               |

---

## 🗂️ Deskripsi Singkat

Proyek ini mengimplementasikan relasi One-to-Many antara tabel **artikel** dan **kategori** menggunakan CodeIgniter 4.  
Tujuannya adalah agar setiap artikel dapat dikaitkan dengan satu kategori tertentu, serta memungkinkan pengelolaan CRUD artikel lengkap dengan fitur filtering.

---

## 🎯 Tujuan Praktikum

- Memahami relasi One-to-Many antar tabel
- Menghubungkan tabel artikel dengan tabel kategori
- Melakukan operasi CRUD artikel
- Menampilkan data artikel lengkap dengan nama kategori
- Menggunakan Laragon untuk server lokal

---

## ⚙️ Fitur Aplikasi

✅ Relasi artikel dengan kategori (One-to-Many)  
✅ CRUD Artikel (Tambah, Edit, Hapus)  
✅ Filter artikel berdasarkan kategori  
✅ Pencarian judul artikel di admin  
✅ Upload gambar artikel  
✅ Validasi form  

---

## 🛠️ Langkah-langkah Pengerjaan & Buat Tabel Database di Laragon

### 1️⃣ Setup Database di Laragon
- Jalankan Laragon → Start All
- Akses phpMyAdmin → buat database `lab_ci4`

---

### 2️⃣ Membuat Tabel `article`

```sql
CREATE TABLE article (
  id INT AUTO_INCREMENT PRIMARY KEY,
  status TINYINT(1) DEFAULT '1',
  slug VARCHAR(200),
  category VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  title VARCHAR(200),
  image VARCHAR(200),
  content TEXT
);
```

---

### 3️⃣ Membuat Tabel kategori

```sql
CREATE TABLE kategori (
  id_kategori INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL,
  slug_kategori VARCHAR(100)
);
```


### 4️⃣ Membuat Tabel User
```sql
CREATE TABLE user (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL
);
```
### 5️⃣ Membuat User Model
**app/Models/UserModel.php**
```php
<?php
namespace App\Models;
use CodeIgniter\Model;
class UserModel extends Model
{
    protected $table = "user";
    protected $primaryKey = "id";
    protected $useAutoIncrement = true;
    protected $allowedFields = ["username", "password"];
}

```

### 6️⃣ Memperbarui Model Artikel

Tambahkan method untuk *join* ke kategori.

**app/Models/ArtikelModel.php**

```php
      <?php
namespace App\Models;
use CodeIgniter\Model;
class ArticleModel extends Model
{
    protected $table = 'article';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['title', 'content', 'image', 'status', 'slug', 'created_at', 'updated_at', 'category'];
    protected $useTimestamps = true;

    public function searchArticles($keyword)
    {
        return $this->like('title', $keyword)
                    ->orLike('content', $keyword)
                    ->findAll();
    }

    public function getCategories()
    {
        return $this->select('category')
            ->distinct()
            ->where('category IS NOT NULL')
            ->where('category !=', '')
            ->findAll();
    }
}

---

``` 

### 7️⃣ Memodifikasi Controller Artikel 

✅ Halaman Index untuk menampilkan artikel dengan kategori  
✅ Halaman Admin dengan fitur filter kategori & pencarian  
✅ Form Tambah & Edit dengan pilihan kategori

```php
<?php
namespace App\Controllers;
use App\Models\ArticleModel;
class Article extends BaseController
{
    public function index()
    {
        $title = "Article Lists";
        $model = new ArticleModel();
        $search = $this->request->getGet('q');
        $category = $this->request->getGet('category');
        $sort = $this->request->getGet('sort');
        $categories = $model->getCategories();

        $builder = $model;
        if ($search) {
            $builder = $builder->like('title', $search)->orLike('content', $search);
        }
        if ($category) {
            $builder = $builder->where('category', $category);
        }
        if ($sort === 'asc') {
            $builder = $builder->orderBy('created_at', 'asc');
        } elseif ($sort === 'desc') {
            $builder = $builder->orderBy('created_at', 'desc');
        } else {
            $builder = $builder->orderBy('created_at', 'desc');
        }
        $article = $builder->findAll();
        if ($this->request->isAJAX()) {
            return view('article/_list', compact('article'));
        }
        return view("article/index", compact("article", "title", "search", "categories", "category", "sort"));
    }
    public function view($slug)
    {
        $model = new ArticleModel();
        $article = $model
            ->where([
                "slug" => $slug,
            ])
            ->first();
        if (!$article) {
            throw PageNotFoundException::forPageNotFound();
        }
        $title = $article["title"];
        return view("article/detail", compact("article", "title"));
    }
    public function admin_index()
    {
        $title = "Article Lists";
        $model = new ArticleModel();
        $article = $model->findAll();
        return view("article/admin_index", compact("article", "title"));
    }
    public function add()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            "title" => "required",
            "content" => "required",
            "category" => "required",
        ]);

        $isDataValid = $validation->withRequest($this->request)->run();

        if ($isDataValid) {
            $article = new ArticleModel();
            $title = $this->request->getPost("title");
            $slug = url_title($title, '-', true);
            $exists = $article->where("slug", $slug)->countAllResults();
            if ($exists > 0) {
                $slug .= '-' . time();
            }

            // Handle image upload
            $imageFile = $this->request->getFile('image');
            $imageName = null;
            if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                $imageName = $imageFile->getRandomName();
                $imageFile->move(ROOTPATH . 'public/image', $imageName);
            }

            $article->insert([
                "title" => $title,
                "content" => $this->request->getPost("content"),
                "slug" => $slug,
                "category" => $this->request->getPost("category"),
                "created_at" => date("Y-m-d H:i:s"),
                "image" => $imageName,
            ]);

            return redirect()->to("admin/article");
        }

        $title = "Add Article";
        return view("article/form_add", compact("title"));
    }
    public function edit($id)
    {
        $article = new ArticleModel();
        $validation = \Config\Services::validation();
        $validation->setRules([
            "title" => "required",
            "content" => "required",
            "category" => "required",
        ]);

        $isDataValid = $validation->withRequest($this->request)->run();

        if ($isDataValid) {
            $title = $this->request->getPost("title");
            $slug = url_title($title, '-', true);
            $exists = $article->where("slug", $slug)->where("id !=", $id)->countAllResults();
            if ($exists > 0) {
                $slug .= '-' . time();
            }

            $article->update($id, [
                "title" => $title,
                "content" => $this->request->getPost("content"),
                "slug" => $slug,
                "category" => $this->request->getPost("category"),
            ]);

            return redirect()->to("admin/article");
        }

        $data = $article->where("id", $id)->first();
        $title = "Edit Article";
        return view("article/form_edit", compact("title", "data"));
    }

    public function delete($id)
    {
        $article = new ArticleModel();
        $article->delete($id);
        return redirect("admin/article");
    }

    public function save()
{
    $data = [
        'title'    => $this->request->getPost('title'),
        'content'  => $this->request->getPost('content'),
        'image'    => $this->request->getPost('image'),
        'status'   => 1,
        'slug'     => url_title($this->request->getPost('title'), '-', true),
        'category' => $this->request->getPost('category'),
    ];

    dd($data); // Cek apakah status 1 dan created_at tidak dikirim
    $model = new \App\Models\ArticleModel();
    $model->save($data);
}


}

```

---

### 8️⃣ Menyesuaikan Tampilan (View)

✅ **index.php**  
- Menampilkan judul artikel, isi singkat, gambar, dan kategori.
```php
<?= $this->include("template/header") ?>
<form method="get" action="<?= base_url('/article') ?>" style="margin-bottom:20px;">
    <input type="text" name="q" value="<?= isset(
        $search
    ) ? esc($search) : '' ?>" placeholder="Search articles..." />
    <select name="category">
        <option value="">All Categories</option>
        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
            <option value="<?= esc($cat['category']) ?>" <?= (isset($category) && $category == $cat['category']) ? 'selected' : '' ?>><?= esc($cat['category']) ?></option>
        <?php endforeach; endif; ?>
    </select>
    <select name="sort">
        <option value="desc" <?= (isset($sort) && $sort == 'desc') ? 'selected' : '' ?>>Latest</option>
        <option value="asc" <?= (isset($sort) && $sort == 'asc') ? 'selected' : '' ?>>Oldest</option>
    </select>
    <button type="submit">Search</button>
</form>
<div id="article-list">
<?php if ($article):
    foreach ($article as $row): ?>
<article class="entry">
    <h2><a href="<?= base_url("/article/" . $row["slug"]) ?>"><?= $row["title"] ?></a></h2>
    <img src="<?= base_url("/image/" . $row["image"]) ?>" alt="<?= $row["slug"] ?>">
    <p><?= substr($row["content"], 0, 200) ?>...</p>
    <a href="<?= base_url("/article/" . $row["slug"]) ?>" class="btn-read-more">Read More</a>
</article>
<hr class="divider" />
<?php endforeach;
else: ?>
<article class="entry">
    <h2>No articles available.</h2>
</article>
<?php endif; ?>
</div>
<?= $this->include("template/footer") ?>
```

✅ **admin_index.php**  
- Tabel artikel + kolom kategori
- Form pencarian & filter kategori
```php
<?= $this->include("template/header") ?>

<div id="container">
    <a href="<?= base_url('/admin/article/add') ?>" class="btn-read-more">➕ Add Article</a>

    <div id="main">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th>Function</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($article): ?>
                        <?php foreach ($article as $row): ?>
                            <tr>
                                <td><?= esc($row["id"]) ?></td>
                                <td>
                                    <b class="title-text"><?= esc($row["title"]) ?></b>
                                    <p class="content-preview"><?= esc(substr(strip_tags($row["content"]), 0, 50)) ?>...</p>
                                </td>
                                <td class="slug-column"><?= esc($row["slug"]) ?></td>
                                <td><?= esc($row["category"] ?? 'N/A') ?></td>
                                <td><?= date('d M Y, H:i', strtotime($row["created_at"] ?? 'now')) ?></td>
                                <td><?= esc($row["status"] ?? 'Draft') ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-read-more" href="<?= base_url("/admin/article/edit/" . $row["id"]) ?>">✏️ Edit</a>
                                        <a class="btn btn-back" onclick="return confirm('Are you sure?');" href="<?= base_url("/admin/article/delete/" . $row["id"]) ?>">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-color);">No articles available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->include("template/footer") ?>

```

✅ **form_add.php** dan **form_edit.php**  
- Dropdown untuk memilih kategori
- Input judul, isi, gambar
- 
**form_add.php**
```php
<?= $this->include("template/header") ?>
<h2><?= $title ?></h2>
<form action="" method="post" enctype="multipart/form-data">
    <p>
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title">
    </p>
    <p>
        <label for="content">Content:</label><br>
        <textarea id="content" name="content" cols="50" rows="10"></textarea>
    </p>
    <p>
        <label for="slug">Slug:</label><br>
        <input type="text" id="slug" name="slug">
    </p>
    <p>
        <label for="category">Category:</label><br>
        <input type="text" id="category" name="category">
    </p>
    <p>
        <label for="created_at">Created At:</label><br>
        <input type="datetime-local" id="created_at" name="created_at" value="<?= date('Y-m-d\TH:i') ?>">
    </p>
    <p>
        <label for="image">Image:</label><br>
        <input type="file" id="image" name="image" accept="image/*">
    </p>
    <p><input type="submit" value="Submit" class="btn-back"></p>
</form>
<?= $this->include("template/footer") ?>

```
**form_edit.php**
```php
<?= $this->include("template/header") ?>
<h2><?= $title ?></h2>
<form action="" method="post">
    <p>
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" value="<?= $data["title"] ?>">
    </p>
    <p>
        <label for="content">Content:</label><br>
        <textarea id="content" name="content" cols="50" rows="10"><?= $data["content"] ?></textarea>
    </p>
    <p>
        <label for="slug">Slug:</label><br>
        <input type="text" id="slug" name="slug" value="<?= $data["slug"] ?>">
    </p>
    <p>
        <label for="category">Category:</label><br>
        <input type="text" id="category" name="category" value="<?= $data["category"] ?>">
    </p>
    <p>
        <label for="created_at">Created At:</label><br>
        <input type="datetime-local" id="created_at" name="created_at" value="<?= date('Y-m-d\TH:i', strtotime($data['created_at'])) ?>">
    </p>
    <p><input type="submit" value="Submit" class="btn-back"></p>
</form>
<?= $this->include("template/footer") ?>

```
---

## 💻 Testing

Berikut rangkuman hasil pengujian aplikasi yang dibuat:

✅ 1. Halaman Daftar Artikel Lengkap Kategori
Menampilkan seluruh artikel beserta nama kategori yang terhubung.
📸 Contoh tampilan:
![image](https://github.com/user-attachments/assets/8b75ee9c-6eac-4082-96ec-58d4836a447c)

✅ 2. Formulir Penambahan Artikel Baru
Form tambah artikel dilengkapi dropdown kategori untuk pemilihan yang sesuai.
📸 Contoh tampilan form tambah:
![image](https://github.com/user-attachments/assets/4713160c-37a1-4f17-82b6-4410102dfef4)

✅ 3. Fitur Edit Artikel
Memungkinkan pengguna admin mengubah isi artikel, judul, dan mengganti kategori terpilih.
📸 Contoh tampilan form edit:
![image](https://github.com/user-attachments/assets/019985cb-bd84-418f-83c1-8d1e79a51cc2)

📸 Hasil setelah disimpan:
![image](https://github.com/user-attachments/assets/4ce0f19d-dfa6-4097-bfd0-8d7b2a0859e4)

✅ 4. Fitur Penghapusan Artikel
Admin dapat menghapus artikel yang sudah tidak diperlukan.
📸 Dialog konfirmasi hapus:
![image](https://github.com/user-attachments/assets/dac82b7e-e26c-4ab7-a79d-085cf773c8f1)

📸 Daftar artikel setelah dihapus:
![image](https://github.com/user-attachments/assets/ee063169-2571-4bce-b212-23acd7ea173f)


---

## ⚠️ Catatan Penting

- Folder `writable/` di-ignore dari Git, kecuali file `index.html`
- File `.env` berisi konfigurasi database **tidak diupload**
- Pastikan `writable/` bisa diakses server untuk logs & uploads

---

## 🗃️ Cara Menjalankan

✅ Clone repository:  
```bash
git clone https://github.com/ANANDARHMDNII/Uas_p.web2.git
```

✅ Masuk folder project:  
```bash
cd Uas_p.web2
```

✅ Install dependency:  
```bash
composer install
```

✅ Copy file env & konfigurasi:  
```bash
cp env .env
```
Edit `.env` sesuai koneksi database lokal.

✅ Jalankan server:  
```bash
php spark serve
```
Akses di browser: `http://localhost:8080`

✅ Import file `6x.sql` ke database MySQL.

---

## 🚀 Catatan
Proyek ini dibuat sebagai media belajar. Cocok untuk teman-teman yang mau memahami konsep relasi One-to-Many di CodeIgniter 4 sambil praktek langsung. Jangan ragu untuk bereksperimen atau menambah fitur lain sesuai ide kalian!

📢 Kontribusi
Silakan fork atau modifikasi project ini untuk kebutuhan tugas atau pengembangan lebih lanjut. Sharing is caring!

❤️ Terima kasih
Terima kasih sudah mampir dan membaca. Semoga membantu proses belajar kalian. Happy coding!
