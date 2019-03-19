<?php

namespace ttm4135\webapp\models;

class User
{
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
            $INSERT_QUERY = self::$app->db->prepare("INSERT INTO users (username, password, email, bio, isadmin) VALUES (:username, :password, :email, :bio, :isadmin)");

            $INSERT_QUERY->bindParam(':username', $this->username);
            $INSERT_QUERY->bindParam(':password', $this->password);
            $INSERT_QUERY->bindParam(':email', $this->email);
            $INSERT_QUERY->bindParam(':bio', $this->bio);
            $INSERT_QUERY->bindParam(':isadmin', $this->isAdmin);

            $INSERT_QUERY->execute();
	    
            return;

        } else {
            $UPDATE_QUERY = self::$app->db->prepare("UPDATE users SET username=:username, password=:password, email=:email, bio=:bio, isadmin=:isadmin WHERE id=:id");

            $UPDATE_QUERY->bindParam(':username', $this->username);
            $UPDATE_QUERY->bindParam(':password', $this->password);
            $UPDATE_QUERY->bindParam(':email', $this->email);
            $UPDATE_QUERY->bindParam(':bio', $this->bio);
            $UPDATE_QUERY->bindParam(':isadmin', $this->isAdmin);
            $UPDATE_QUERY->bindParam(':id', $this->id);

            $UPDATE_QUERY->execute();

            return;
        }
    }

    function delete()
    {
        $DELETE_QUERY = self::$app->db->prepare("DELETE FROM users WHERE id=:id");
        $DELETE_QUERY->bindParam(':id', $this->id);
        $DELETE_QUERY->execute();

        return;
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
        $FIND_BY_ID_QUERY = self::$app->db->prepare("SELECT * FROM users WHERE id=:id");
        $FIND_BY_ID_QUERY->bindParam(':id', $userid);
        $FIND_BY_ID_QUERY->execute();

        $row = $FIND_BY_ID_QUERY->fetch();

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
        $FIND_BY_NAME_QUERY = self::$app->db->prepare("SELECT * FROM users WHERE username=:username");
        $FIND_BY_NAME_QUERY->bindParam(':username', $username);
        $FIND_BY_NAME_QUERY->execute();

        $row = $FIND_BY_NAME_QUERY->fetch();

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

