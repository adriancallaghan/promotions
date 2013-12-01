<?php
namespace Rest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Model\Zone;
use Application\Model\Art;
use Application\Model\Like;
use \Exception;


class ArtController extends AbstractActionController
{
    
    
    
    public function listAction()
    {
        /*
         * lists by 
         * 
         * featured
         * likes
         * views
         * latest (default)
         * 
         * mapped in routes
         */

        // in
        $limit      = $this->params()->fromQuery('limit', null);
        $offset     = $this->params()->fromQuery('offset', null);

        // out
        $success    = true;
        $errors     = array();
        $artwork    = array();
        
        // models
        $artTable   = $this->getServiceLocator()->get('Application\Model\ArtTable');
        $gallery    = array();
        
        // logic
        switch($this->params()->fromRoute('order', 'latest')){
            
            case 'featured':
                $service    = 'List Artwork by order of featured';
                $gallery    = $artTable->getArts(null,'featured DESC', $limit, $offset);
            break;

            case 'likes':               
                $service    = 'List Artwork by order of likes';
                $gallery    = $artTable->getArts(null,'likes DESC', $limit, $offset);
            break;
        
            case 'views':
                $service    = 'List Artwork by order of views';
                $gallery    = $artTable->getArts(null,'views DESC', $limit, $offset);
            break;
        
            case 'latest':
            default:
                $service    = 'List Artwork by order of latest';
                $gallery    = $artTable->getArts(null,'created DESC', $limit, $offset);
            break;
            
        }
        
        // format
        foreach ($gallery AS $art){
            $artwork[] = $art->toArray();
        }
        
        // respond
        return new JsonModel(array(   
            'service name'  => $service,
            'success'       => $success,
            'errors'        => $errors,            
            'artwork'       => $artwork,            
        ));
        

    }
        
    public function getAction()
    {

        /*
         * All artwork based on a set of id`s - delimited by : 
         */

        $success    = true;
        $errors     = array();
        $artwork    = array();


        $ids        = is_null($ids = $this->params()->fromQuery('art_ids',null)) ? null : explode(':',$ids);
        $limit      = $this->params()->fromQuery('limit', null);
        $offset     = $this->params()->fromQuery('offset', null);
        
        $artTable   = $this->getServiceLocator()->get('Application\Model\ArtTable');
        $gallery    = $artTable->getArts($ids,null,$limit,$offset);

        foreach ($gallery AS $art){
            $artwork[] = $art->toArray();
        }

        return new JsonModel(array(   
            'service name'  => 'Get Artwork',
            'success'       => $success,
            'errors'        => $errors,            
            'artwork'       => $artwork,            
        ));
        
        
    }
    
    public function setAction()
    {                                      
        /*
         * sets artwork 
         */

        /*
         * TEST
         * 
         * curl -X POST --data "lon=-0.126557&lat=51.513871&zone_ref=130979-87163-18" 
         * http://www.streetart.adobe.localhost/rest/art/set?user_email=adrian@lessrain.net
         * 
         */
        
        /*if (!$this->getRequest()->isPost()){
        ?>
        <form name="upload-form"    id="upload-form"        method="post" enctype="multipart/form-data">        
            <input type="file"      name="image_final"      id="image_final">
            <input type="file"      name="image_tag"        id="image_tag">
            <input type="file"      name="image_background" id="image_background">
            <input type="file"      name="image_thumb"      id="image_thumb">
            <input type="hidden"    name="data"             id="data" value="some encrypted stuff">
            <input type="hidden"    name="lon"              id="lon" value="0.126557">            
            <input type="hidden"    name="lat"              id="lat" value="51.513871">            
            <input type="hidden"    name="zone_ref"         id="zone_ref" value="130979-87163-18">            
            <input type="submit">
        </form>
        <?php die; } */
        
        
        ////////////////////////////////
        // returned values
        $success        = false;
        $errors         = array();

        
        ////////////////////////////////
        // enviroment
        $art            = new Art();
        $sm             = $this->getServiceLocator();
        $request        = $this->getRequest();
        $sysLog         = $this->getServiceLocator()->get('SystemLogger');
        $userTable      = $sm->get('Application\Model\UserTable');
        $zoneTable      = $sm->get('Application\Model\ZoneTable');
        $artTable       = $sm->get('Application\Model\ArtTable');                
        $artInputFilter = $art->getInputFilter(); // LOVE the way we can do this in ZF2!!!!       
        $artInputFilter->setData($request->getPost()); // additional things are set later on

        
        
        
        
        ////////////////////////////////
        // validation 
        
        // user
        if(!$user = $userTable->getUserByIdentityToken($this->params()->fromQuery('identity_token',''))) {    
            $errors['user'] = 'unknown';                 
        } 

        // zone
        if (!$zoneRef = $this->params()->fromPost('zone_ref',false)){
            $errors['submission'] = 'invalid';            
        } elseif (!$zone = $zoneTable->findZone($zoneRef)){
                
            // If the zone was not found, it probably does'nt exist yet
            // attempt to create it, this in turn enforces the zone validation
            // if an exception is thrown then we have an invalid zone ref (submission)
            try {              
                $zone       = new Zone(array('ref'=>$zoneRef));
                $zoneTable->save($zone); 
                $artInputFilter->get('zone')->setValue($zone->id);
            } catch(Exception $e){               
                $sysLog->alert('Zone could not be created with ref: '.$zoneRef.' the following error was encountered: '.$e->getMessage());
                $errors['submission'] = 'invalid'; 
                
            }
        
        } 
        
        
        
        /*
         * Image 
         * 
         * it uploads the images and adds them to the input filter 
         * everything is handled the same way until it hits the switch statement
         */      
        $path = './public_html/'. Art::uploadDir;
        $validator = new \Zend\Validator\File\MimeType(array('image/jpg','image/jpeg','enableHeaderCheck'=>true));
        
        foreach ($request->getFiles() AS $field=>$fileDetails){

            $sysLog->debug('Upload image > art image > name: '.$fileDetails['name']);
            $imageName = "art_".uniqid().".jpg"; // FILENAME
            $filter = new \Zend\Filter\File\RenameUpload("$path/$imageName"); // DESTINATION

            if((int) $fileDetails['error'] > 0){  
                $errors['server'] = 'error'; 
                $sysLog->alert("Upload error > art image > $field > code: ".$fileDetails['error']);
                continue;
            }

            // SWITCH VALIDATION
            if (!$validator->isValid($fileDetails['tmp_name'])) {                     
                $errors['submission'] = 'invalid';                          
                $sysLog->warn("Upload error > art image > $field > Unsupported image format ".$fileDetails['tmp_name']);
            } else {                                                  
                   switch($field){       
                       
                        // main resource
                        case 'image_final':                    
                            $filter->filter($fileDetails['tmp_name']);
                            $artInputFilter->get('image_final')->setValue($imageName);                    
                            break;

                       // scaled to different sizes (image will be rescaled here)
                       case 'image_thumb':                    
                            $filter->filter($fileDetails['tmp_name']);
                            $artInputFilter->get('image_thumb')->setValue($imageName);                    
                            break;
                        
                        // stored as data
                        case 'image_tag':                    
                            $filter->filter($fileDetails['tmp_name']);
                            $artInputFilter->get('image_tag')->setValue($imageName);                    
                            break;    
                        
                        // stored as data
                        case 'image_background':                    
                            $filter->filter($fileDetails['tmp_name']);
                            $artInputFilter->get('image_background')->setValue($imageName);                    
                            break;
                          
                    }
                    
                    $sysLog->debug('Uploaded image > art image > name: '.$imageName);
               }            
        }
        
        
        // validate all art fields including images
        if (!$artInputFilter->isValid()){            
            $errors['submission'] = 'invalid'; 
        }
        
        ////////////////////////////


        
        // if no errors, save and set the status
        if (empty($errors)){

            // base data
            $art->user              = $user;
            $art->zone              = $zone;
            $art->lon               = $artInputFilter->getValue('lon');
            $art->lat               = $artInputFilter->getValue('lat');
            $art->imageFinal        = $artInputFilter->getValue('image_final');
            $art->imageTag          = $artInputFilter->getValue('image_tag');
            $art->imageBackground   = $artInputFilter->getValue('image_background');
            $art->imageThumb        = $artInputFilter->getValue('image_thumb');
            $art->data              = $artInputFilter->getValue('data');            

            $artTable->saveArt($art);          
            $success        = true;
        }
        
    
         
        return new JsonModel(array(   
            'service name'  => 'Save Artwork',
            'success'       => $success,
            'errors'        => $errors,            
            'artwork_id'    => $art->id,            
        ));
        
    }
    
    public function likeAction()
    {

        /*
         * 
         * Like art
         * 
         */
        $success    = false;
        $errors     = array();
        
        $artTable   = $this->getServiceLocator()->get('Application\Model\ArtTable');
        $likeTable  = $this->getServiceLocator()->get('Application\Model\LikeTable');
        $userTable  = $this->getServiceLocator()->get('Application\Model\UserTable');
        
        $art        = $artTable->getArt($this->params()->fromPost('art_id',''));
        $user       = $userTable->getUserByIdentityToken($this->params()->fromPost('identity_token',''));
        
        
        if (!$art) {                                     
            $errors['art']  = 'unknown';
        } 
        
        if(!$user) {
            $errors['user'] = 'unknown';
        }         
        
        if ($user && $art && $likeTable->likeExists($user,$art)){
            $errors['submission'] = 'error';        
        }
        
        if(empty($errors)) {       
            
            $like = new Like();
            $like->art = $art;
            $like->user = $user;
            
            $likeTable->saveLike($like);
            $likes = $likeTable->getLikesForArt($art);
            
            $art->likes = (int) count($likes);             
            $artTable->saveArt($art);    // save
            
            $success        = true;            
        }
         
        return new JsonModel(array(   
            'service name'  => 'Art like',
            'success'       => $success,
            'errors'        => $errors,            
        ));
        
    }
        
    public function unlikeAction()
    {

        /*
         * 
         * Like art
         * 
         */
        $success    = false;
        $errors     = array();
        
        $artTable   = $this->getServiceLocator()->get('Application\Model\ArtTable');
        $likeTable  = $this->getServiceLocator()->get('Application\Model\LikeTable');
        $userTable  = $this->getServiceLocator()->get('Application\Model\UserTable');
        
        $art        = $artTable->getArt($this->params()->fromPost('art_id',''));
        $user       = $userTable->getUserByIdentityToken($this->params()->fromPost('identity_token',''));
        
        
        if (!$art) {                                     
            $errors['art']  = 'unknown';
        } 
        
        if(!$user) {
            $errors['user'] = 'unknown';
        }         
        
        if ($user && $art && !$likeTable->likeExists($user,$art)){
            $errors['submission'] = 'error';        
        }
        
        if(empty($errors)) {       

            $rowset = $likeTable->fetchAll(array('art'=>$art->id,'user'=>$user->id));
            
            $likeTable->deleteLike($rowset->current()->id);
            $likes = $likeTable->getLikesForArt($art);
            
            $art->likes = (int) count($likes);             
            $artTable->saveArt($art);    // save
            
            $success        = true;            
        }
         
        return new JsonModel(array(   
            'service name'  => 'Art unlike',
            'success'       => $success,
            'errors'        => $errors,            
        ));
        
    }
    
    public function viewAction()
    {

        /*
         * 
         * Increments the view counter for art
         * 
         */
        $success    = false;
        $errors     = array();
        
        $artTable   = $this->getServiceLocator()->get('Application\Model\ArtTable');
        $art        = $artTable->getArt($this->params()->fromPost('art_id',''));
        
        if (!$art) {                                     
            $errors['art'] = 'unknown';
        } else {
            $art->views++;   
            $artTable->saveArt($art);
            $success        = true;            
        }
         
        return new JsonModel(array(   
            'service name'  => 'View counter incrementor',
            'success'       => $success,
            'errors'        => $errors,            
        ));
        
        
    }

}