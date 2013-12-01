<?php
namespace Application\Model;

use Application\Model\User;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail;
use \Application\Traits\ReadOnly;

class Mailer 
{
    
    use ReadOnly;
    

    protected $_subject;
    protected $_body;
    protected $_template;
    protected $_user;
    protected $_from;
    protected $_errors;
    protected $_filters;
    protected $_logger;
    
    
    public function __construct(){        
        
    }

    public function hasErrors(){
        return count($this->getErrors())>0 ? true : false;
    }
    
    public function getErrors(){
        
        if (!isset($this->_errors)){
            $this->setErrors();
        }
        return $this->_errors;
    }

    public function setErrors($errors = array()){
        $this->_errors = $errors;
        return $this;
    }  

    public function addError($error){
        
        $errors = $this->getErrors();
        $errors[] = $error;
        $this->setErrors($errors);
        return $this;
    }
    
    public function getUser(){
        
        if (!isset($this->_user)){
            $this->setUser();
        }
        return $this->_user;
    }

    protected function setUser(User $user = null){
        if ($user==null){
            $user = new User();
        }
        $this->_user = $user;
        return $this;
    }  

    public function getSubject(){
        
        if (!isset($this->_subject)){
            $this->setSubject();
        }
        return $this->_subject;
    }

    public function setSubject($subject = ''){
        $this->_subject = $subject;
        return $this;
    }    
    
    public function getFrom(){
        
        if (!isset($this->_from)){
            $this->setFrom();
        }
        return $this->_from;
    }

    public function setFrom($from = array('noreply@lessrain.co.uk'=>'lessrain')){
        $this->_from = $from;
        return $this;
    }
    
    public function getBody(){
        
        if (!isset($this->_body)){
            $this->setBody();
        }
        return $this->_body;
    }

    public function setBody($body = ''){
        $this->_body = $body;
        return $this;
    }
    
    public function getTemplate(){
        
        if (!isset($this->_template)){
            $this->setTemplate();
        }
        return $this->_template;
    }
    
    public function setTemplate($templateName = 'Undefined'){
        
        $this->_template = $templateName;
        
        switch ($this->_template){
            
            case 'register':
            case 'remind':
                $this->setSubject('Please confirm your email address');
                $this->setBody(
                   'Hello <NAME>'.
                   '<br /><br />'.
                   'Please verify your email address by clicking the following <a href="<LINK>">link</a>'.
                   '<br /><br />'.
                   'Thanks,'.
                   '<br /><br />'.
                   'StreetArt'
                   );
                break;
            case 'reset':
                $this->setSubject('Password reset');
                   $this->setBody(
                   'Hello <NAME>'.
                   '<br /><br />'.
                   'Your password has been reset to <PASSWORD>'.
                   '<br /><br />'.
                   'See you soon!,'.
                   '<br /><br />'.
                   'StreetArt'
                   );
                break;            
            default:
                // invalid template
                $this->addError('Email template not found');
                break;
        }
        
    }

    public function getCopy(){
        
        /*
         * Returns the copy, after passing through the filters
         */
        
        $copy = $this->getBody();
        
        foreach ($this->getFilters() AS $search=>$replace){
            $copy = str_replace($search,$replace, $copy);
        }

        return $copy;
    }
    
    public function getFilters(){
        
        if (!isset($this->_filters)){
            $this->setFilters();
        }
        return $this->_filters;
    }

    public function setFilters(array $filters = array()){
        $this->_filters = $filters;
        return $this;
    }
    
    public function send(User $user, $tester = 'adrian@lessrain.net'){
        
        /*
         * Main Method
         */
        $this->setUser($user);
        $copy = $this->getCopy();        
                
        
        $htmlPart = new MimePart($copy);
        $htmlPart->type = "text/html";

        $textPart = new MimePart(htmlentities(str_replace('<br />', "\r\n", $copy)));
        $textPart->type = "text/plain";

        $body = new MimeMessage();
        $body->setParts(array($textPart, $htmlPart));

        $message = new Message();
        $message->setFrom($this->getFrom());
        $message->addTo($this->getUser()->email);
        $message->addBcc($tester);
        $message->setSubject($this->getSubject());

        $message->setEncoding("UTF-8");
        $message->setBody($body);
        $message->getHeaders()->get('content-type')->setType('multipart/alternative');

        if (!$this->hasErrors()){
        
            try{
                $transport = new Sendmail();
                $transport->send($message);                 
            } catch (\Exception $e){ 
                $this->addError($e->getMessage());
            }
        }
        
        $this->log(); // log the outcome
    }
    
    
    public function Log(){       
        
        if ($this->getLogger()){
            
            $details = "/E:{$this->user->email}/T:{$this->template}/C:{$this->copy}";
            $logger = $this->getLogger();
            
            if ($this->hasErrors()){
                
                foreach($this->getErrors() AS $error){
                    $logger->alert($error.$details);
                }
                
            } else {                
                $logger->debug("No Errors".$details);
            }
        }
        
    }
    
    public function getLogger(){
        
        if (!isset($this->_logger)){
            $this->setLogger();
        }
        return $this->_logger;
    }

    public function setLogger($logger = null){
        $this->_logger = $logger;
        return $this;
    }
    
}
    

