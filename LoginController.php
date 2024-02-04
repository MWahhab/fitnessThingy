<?php

class LoginController
{
    private database\Database $connection;
    private User     $user;

    /**
     * @param \database\Database $connection
     * @param User $user
     */
    public function __construct(database\Database $connection, User $user)
    {
        $this->connection = $connection;
        $this->user       = $user;
    }

    /**
     * Returns the currently logged-in user id or null if not found
     * @return User|null
     */
    public function isValidLogin(): User|null
    {
        $userEmail = $this->user->getEmail();
        $userPw    = $this->user->getPassword();
        $foundUser = $this->connection->select('user', [], "email = '{$userEmail}' AND password = '{$userPw}'", 1);

        if ($foundUser) {
            $this->user->setId($foundUser['id']);
            $this->user->setFullName($foundUser['full_name']);
            $this->user->setAdmin($foundUser['is_admin']);
            return $this->user;
        }

        return null;
    }

}