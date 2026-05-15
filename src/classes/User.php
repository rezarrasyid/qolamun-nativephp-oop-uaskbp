<?php
class User {
    protected $id;
    protected $nama;
    protected $username;
    protected $password;
    protected $role;
    protected $koneksi; // mysqli connection object

    public function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    public function getId() { 
        return $this->id; 
    }
    public function setId($id) { 
        $this->id = (int)$id; 
    }

    public function getNama()               { return $this->nama; }
    public function setNama($nama)          { $this->nama = $nama; }

    public function getUsername()           { return $this->username; }
    public function setUsername($username)  { $this->username = $username; }

    public function getPassword()           { return $this->password; }
    public function setPassword($password)  {
        $this->password = md5($password);
    }

    public function getRole()               { return $this->role; }
    public function setRole($role)          { $this->role = $role; }


    public function login($username, $password) {
        $username = mysqli_real_escape_string($this->koneksi, $username);
        $passMd5  = md5($password); // password user di-hash MD5 lalu dicocokkan

        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$passMd5' LIMIT 1";
        $result = mysqli_query($this->koneksi, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $data = mysqli_fetch_assoc($result);
            // Set value ke property melalui setter (encapsulation tetap terjaga)
            $this->id       = $data['id'];
            $this->nama     = $data['nama'];
            $this->username = $data['username'];
            $this->password = $data['password'];
            $this->role     = $data['role'];
            return $data;
        }
        return false;
    }

    public function register($nama, $username, $password, $role = 'penulis')
    {
        $nama     = mysqli_real_escape_string($this->koneksi, $nama);
        $username = mysqli_real_escape_string($this->koneksi, $username);
        $password = md5($password);
        $role     = mysqli_real_escape_string($this->koneksi, $role);

        $sql = "INSERT INTO users (nama, username, password, role)
                VALUES ('$nama', '$username', '$password', '$role')";
        return mysqli_query($this->koneksi, $sql);
    }

    /**
     * Ambil semua user dari database (akan di-override di class Admin).
     */
    public function ambilSemuaUser()
    {
        $sql = "SELECT id, nama, username, role, created_at FROM users ORDER BY id ASC";
        return mysqli_query($this->koneksi, $sql);
    }

    /**
     * Ambil 1 user berdasarkan ID.
     */
    public function ambilUserById($id)
    {
        $id = (int)$id;
        $sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
        $result = mysqli_query($this->koneksi, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    public function tampilkanInfo()
    {
        return "User: {$this->nama} (role: {$this->role})";
    }
}