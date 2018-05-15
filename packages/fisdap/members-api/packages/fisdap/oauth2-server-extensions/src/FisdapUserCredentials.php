<?php namespace Fisdap\OAuth;

use PDO;
use OAuth2\Storage\UserCredentialsInterface;

/**
 * Class FisdapUserCredentials
 *
 * @package Fisdap\OAuth
 *
 * @todo   add user details - username, first name, last name, id, program_id
 * @author astevenson, bgetsug, nkarnick
 */
class FisdapUserCredentials implements UserCredentialsInterface
{

    /**
     * @var null|PDO
     */
    protected $pdo = null;


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    /**
     * Required for \OAuth2\Storage\UserCredentialsInterface
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function checkUserCredentials($username, $password)
    {
        // Grab the user record for the attempted login
        $sth = $this->pdo->prepare("SELECT * FROM fisdap2_users WHERE username = :un");
        $sth->execute([':un' => $username]);

        $user = $sth->fetch();

        if ($user !== false) {
            // Check the password here...
            $password = md5($password . $user['password_salt']);

            return $password == $user['password'];
        }

        return false;
    }


    /**
     * Required for \OAuth2\Storage\UserCredentialsInterface
     *
     * @param string $username
     *
     * @return array|bool
     */
    public function getUserDetails($username)
    {
        // Grab the user record for the attempted login
        $sth = $this->pdo->prepare("SELECT id FROM fisdap2_users WHERE username = :un");
        $sth->execute([':un' => $username]);

        $user = $sth->fetch();

        if ($user !== false) {
            // Determine the available scope here.
            $scope = 'api';

            $data = [
                'user_id' => $user['id'],
                'scope'   => $scope,
            ];

            return $data;
        } else {
            return false;
        }
    }


    /**
     * Custom function that gets FISDAP specific user details via user_id stored in an access_token
     *
     * @param integer $user_id ID of the user to fetch information for.
     *
     * @return array con
     */
    public function getUserDetailsById($user_id)
    {
        // Grab the user record for the attempted login
        $sth = $this->pdo->prepare("SELECT u.* ,id.Instructor_id FROM fisdap2_users u LEFT JOIN InstructorData id ON id.user_id = u.id WHERE id = :id");
        $sth->execute([':id' => $user_id]);

        $user = $sth->fetch();

        if ($user !== false) {
            $data = [
                'id'         => $user['id'],
                'username'   => $user['username'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'instructor' => ($user['Instructor_id'] == null ? false : true),
                'email'      => $user['email'],
                'city'       => $user['city'],
                'state'      => $user['state'],
            ];

            return $data;
        } else {
            return false;
        }
    }

    /** 
     * Custom function that gets FISDAP specific user details via a provided email address 
     *
     * @param $email  
     * 
     * @return array|bool 
     */
    public function getUsersByEmail($email)
    {
        $sth = $this->pdo->prepare("SELECT u.* FROM fisdap2_users u WHERE u.email = :email");
        $sth->execute([':email' => $email]);
        
        $userRecords = $sth->fetchAll();

        if ($userRecords !== false) {
            $rtv = array();

            foreach ($userRecords as $user) {
                $data = [
                    'id'         => $user['id'],
                    'username'   => $user['username'],
                    'first_name' => $user['first_name'],
                    'last_name'  => $user['last_name'],
                    'email'      => $user['email'],
                ];

                array_push($rtv, $data);
            }

            return $rtv;
        } else {
            return false;
        }
    }
}
