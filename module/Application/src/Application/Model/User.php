<?php
namespace Application\Model;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 
use Application\Traits\ReadOnly;

class User implements InputFilterAwareInterface
{

    use ReadOnly {
        toArray as traitToArray;
    }

    protected $_inputFilter;
    protected $_id;
    protected $_identityToken;
    protected $_email;
    protected $_password;
    protected $_username;
    protected $_location;
    protected $_image;
    protected $_confirmedEmail;
    protected $_confirmationToken;
    protected $_created;

    const uploadDir = 'uploads'; // no trailing slash
    
    
    public function setId($id = 0){
        $this->_id = $id;
        return $this;
    }
    
    public function getId(){
        
        if (!isset($this->_id)){
            $this->setId();
        }
        return $this->_id;
    }   
     
    public function setIdentityToken($identityToken = null){
        
        if ($identityToken==null){
            $identityToken = uniqid();
        }
        $this->_identityToken = $identityToken;
        return $this;
    }
    
    public function getIdentityToken(){
        
        if (!isset($this->_identityToken)){
            $this->setIdentityToken();
        }
        return $this->_identityToken;
    }
    
    public function setEmail($email = ''){
        $this->_email = $email;
        return $this;
    }
    
    public function getEmail(){
        
        if (!isset($this->_email)){
            $this->setEmail();
        }
        return $this->_email;
    }  
    
    public function isPassword($password){
        if (self::preparePassword($password)==$this->password){
            return true;
        }
    }
    
    public function setPassword($password = 'le33rain'){
        $this->_password = $password;
        return $this;
    }
    
    public function getPassword(){
        
        if (!isset($this->_password)){
            $this->setPassword();
        }
        return $this->_password;
    }   
    
    public function setUsername($username = ''){
        $this->_username = $username;
        return $this;
    }
    
    public function getUsername(){
        
        if (!isset($this->_username)){
            $this->setUsername();
        }
        return $this->_username;
    }   
    
    public function setLocation($location = 'unknown'){
        $this->_location = $location;
        return $this;
    }
    
    public function getLocation(){
        
        if (!isset($this->_location)){
            $this->setLocation();
        }
        return $this->_location;
    }   
    
    public function setImage($image = 'user_default.jpg'){
        $this->_image = $image;
        return $this;
    }
    
    public function getImage(){
        
        if (!isset($this->_image)){
            $this->setImage();
        }
        return (string) $this->_image;
    }   
    
    public function setConfirmedEmail($confirmedEmail = false){
        $this->_confirmedEmail = $confirmedEmail;
        return $this;
    }
    
    public function getConfirmedEmail(){
        
        if (!isset($this->_confirmedEmail)){
            $this->setConfirmedEmail();
        }
        return $this->_confirmedEmail;
    }  
    
    public function setConfirmationToken($confirmationToken = null){
        
        if ($confirmationToken==null){
            $confirmationToken = uniqid();
        }
        $this->_confirmationToken = $confirmationToken;
        return $this;
    }
    
    public function getConfirmationToken(){
        
        if (!isset($this->_confirmationToken)){
            $this->setConfirmationToken();
        }
        return $this->_confirmationToken;
    } 
    
    public function setCreated($created = ''){
        $this->_created = $created;
        return $this;
    }
    
    public function getCreated(){
        
        if (!isset($this->_created)){
            $this->setCreated();
        }
        return $this->_created;
    } 

    public function exchangeArray($data)
    {
        /*
         * Differs from setOptions in that it defines EVERY field, resetting the object
         */
        $this->id                   = (isset($data['id'])) ? $data['id'] : null;
        $this->identityToken        = (isset($data['identity_token'])) ? $data['identity_token'] : null;
        $this->email                = (isset($data['email'])) ? $data['email'] : null;
        $this->password             = (isset($data['password'])) ? $data['password'] : null;
        $this->username             = (isset($data['username'])) ? $data['username'] : null;
        $this->location             = (isset($data['location'])) ? $data['location'] : null;
        $this->image                = (isset($data['image'])) ? $data['image'] : null;
        $this->confirmedEmail       = (isset($data['confirmed_email'])) ? $data['confirmed_email'] : null;
        $this->confirmationToken    = (isset($data['confirmation_token'])) ? $data['confirmation_token'] : null;
        $this->created              = (isset($data['created'])) ? $data['created'] : null;
    }

    public function setInputFilter(InputFilterInterface $inputFilter = null)
    {
        
        if ($inputFilter==null){
            
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            /*$inputFilter->add($factory->createInput(array(
                'name'       => 'id',
                'required'   => true,
                'filters' => array(
                    array('name'    => 'Int'),
                ),
            )));*/
                                   
            $inputFilter->add($factory->createInput(array(
                'name'     => 'email',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                    array(
                        'name'          => 'EmailAddress',
                        'options'               => array(
                            'allow'             => \Zend\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck'        => true,
                            'useDeepMxCheck'    => true
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'password',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'username',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'location',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'image',
                    'required' => false,
                ))
            );

            
        }
        
        $this->_inputFilter = $inputFilter;
        
        return $this;
    }

    public function getInputFilter()
    {
        
        if (!isset($this->_inputFilter)) {
            $this->setInputFilter();        
        }
        
        return $this->_inputFilter;
    } 
    
    public function toArray() {
        
        $array = $this->traitToArray();
        
        // some values should not be visible
        unset(
            $array['password'],
            $array['identityToken'],
            $array['confirmationToken'],
            $array['inputFilter']
            );

        // some values need changing a bit        
        $array['image'] = '/'.self::uploadDir.'/'.$array['image'];

        return $array;
        
    }
    
    
    public static function generatePassword($desired_length = 8){
        
        /*
         * generates a new user password
         */

        $password = '';
        
        while($desired_length>0){
            $password .= chr(rand(32, 126)); //Append a random ASCII character (including symbols)
            $desired_length--;
        }

        return $password;
    }
    
    public static function preparePassword($string){
        return md5($string);
    }
    
}
