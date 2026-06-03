<?php

class User
{
    protected $id;
    protected $nama;
    protected $username;
    protected $password;
    protected $role;
    protected $koneksi;

    public function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function getNama()
    {
        return $this->nama;
    }

    public function setNama($nama)
    {
        $this->nama = $nama;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = md5($password);
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function login($username, $password)
    {
        $username = mysqli_real_escape_string($this->koneksi, $username);
        $passMd5  = md5($password); 

        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$passMd5' LIMIT 1";
        $result = mysqli_query($this->koneksi, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $data = mysqli_fetch_assoc($result);
            
            $this->setId($data['id']);
            $this->setNama($data['nama']);
            $this->setUsername($data['username']);
            $this->password = $data['password'];
            $this->setRole($data['role']);
            
            return $data;
        }
        return false;
    }

    public function register($nama, $username, $password, $role = 'penulis')
    {
        $this->setNama($nama);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setRole($role);

        $escapedNama     = mysqli_real_escape_string($this->koneksi, $this->getNama());
        $escapedUsername = mysqli_real_escape_string($this->koneksi, $this->getUsername());
        $hashedPassword  = $this->getPassword();
        $escapedRole     = mysqli_real_escape_string($this->koneksi, $this->getRole());

        $sql = "INSERT INTO users (nama, username, password, role)
                VALUES ('$escapedNama', '$escapedUsername', '$hashedPassword', '$escapedRole')";
        
        return mysqli_query($this->koneksi, $sql);
    }

    public function ambilSemuaUser()
    {
        $sql = "SELECT id, nama, username, role, created_at FROM users ORDER BY id ASC";
        return mysqli_query($this->koneksi, $sql);
    }

    public function ambilUserById($id)
    {
        $id = (int)$id;
        $sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
        $result = mysqli_query($this->koneksi, $sql);
        
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    public function tampilkanInfo()
    {
        return "User: " . $this->nama . " (role: " . $this->role . ")";
    }
}