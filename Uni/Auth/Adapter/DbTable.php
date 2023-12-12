<?php
namespace Uni\Auth\Adapter;

use Tk\Auth\Result;

/**
 * A DB table authenticator adaptor
 *
 * This adapter requires that the data values have been set
 * ```
 * $adapter->replace(array('username' => $value, 'password' => $password));
 * ```
 *
 */
class DbTable extends \Tk\Auth\Adapter\DbTable
{

    /**
     * @var \Uni\Db\Institution
     */
    protected $institution = null;

    /**
     * @param \Uni\Db\Institution $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }


    /**
     *
     * @return Result
     * @throws \Tk\Auth\Exception if answering the authentication query is impossible
     */
    public function authenticate()
    {
        $username = $this->get('username');
        $password = $this->get('password');

        if (!$username || !$password) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'No username or password.');
        }

        try {
            $iid = 0;
            if ($this->institution)
                $iid = $this->institution->getId();
            
            /* @var \Uni\Db\User $user */
            $user = \Uni\Config::getInstance()->getUserMapper()->findByUsername($username, $iid);

            if ($user && !$user->password) {
                throw new \Tk\Exception('Please validate your account first.');
            }
            if ($user && $this->hashPassword($password, $user) == $user->{$this->passwordColumn}) {
                return new Result(Result::SUCCESS, \Uni\Config::getInstance()->getUserIdentity($user));
            }
        } catch (\Exception $e) {
            \Tk\Log::debug($e->__toString());
            \Tk\Log::warning('The supplied parameters failed to produce a valid sql statement, please check table and column names for validity.');
            //throw new \Tk\Auth\Exception('Authentication server error.');
        }
        return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $username, 'Invalid username or password.');
    }


}
