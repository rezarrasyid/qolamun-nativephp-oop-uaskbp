<?php
require_once __DIR__ . '/User.php';

class Admin extends User
{
    private $hakAkses;

    public function __construct($koneksi)
    {
        parent::__construct($koneksi);            // panggil constructor parent (User)
        $this->hakAkses = 'full';                 // default hak akses admin
        $this->role     = 'admin';                // override role default
    }

    public function getHakAkses()           { return $this->hakAkses; }
    public function setHakAkses($hakAkses)  { $this->hakAkses = $hakAkses; }

    public function tampilkanInfo()
    {
        return "ADMIN: {$this->nama} | Hak akses: {$this->hakAkses}";
    }

    public function ambilSemuaUser()
    {
        $sql = "SELECT id, nama, username, role, created_at
                FROM users ORDER BY role ASC, id DESC";
        return mysqli_query($this->koneksi, $sql);
    }

    public function hapusUser($id)
    {
        $id = (int)$id;
        $sql = "DELETE FROM users WHERE id = $id";
        return mysqli_query($this->koneksi, $sql);
    }

    public function editUser($id, $nama, $username, $role, $password = null)
    {
        $id       = (int)$id;
        $nama     = mysqli_real_escape_string($this->koneksi, $nama);
        $username = mysqli_real_escape_string($this->koneksi, $username);
        $role     = mysqli_real_escape_string($this->koneksi, $role);

        if (!empty($password)) {
            $pass = md5($password);
            $sql = "UPDATE users SET nama='$nama', username='$username', role='$role', password='$pass' WHERE id=$id";
        } else {
            $sql = "UPDATE users SET nama='$nama', username='$username', role='$role' WHERE id=$id";
        }
        return mysqli_query($this->koneksi, $sql);
    }

    public function tambahKategori($nama, $slug)
    {
        $nama = mysqli_real_escape_string($this->koneksi, $nama);
        $slug = mysqli_real_escape_string($this->koneksi, $slug);
        $sql  = "INSERT INTO categories (nama_kategori, slug) VALUES ('$nama', '$slug')";
        return mysqli_query($this->koneksi, $sql);
    }

    public function editKategori($id, $nama, $slug)
    {
        $id   = (int)$id;
        $nama = mysqli_real_escape_string($this->koneksi, $nama);
        $slug = mysqli_real_escape_string($this->koneksi, $slug);
        $sql  = "UPDATE categories SET nama_kategori='$nama', slug='$slug' WHERE id=$id";
        return mysqli_query($this->koneksi, $sql);
    }

    public function hapusKategori($id)
    {
        $id = (int)$id;
        return mysqli_query($this->koneksi, "DELETE FROM categories WHERE id=$id");
    }

    public function hapusKaryaApapun($id)
    {
        $id = (int)$id;
        $r = mysqli_query($this->koneksi, "SELECT thumbnail FROM posts WHERE id=$id");
        if ($r && $row = mysqli_fetch_assoc($r)) {
            $file = __DIR__ . '/../uploads/' . $row['thumbnail'];
            if (!empty($row['thumbnail']) && file_exists($file)) {
                @unlink($file);
            }
        }
        return mysqli_query($this->koneksi, "DELETE FROM posts WHERE id=$id");
    }
}
?>