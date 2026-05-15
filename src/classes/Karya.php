<?php
class Karya
{
    protected $koneksi;

    public function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    public function ambilSemuaKarya()
    {
        $sql = "SELECT p.*, c.nama_kategori, u.nama AS nama_penulis
                FROM posts p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN users u ON u.id = p.user_id
                ORDER BY p.created_at DESC, p.id DESC";
        return mysqli_query($this->koneksi, $sql);
    }

    public function ambilKaryaById($id)
    {
        $id = (int)$id;
        $sql = "SELECT p.*, c.nama_kategori, u.nama AS nama_penulis
                FROM posts p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN users u ON u.id = p.user_id
                WHERE p.id = $id LIMIT 1";
        $r = mysqli_query($this->koneksi, $sql);
        return $r ? mysqli_fetch_assoc($r) : null;
    }

    public function ambilSemuaKategori()
    {
        return mysqli_query($this->koneksi, "SELECT * FROM categories ORDER BY nama_kategori ASC");
    }

    public function hitungSemua()
    {
        $r = mysqli_query($this->koneksi, "SELECT COUNT(*) AS t FROM posts");
        return $r ? (int)mysqli_fetch_assoc($r)['t'] : 0;
    }
}
?>