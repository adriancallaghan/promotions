<?php
namespace Rest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Model\User;
use Application\Model\Art;
use Zend\Db\Sql\Sql;


class UserController extends AbstractActionController
{
    
        
    
    public function indexAction()
    {
        
        /*
         * List all users
         */ 
        $success    = true; 
        $errors     = array();
        $users      = array();
                
        
        $usersRaw   = $this->getServiceLocator()
                    ->get('Application\Model\UserTable')
                    ->fetchAll();
        
        
        foreach($usersRaw AS $userRaw){
            $users[] = $userRaw->toArray();
        }
        
        
        return new JsonModel(array(      
            'service name'  => 'User list',
            'success'       => $success,
            'errors'        => $errors,
            'users'         => $users,
        ));

    }

    public function getAction()
    {
        
        /*
         * get user
         * 
         * errors 
         * [user] = unknown
         * 
         */      
        $success    = false;   
        $errors     = array();        
        $userArray  = array();

        $sm         = $this->getServiceLocator();
        $userTable  = $sm->get('Application\Model\UserTable');        
        $email      = $this->params()->fromQuery('email',null);
        $userId     = $this->params()->fromQuery('user_id',null);
        $userToken  = $this->params()->fromQuery('identity_token',null);

        
        // precedency order is token, id then email
        if (!$user  = $userTable->getUserByIdentityToken($userToken)){
            if (!$user  = $userTable->getUser($userId)){
                $user   = $userTable->getUserByEmail($email);
            }
        }
        
        if (!$user) {                
            $errors['user'] = 'unknown';
        } else {
            $success    = true; 
            $userArray  = $user->toArray();
        }
 
        return new JsonModel(array(      
            'service name'  => 'Get User',
            'success'       => $success,
            'errors'        => $errors,
            'user'          => $userArray,
        ));
    }

    public function setAction()
    {
        /*
         * set user
         * 
         * errors 
         * [email] = taken
         * [username] = taken
         * [submission] = invalid
         * 
         */
        $success        = false;
        $errors         = array();
        $userId         = 0;
        $identityToken  = 0;
        $emailed        = false;
        

        $request    = $this->getRequest();
        $user       = new User();

        $input = $user->getInputFilter(); // LOVE the way we can do this in ZF2!!!!
        $input->setData($request->getPost());
        $userTable = $this->getServiceLocator()->get('Application\Model\UserTable');
                        
        // validate submission
        if ($request->isPost() && $input->isValid()) {               
            if ($userTable->getUserByEmail($input->getValue('email'))){
                $errors['email']            = 'taken';
            } 
            if ($userTable->getUserByUsername($input->getValue('username'))){
                $errors['username']         = 'taken';
            } 
        } else {
            //$errors = array_merge($errors, $input->getMessages());   
            $errors['submission']           = 'invalid';  
        }
         
        // process
        if (empty($errors)){

            $pass = User::preparePassword($input->getValue('password'));
            $input->get('password')->setValue($pass);
            
            $user->exchangeArray($input->getValues());   
            $userTable->saveUser($user);    
            
            // generates token url (absolute path for email)
            $tokenUrl = $request->getUri()->getScheme().'://'.
                $request->getUri()->getHost().
                $this->url()->fromRoute(
                        'confirmations',array(
                            'token'=>$user->confirmationToken
                            )
                        );
            
            // mailer
            $mailer = $this->getServiceLocator()->get('Mailer');
            $mailer->setTemplate('register');
            $mailer->setFilters(array(
                '<LINK>'=>$tokenUrl,
                '<NAME>'=>ucfirst($user->username),
                ));
            $mailer->send($user);

            if ($mailer->hasErrors()){
                $errors = array_merge($errors, $mailer->getErrors());
            }
            
            $success        = true;
            $userId         = $user->id; 
            $identityToken  = $user->identityToken;             
            $emailed        = !$mailer->hasErrors();            
        }



        return new JsonModel(array(   
            'service name'      => 'Set User',
            'success'           => $success,
            'errors'            => $errors,            
            'user_id'           => $userId,
            'identity_token'    => $identityToken,
            'emailed'           => $emailed,
        ));
        
    }
    
    public function getIdentityTokenAction(){
        
        /*
         * Get user identity token from email & password pair
         */
        
        $success    = false;
        $errors     = array();
        $idToken    = 0;
        $userArray  = array();
        
        $password   = $this->params()->fromPost('password',false);
        $email      = $this->params()->fromPost('email',false);                
        $userTable  = $this->getServiceLocator()->get('Application\Model\UserTable');       
        
        if (!$email || !$password){
            $errors['submission'] = 'invalid';
        }
        
        
        if (empty($errors)){
            
            if (!$user = $userTable->fetchAll(array(        
                'email'=>$email,
                'password'=>User::preparePassword($password)
                ))->current()){            
                
                $errors['user'] = 'unknown';
                
            } else {
                $success    = true;
                $userArray  = $user->toArray();
                $idToken    = $user->identityToken;
            }
            
        }
  
        return new JsonModel(array(   
            'service name'      => 'Get Identity Token',
            'success'           => $success,
            'errors'            => $errors,            
            'user'              => $userArray,            
            'identity_token'    => $idToken,
        ));
        
    }
    
    
    
    public function updateAction()
    {

        /*
         * Update user
         */
        /*
        if (!$this->getRequest()->isPost()){ ?>
        <form name="upload-form" id="upload-form" method="post" enctype="multipart/form-data">        
            <input type="file" name="image" id="image">
            <input type="hidden" name="username" id="username" value="username">            
            <input type="hidden" name="location" id="location" value="location">
            <!--input type="hidden" name="password" id="password" value="ABC"-->            
            <input type="submit">
        </form>
        <?php die; } */
        
        
        $success    = false;
        $errors     = array();
        $userId     = 0;

        $request    = $this->getRequest();
        $sysLogger  = $this->getServiceLocator()->get('SystemLogger');
        $userTable  = $this->getServiceLocator()->get('Application\Model\UserTable');
        $user       = $userTable->getUserByIdentityToken($this->params()->fromQuery('identity_token',''));        
        $path       = './public_html/'.User::uploadDir;

        
        // not found user
        if(!$user) {    
            $errors['user'] = 'unknown';                 
        } else {

            /*
             * @@@ IMAGE UPLOAD @@@
             */
            if ($image = $request->getFiles('image')){

                $this->getServiceLocator()->get('SystemLogger')->debug('Upload image > user image > name: '.$image['name']);
                /*
                 * Errors here bubble up to the main evaluation at runtime
                 */
                if((int) $image['error'] > 0){                    
                    $errors['server'] = 'error';                     
                    $sysLogger->alert('Upload error > user image > code: '.$image['error']);
                } else {

                    $validator = new \Zend\Validator\File\MimeType(array('image/jpg','image/jpeg','enableHeaderCheck'=>true));

                    if (!$validator->isValid($image['tmp_name'])) { 
                        $errors['submission'] = 'invalid';                          
                        $sysLogger->debug('Upload error > user image > invalid format');
                    } else {                    
                        $imageName = "usr_".uniqid().".jpg";
                        $filter = new \Zend\Filter\File\RenameUpload("$path/$imageName");
                        $filter->filter($image['tmp_name']);                        
                        $user->setImage($imageName); // modify image, and pass to validator
                    }
                }
            }
            /*
             * @@@ IMAGE UPLOAD @@@
             */

            
            $input = $user->getInputFilter(); // LOVE the way we can do this in ZF2!!!!
            $input->setData(                
                array(            
                    'email'     => $user->email, 
                    'username'  => $this->params()->fromPost('username',$user->username),                      
                    'location'  => $this->params()->fromPost('location',$user->location),        
                    'password'  => $user->password,        
                    'image'     => $user->image, // this is modified above if it uploaded         
                ));
            
            // change password? add it to the filter
            if ($pass = $this->params()->fromPost('password',false)){
                $input->get('password')->setValue(User::preparePassword($pass));      
            }
            
            if(!$input->isValid()) {            
                //$errors = array_merge($errors, $input->getMessages());  
                $errors['submission'] = 'invalid';  
            } 
        
        }
        

        // if no errors save the changes and set the status
        if (empty($errors)){
            
            /*
             * Its important to call setOptions and not exchange array on the update
             * 
             * this is because there is only partial data contained in the InputFilter, things like Id, and email_confirmed are not stored
             * 
             * using setOptions instead of exchangeArray, will add to the existing data set for the user, the values from the input filter
             */
            $user->setOptions($input->getValues());                 
            $userTable->saveUser($user); 
            $success    = true;
            $userId     = $user->id;            
            
        }
        
        
        
        return new JsonModel(array(   
            'service name'  => 'Update User',
            'success'       => $success,
            'errors'        => $errors,            
            'user_id'       => $userId,
        ));
        
        
    }
    
    public function remindAction()
    {
        /*
         * remind user
         */
   
        $success    = false;
        $errors     = array();
        
        $userTable  = $this->getServiceLocator()->get('Application\Model\UserTable');
        $user       = $userTable->getUserByIdentityToken($this->params()->fromPost('identity_token',''));

        // not found user
        if(!$user) {    
            $errors['user'] = 'unknown';            
        } else {        

            // generates token url (absolute path for email)
            $tokenUrl = $this->getRequest()->getUri()->getScheme().'://'.
                $this->getRequest()->getUri()->getHost().
                $this->url()->fromRoute(
                        'confirmations',array(
                            'token'=>$user->confirmationToken
                            )
                        );

            // mailer
            $mailer = $this->getServiceLocator()->get('Mailer');
            $mailer->setTemplate('register');
            $mailer->setFilters(array(
                '<LINK>'=>$tokenUrl,
                '<NAME>'=>ucfirst($user->username),
                ));
            $mailer->send($user);

            if ($mailer->hasErrors()){
                //$errors = array_merge($errors, $mailer->getErrors());
                $errors['server'] = 'error';
            } else {
                $success    = true;
            }
        }
            
        
        return new JsonModel(array(   
            'service name'  => 'Remind User',
            'success'       => $success,
            'errors'        => $errors,                      
        ));
        
    }
    
    public function resetAction()
    {
        /*
         * reset user
         */
          
        $success    = false;
        $errors     = array();
        
        $userTable  = $this->getServiceLocator()->get('Application\Model\UserTable');
        $user       = $userTable->getUserByEmail($this->params()->fromPost('email',''));

        // not found user
        if(!$user) {    
            $errors['user'] = 'unknown';                  
        } else {                 

            $newPassword = $user::generatePassword();
            $user->setPassword($user::preparePassword($newPassword));
            $userTable->saveUser($user);

            // mailer
            $mailer = $this->getServiceLocator()->get('Mailer');
            $mailer->setTemplate('reset');
            $mailer->setFilters(array(
                '<PASSWORD>'=>$newPassword,
                '<NAME>'=>ucfirst($user->username),
                ));
            $mailer->send($user);

            if ($mailer->hasErrors()){
                //$errors = array_merge($errors, $mailer->getErrors());
                $errors['server'] = 'error';
            } else {
                $success    = true;
            }

        }
            
                
        return new JsonModel(array(   
            'service name'  => 'Reset Password',
            'success'       => $success,
            'errors'        => $errors,                      
        ));
        
    }
    
    public function likesAction()
    {
        /*
         * return art user likes
         */
        $success    = false;   
        $errors     = array();        
        $artwork    = array();

        $sm             = $this->getServiceLocator();
        $dbAdapter      = $sm->get('Zend\Db\Adapter\Adapter');
        $userTable      = $sm->get('Application\Model\UserTable');   
        

        $email      = $this->params()->fromQuery('email',null);
        $userId     = $this->params()->fromQuery('user_id',null);
        $userToken  = $this->params()->fromQuery('identity_token',null);
        $limit      = $this->params()->fromQuery('limit', null);
        $offset     = $this->params()->fromQuery('offset', null);
        
        $gallery    = array();
        
        // precedency order is token, id then email
        if (!$user  = $userTable->getUserByIdentityToken($userToken)){
            if (!$user  = $userTable->getUser($userId)){
                $user   = $userTable->getUserByEmail($email);
            }
        }
        
        if (!$user) {                
            $errors['user'] = 'unknown';
        } else {
            
            $success    = true; 

            
            $sql = new Sql($dbAdapter);
            $select = $sql->select();
            $select->from(array('l' => 'like'))
                    ->join(array('a' => 'art'),'l.user = a.id')
                    ->where(array('l.user=?'=>$user->getId()))
                    ->order('l.created DESC');
            
            if (!is_null($limit)){
                $select->limit($limit);
            }
            
            if (!is_null($offset)){
                $select->limit($offset);
            }
            
            $selectString = $sql->getSqlStringForSqlObject($select);
            $rowset = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
            
            foreach($rowset AS $row){
                $art = new Art();
                $art->exchangeArray($row);
                $gallery[] = $art;
            }
     
        }
        
        // format
        foreach ($gallery AS $art){
            $artwork[] = $art->toArray();
        }

        return new JsonModel(array(   
            'service name'  => 'User Artwork',
            'success'       => $success,
            'errors'        => $errors,            
            'artwork'       => $artwork,            
        ));
        
    }
    
    public function artAction()
    {
        /*
         * list user art in different orders
         */
        $success    = false;
        $errors     = array();
        $artwork    = array();

        $email      = $this->params()->fromQuery('email',null);
        $userId     = $this->params()->fromQuery('user_id',null);
        $limit      = $this->params()->fromQuery('limit', null);
        $offset     = $this->params()->fromQuery('offset', null);
        $order      = $this->params()->fromRoute('order', null);
        
        $artTable   = $this->getServiceLocator()->get('Application\Model\ArtTable');
        $userTable  = $this->getServiceLocator()->get('Application\Model\UserTable');
        
        // id takes precedency over email
        if (!$user  = $userTable->getUser($userId)){
            $user   = $userTable->getUserByEmail($email);
        }

        $gallery    = array();
        
        // logic
        if (!$user){
            $errors['user'] = 'unknown';
        } else {
            
            switch($order){
                case 'likes': $orderClause = 'likes DESC'; break;
                case 'views': $orderClause = 'views DESC'; break;
                default: $orderClause = 'created DESC';
            }
            
            $gallery    = $artTable->getUserArt($user, $orderClause, $limit, $offset);   
            $success    = true;
        }
                
        // format
        foreach ($gallery AS $art){
            $artwork[] = $art->toArray();
        }

        return new JsonModel(array(   
            'service name'  => 'User Artwork',
            'success'       => $success,
            'errors'        => $errors,            
            'artwork'       => $artwork,            
        ));
        
        
    }
    
    
}