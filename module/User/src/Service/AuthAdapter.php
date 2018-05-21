<?php
namespace User\Service;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use User\Entity\User;

/**
 * Adapter used for authenticating user. It takes login and password on input
 * and checks the database if there is a user with such login (email) and password.
 * If such user exists, the service returns its identity (email). The identity
 * is saved to session and can be retrieved later with Identity view helper provided
 * by ZF3.
 */
class AuthAdapter implements AdapterInterface
{
    /**
     * User email.
     * @var string
     */
    private $email;
    
    /**
     * Password
     * @var string
     */
    private $password;
    
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Constructor.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Sets user email.
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    /**
     * Sets password.
     */
    public function setPassword($password)
    {
        $this->password = (string)$password;
    }
    
    /**
     * Performs an authentication attempt.
     */
    public function authenticate()
    {
        $username = $this->username;
        
        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
        
        if ($user == null) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['Invalid credentials1.']
            );
        }
        
        $passwordHash = $user->getPassword();
        $hashedpassword = $user->hashedpassword($this->password, $user->getPasswordSalt());
        
        if ($hashedpassword == $passwordHash) {
            return new Result(
                    Result::SUCCESS,
                    $this->username,
                    ['Authenticated successfully.']
            );
        }
        
        // If password check didn't pass return 'Invalid Credential' failure status.
        return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                null,
                ['Invalid credentials.']
        );
    }
}
