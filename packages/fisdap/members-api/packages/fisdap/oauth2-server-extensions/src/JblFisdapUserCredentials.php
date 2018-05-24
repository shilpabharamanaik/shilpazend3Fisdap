<?php namespace Fisdap\OAuth;

use Fisdap\JBL\Authentication\Contracts\EmailPasswordAuthenticator;
use Fisdap\JBL\Authentication\Exceptions\RequestException;
use OAuth2\Storage\UserCredentialsInterface;
use PDO;
use Fisdap\Doctrine\Extensions\ColumnType\UuidType;

/**
 * Class JblFisdapUserCredentials
 *
 * Wrap the fisdap user credentials to add ability to check for JBL users
 *
 * @package Fisdap\OAuth
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class JblFisdapUserCredentials implements UserCredentialsInterface
{
    /**
     * @var null|PDO
     */
    protected  $pdo = null;

    /**
     * @var FisdapUserCredentials
     */
    private $fisdapUserCredentials;

    /**
     * @var EmailPasswordAuthenticator
     */
    private $jblAuthenticator;

    /**
     * JBL user uuid
     *
     * @var null|string
     */
    private $jblUserUuid = null;

    /**
     * JblFisdapUserCredentials constructor
     *
     * @param PDO $pdo
     * @param FisdapUserCredentials $fisdapUserCredentials
     * @param EmailPasswordAuthenticator $jblAuthenticator
     */
    public function __construct
    (
        PDO $pdo,
        FisdapUserCredentials $fisdapUserCredentials,
        EmailPasswordAuthenticator $jblAuthenticator
    )
    {
        $this->pdo = $pdo;
        $this->fisdapUserCredentials = $fisdapUserCredentials;
        $this->jblAuthenticator = $jblAuthenticator;
    }

    /**
     * Check user credentials
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function checkUserCredentials($username, $password)
    {
        $fisdapUser = $this->fisdapUserCredentials->checkUserCredentials($username, $password);

        if (!$fisdapUser) {
            try {
                // We need to check PSG to see if they have an email/password combination.
                $psgUser = $this->jblAuthenticator->authenticateWithEmailPassword($username, $password);

                $this->jblUserUuid = hex2bin(UuidType::transposeUuid($psgUser->PersonId));

                // if they do, we need to check the database again to see if the psg_user_id exists for that user
                $sth = $this->pdo->prepare("SELECT * FROM fisdap2_users WHERE psg_user_id = :un");
                $sth->execute([':un' => $this->jblUserUuid]);

                $user = $sth->fetch();

                if ($user !== false) {
                    return true;
                }
            } catch (RequestException $e) {
                return false;
            }
        }

        return $fisdapUser;
    }

    /**
     * Get fisdap or jbl user details
     *
     * @param $username
     * @return array|bool
     * @throws \Exception
     */
    public function getUserDetails($username)
    {
        $fisdapUserDetails = $this->fisdapUserCredentials->getUserDetails($username);

        if (!$fisdapUserDetails) {

            if (is_null($this->jblUserUuid)) {
                throw new \Exception('JBL User UUID is not set in getUserDetails');
            }

            // Grab the user record for the attempted login by using psg_user_id
            $sth = $this->pdo->prepare("SELECT id FROM fisdap2_users WHERE psg_user_id = :un");
            $sth->execute([':un' => $this->jblUserUuid]);

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

        return $fisdapUserDetails;
    }
}
