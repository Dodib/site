<?php

namespace ttm4135\webapp\models;

class User
{
    const INSERT_QUERY = "INSERT INTO users(username, password, email, bio, isadmin) VALUES('%s', '%s', '%s' , '%s' , '%s')";
    const UPDATE_QUERY = "UPDATE users SET username='%s', password='%s', email='%s', bio='%s', isadmin='%s' WHERE id='%s'";
    const DELETE_QUERY = "DELETE FROM users WHERE id='%s'";
    const FIND_BY_NAME_QUERY = "SELECT * FROM users WHERE username='%s'";
    const FIND_BY_ID_QUERY = "SELECT * FROM users WHERE id='%s'";
    protected $id = null;
    protected $username;
    protected $password;
    protected $email;
    protected $bio = 'Bio is empty.';
    protected $isAdmin = 0;

    static $app;


    static function make($id, $username, $password, $email, $bio, $isAdmin )
    {
        $user = new User();
        $user->id = $id;
        $user->username = $username;
        $user->password = $password;
        $user->email = $email;
        $user->bio = $bio;
        $user->isAdmin = $isAdmin;

        return $user;
    }

    static function makeEmpty()
    {
        return new User();
    }

    /**
     * Insert or update a user object to db.
     */
    function save()
    {
        if ($this->id === null) {
            $query = sprintf(self::INSERT_QUERY,
                $this->username,
                $this->password,
                $this->email,
                $this->bio,
                $this->isAdmin            );
        } else {
          $query = sprintf(self::UPDATE_QUERY,
                $this->username,
                $this->password,
                $this->email,
                $this->bio,
                $this->isAdmin,
                $this->id
            );
        }

        return self::$app->db->exec($query);
    }

    function delete()
    {
        $query = sprintf(self::DELETE_QUERY,
            $this->id
        );
        return self::$app->db->exec($query);
    }

    function getId()
    {
        return $this->id;
    }

    function getUsername()
    {
        return $this->username;
    }

    function getPassword()
    {
        return $this->password;
    }

    function getEmail()
    {
        return $this->email;
    }

    function getBio()
    {
        return $this->bio;
    }

    function isAdmin()
    {
        return $this->isAdmin === "1";
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setUsername($username)
    {
        $this->username = $username;
    }

    function setPassword($password)
    {
        $this->password = $password;
    }

    function setEmail($email)
    {
        $this->email = $email;
    }

    function setBio($bio)
    {
        $this->bio = $bio;
    }
    function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }


    /**
     * Get user in db by userid
     *
     * @param string $userid
     * @return mixed User or null if not found.
     */
    static function findById($userid)
    {
        $query = sprintf(self::FIND_BY_ID_QUERY, $userid);
        $result = self::$app->db->query($query, \PDO::FETCH_ASSOC);
        $row = $result->fetch();

        if($row == false) {
            return null;
        }

        return User::makeFromSql($row);
    }

    /**
     * Find user in db by username.
     *
     * @param string $username
     * @return mixed User or null if not found.
     */
    static function findByUser($username)
    {
        $query = sprintf(self::FIND_BY_NAME_QUERY, $username);
        $result = self::$app->db->query($query, \PDO::FETCH_ASSOC);
        $row = $result->fetch();

        if($row == false) {
            return null;
        }

        return User::makeFromSql($row);
    }

    
    static function all()
    {
        $query = "SELECT * FROM users";
        $results = self::$app->db->query($query);

        $users = [];

        foreach ($results as $row) {
            $user = User::makeFromSql($row);
            array_push($users, $user);
        }

        return $users;
    }

    static function makeFromSql($row)
    {
        return User::make(
            $row['id'],
            $row['username'],
            $row['password'],
            $row['email'],
            $row['bio'],
            $row['isadmin']
        );
    }

}


  User::$app = \Slim\Slim::getInstance();

