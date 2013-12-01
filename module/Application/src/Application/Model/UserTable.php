<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class UserTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll(array $arguments = null)
    {
        $resultSet = $this->tableGateway->select($arguments);
        return $resultSet;
    }

    public function getUserByUsername($username)
    {
        $rowset = $this->tableGateway->select(array('username = ?' => $username));
        $row = $rowset->current();
        return $row;
    }
    
    public function getUserByEmail($email)
    {
        $rowset = $this->tableGateway->select(array('email = ?' => $email));
        $row = $rowset->current();
        return $row;
    }
    
    public function getUserByIdentityToken($token)
    {
        $rowset = $this->tableGateway->select(array('identity_token = ?' => $token));
        $row = $rowset->current();
        return $row;
    }
    
    
    public function getUserByConfirmationToken($token)
    {
        $rowset = $this->tableGateway->select(array('confirmation_token = ?' => $token));
        $row = $rowset->current();
        return $row;
    }
    
    public function getUser($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        return $row;
    }
    
    public function saveUser(User $user)
    {
        $data = array(
            'identity_token'        => $user->identityToken,
            'email'                 => $user->email,
            'password'              => $user->password,
            'username'              => $user->username,
            'location'              => $user->location,
            'image'                 => $user->image,
            'confirmed_email'       => $user->confirmedEmail,
            'confirmation_token'    => $user->confirmationToken,
        );

        $id = (int)$user->id;
        
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $user->id = $this->tableGateway->lastInsertValue;
        } else {
            $this->tableGateway->update($data, array('id' => $id));            
        }
    }

    public function deleteUser($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}