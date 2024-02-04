<?php

class User
{
    private int    $id;
    private string $fullName;
    private string $email;
    private string $password;
    private int    $admin;

    /**
     * @param string $email
     * @param string $password
     * @param string $fullName
     */
    public function __construct(string $email, string $password, string $fullName = '')
    {
        $this->email    = $email;
        $this->password = $password;
        $this->fullName = $fullName;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    public function isAdmin(): int
    {
        return $this->admin;
    }

    public function setAdmin(int $admin): void
    {
        $this->admin = $admin;
    }



    /**
     * @param Database $connection
     * @return void
     */
    public function register(Database $connection): void
    {
        if (!empty($connection->selectUserByEmail($this->email))) {
            die('User is already registered.');
        }

        $userData = [
            'full_name' => $this->getFullName(),
            'email'     => $this->getEmail(),
            'password'  => $this->getPassword()
        ];

        $connection->insert('user', $userData);
        header("Location: http://localhost/fitnessThingy/login.html");
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $fullName
     * @return void
     */
    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @param array $data
     * @return bool
     */
    public static function validateUserData(array $data): bool
    {
        if(!isset($data['email'], $data['full-name'], $data['password'], $data['confirm-password'])) {
            return false;
        }

        if (!filter_var($data['email'], FILTER_SANITIZE_EMAIL)) {
            return false;
        }

        return true;
    }

}