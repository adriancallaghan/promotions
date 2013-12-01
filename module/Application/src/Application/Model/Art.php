<?php
namespace Application\Model;

use Application\Model\Zone;
use Application\Model\User;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 
use \Application\Traits\ReadOnly;

class Art
{

    use ReadOnly {
        toArray as traitToArray;
    }

    protected $_inputFilter;
    protected $_id;
    protected $_created;
    protected $_user;
    protected $_views;    
    protected $_lat;
    protected $_lon;
    protected $_imageFinal;
    protected $_imageThumb;
    protected $_imageTag;
    protected $_imageBackground;
    protected $_data; 
    protected $_zone;
    protected $_likes;

    const uploadDir = 'uploads'; // no trailing slash
    
    public function setId($id = 0){
        $this->_id = (int) $id;
        return $this;
    }
    
    public function getId(){
        
        if (!isset($this->_id)){
            $this->setId();
        }
        return $this->_id;
    }

    public function setUser(User $user){
        $this->_user = $user;
        return $this;
    }
    
    public function getUser(){
        
        if (!isset($this->_user)){
            $this->setUser(new User());
        }
        return $this->_user;
    }
    
    public function setViews($views = 0){
        $this->_views = $views;
        return $this;
    }
    
    public function getViews(){
        
        if (!isset($this->_views)){
            $this->setViews();
        }
        return $this->_views;
    }
    
    public function setLikes($likes = 0){
        $this->_likes = $likes;
        return $this;
    }
    
    public function getLikes(){
        
        if (!isset($this->_likes)){
            $this->setLikes();
        }
        return $this->_likes;
    }
    
    public function setImageFinal($imageFinal = 'final_default.jpg'){
        $this->_imageFinal = $imageFinal;
        return $this;
    }
    
    public function getImageFinal(){
        
        if (!isset($this->_imageFinal)){
            $this->setImageFinal();
        }
        return $this->_imageFinal;
    }
    
    public function setImageThumb($imageThumb = 'thumb_default.jpg'){
        $this->_imageThumb = $imageThumb;
        return $this;
    }
    
    public function getImageThumb(){
        
        if (!isset($this->_imageThumb)){
            $this->setImageThumb();
        }
        return $this->_imageThumb;
    }   
    
    public function setImageTag($imageTag = 'tag_default.jpg'){
        $this->_imageTag = $imageTag;
        return $this;
    }
    
    public function getImageTag(){
        
        if (!isset($this->_imageTag)){
            $this->setImageTag();
        }
        return $this->_imageTag;
    } 
    
    public function setImageBackground($imageBackground = 'background_default.jpg'){
        $this->_imageBackground = $imageBackground;
        return $this;
    }
    
    public function getImageBackground(){
        
        if (!isset($this->_imageBackground)){
            $this->setImageBackground();
        }
        return $this->_imageBackground;
    } 
    
    public function setData($data = ''){
        $this->_data = $data;
        return $this;
    }
    
    public function getData(){
        
        if (!isset($this->_data)){
            $this->setData();
        }
        return $this->_data;
    } 
    
    public function setLat($lat = 0){
        $this->_lat = $lat;
        return $this;
    }
    
    public function getLat(){
        
        if (!isset($this->_lat)){
            $this->setLat();
        }
        return $this->_lat;
    }        
    
    public function setLon($lon = 0){
        $this->_lon = $lon;
        return $this;
    }
    
    public function getLon(){
        
        if (!isset($this->_lon)){
            $this->setLon();
        }
        return $this->_lon;
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
    
    public function setZone(Zone $zone){
        $this->_zone = $zone;
        return $this;
    }
    
    public function getZone(){
        
        if (!isset($this->_zone)){
            $this->setZone(new Zone());
        }
        return $this->_zone;
    } 

    
    public function exchangeArray($data)
    {
        $this->id               = (isset($data['id']))              ? $data['id'] : null;
        $this->user             = (isset($data['user']))            ? new User(array('id'=>$data['user'])) : null;
        $this->views            = (isset($data['views']))           ? $data['views'] : null;
        $this->likes            = (isset($data['likes']))           ? $data['likes'] : null;
        $this->lat              = (isset($data['lat']))             ? $data['lat'] : null;
        $this->lon              = (isset($data['lon']))             ? $data['lon'] : null;
        $this->created          = (isset($data['created']))         ? $data['created'] : null;
        $this->imageFinal       = (isset($data['image_final']))     ? $data['image_final'] : null;
        $this->imageTag         = (isset($data['image_tag']))       ? $data['image_tag'] : null;
        $this->imageBackground  = (isset($data['image_background']))? $data['image_background'] : null;
        $this->imageThumb       = (isset($data['image_thumb']))     ? $data['image_thumb'] : null;
        $this->data             = (isset($data['data']))            ? $data['data'] : null;
        $this->zone             = (isset($data['zone']))            ? new Zone(array('id'=>$data['zone'])) : null;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter = null)
    {
        
        if ($inputFilter==null){
            
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'user',
                'required' => false,
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
                            'max'      => 5,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'lon',
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
                            'max'      => 30,
                        ),
                    ),
                ),
            )));                    
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'lat',
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
                            'max'      => 30,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'zone',
                'required' => false,
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
                            'max'      => 5,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'image_final',
                    'required' => true,
                ))
            );
            
            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'image_tag',
                    'required' => true,
                ))
            );

            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'image_background',
                    'required' => true,
                ))
            );
            
            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'image_thumb',
                    'required' => true,
                ))
            );
            
            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'data',
                    'required' => true,
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
            $array['inputFilter'],
                $array['imageTag'],
                $array['imageBackground'],
                $array['data']
            );

        // some values need changing a bit        
        $array['imageFinal'] = '/'.self::uploadDir.'/'.$array['imageFinal'];
        $array['imageThumb'] = '/'.self::uploadDir.'/'.$array['imageThumb'];

        return $array;
        
    }
    
}
