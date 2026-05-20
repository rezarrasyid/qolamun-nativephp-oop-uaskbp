<?php
require_once __DIR__ . '/User.php';

class Penulis extends User
{
    private $jumlahKarya;

    public function __construct($koneksi)
    {
        parent::__construct($koneksi);
        $this->role        = 'penulis';
        $this->jumlahKarya = 0;
    }

    public function getJumlahKarya()  {
         return $this->jumlahKarya; 
    }
    public function setJumlahKarya($n){ 
        $this->jumlahKarya = (int)$n; 
    }

    public function tampilkanInfo()
    {
        return "PENULIS: {$this->nama} | Total karya: {$this->jumlahKarya}";
    }

    public function tambahKarya($judul, $konten, $category_id, $thumbnail, $user_id)
    {
        $judul       = mysqli_real_escape_string($this->koneksi, $judul);
        $slug        = $this->buatSlug($judul);
        $konten      = mysqli_real_escape_string($this->koneksi, $konten);
        $category_id = (int)$category_id;
        $thumbnail   = mysqli_real_escape_string($this->koneksi, $thumbnail);
        $user_id     = (int)$user_id;

        $sql = "INSERT INTO posts (judul, slug, konten, thumbnail, user_id, category_id)
                VALUES ('$judul', '$slug', '$konten', '$thumbnail', $user_id, $category_id)";
        return mysqli_query($this->koneksi, $sql);
    }

    public function editKarya($id, $judul, $konten, $category_id, $thumbnail, $user_id)
    {
        $id          = (int)$id;
        $user_id     = (int)$user_id;
        $judul       = mysqli_real_escape_string($this->koneksi, $judul);
        $slug        = $this->buatSlug($judul);
        $konten      = mysqli_real_escape_string($this->koneksi, $konten);
        $category_id = (int)$category_id;

        if (!empty($thumbnail)) {
            $thumbnail = mysqli_real_escape_string($this->koneksi, $thumbnail);
            $sql = "UPDATE posts SET judul='$judul', slug='$slug', konten='$konten',
                    category_id=$category_id, thumbnail='$thumbnail'
                    WHERE id=$id AND user_id=$user_id";
        } else {
            $sql = "UPDATE posts SET judul='$judul', slug='$slug', konten='$konten',
                    category_id=$category_id
                    WHERE id=$id AND user_id=$user_id";
        }
        return mysqli_query($this->koneksi, $sql);
    }

    public function hapusKarya($id, $user_id)
    {
        $id      = (int)$id;
        $user_id = (int)$user_id;

        $r = mysqli_query($this->koneksi, "SELECT thumbnail FROM posts WHERE id=$id AND user_id=$user_id");
        if ($r && $row = mysqli_fetch_assoc($r)) {
            $file = __DIR__ . '/../uploads/' . $row['thumbnail'];
            if (!empty($row['thumbnail']) && file_exists($file)) {
                @unlink($file);
            }
            return mysqli_query($this->koneksi, "DELETE FROM posts WHERE id=$id AND user_id=$user_id");
        }
        return false;
    }

    public function ambilKaryaSaya($user_id)
    {
        $user_id = (int)$user_id;
        $sql = "SELECT p.*, c.nama_kategori
                FROM posts p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.user_id = $user_id
                ORDER BY p.id DESC";
        return mysqli_query($this->koneksi, $sql);
    }

    public function hitungKaryaSaya($user_id)
    {
        $user_id = (int)$user_id;
        $r = mysqli_query($this->koneksi, "SELECT COUNT(*) AS total FROM posts WHERE user_id=$user_id");
        if ($r && $row = mysqli_fetch_assoc($r)) {
            $this->setJumlahKarya($row['total']);
            return (int)$row['total'];
        }
        return 0;
    }

    private function buatSlug($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text . '-' . time();
    }
}
?>
