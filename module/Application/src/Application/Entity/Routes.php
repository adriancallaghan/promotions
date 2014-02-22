<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/**
 * A music album.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks 
 * @ORM\Table(name="routes")
 * @property string $route
 * @property string $domain
 * @property string $template
 * @property string $from
 * @property string $to
 * @property string $created
 * @property string $signedoff
 * @property int $id
 */
class Routes implements InputFilterAwareInterface 
{
    
    use \Application\Traits\ReadOnly;
    
    
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(name="id",type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="route",type="string")
     */
    protected $route;

    /**
     * @ORM\Column(name="domain",type="string")
     */
    protected $domain;

    /**
     * @ORM\Column(name="template",type="string")
     */
    protected $template;
    
    /**
     * @ORM\Column(name="from", type="datetime")
     */
    protected $from;
    
    /**
     * @ORM\Column(name="to", type="datetime")
     */
    protected $to;
    
    /**
     * @ORM\Column(name="signedoff",type="integer")
     */
    protected $signedoff;
    
    /**
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;
        
    
 /*
    public function __construct(array $options = null) {
        
        $this->setComments(new \Doctrine\Common\Collections\ArrayCollection());
        
        return parent::__construct($options);
    }*/
    
    public function setId($id = 0){
        $this->id = $id;
        return $this;
    }
    
    public function getId(){
        
        if (!isset($this->id)){
            $this->setId();
        }
        return $this->id;
    }
    
    public function setRoute($route = ''){
        $this->route = $route;
        return $this;
    }
    
    public function getRoute(){
        
        if (!isset($this->route)){
            $this->setRoute();
        }
        return $this->route;
    }
    
    public function setDomain($domain = 'uk'){
        $this->domain = $domain;
        return $this;
    }
    
    public function getDomain(){
        
        if (!isset($this->domain)){
            $this->setDomain();
        }
        return $this->domain;
    }
    
    public function setTemplate($template = '2x4'){
        $this->template = $template;
        return $this;
    }
    
    public function getTemplate(){
        
        if (!isset($this->template)){
            $this->setTemplate();
        }
        return $this->template;
    }
    
    public function setSignedoff($signedoff = ''){
        $this->signedoff = $signedoff;
        return $this;
    }
    
    public function getSignedoff(){
        
        if (!isset($this->signedoff)){
            $this->setSignedoff();
        }
        return $this->signedoff;
    }
    
    public function setFrom(\DateTime $from = null){
        
        if ($from==null){
            $from = new \DateTime("now");
        }
        $this->from = $from;
        return $this;
    }
    
    public function getFrom(){
                
        if (!isset($this->from)){
            $this->setFrom();
        }
        return $this->from->format('Y-m-d H:i');
    }
    
    public function setTo(\DateTime $to = null){
        
        if ($to==null){
            $to = new \DateTime("now");
        }
        $this->to = $to;
        return $this;
    }
    
    public function getTo(){
                
        if (!isset($this->to)){
            $this->setTo();
        }
        return $this->to->format('Y-m-d H:i');
    }
    
    public function setCreated(\DateTime $created = null){
        
        if ($created==null){
            $created = new \DateTime("now");
        }
        $this->created = $created;
        return $this;
    }
    
    public function getCreated(){
                
        if (!isset($this->created)){
            $this->setCreated();
        }
        return $this->created->format('Y-m-d H:i');
    }
       
    
    /** 
    *  @ORM\PrePersist 
    */
    public function prePersist()
    {
        $this->getCreated(); // makes sure we have a default time set
    }
            
    
    public function setInputFilter(InputFilterInterface $inputFilter = null)
    {
        
        if ($inputFilter==null){
            
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'       => 'id',
                'required'   => true,
                'filters' => array(
                    array('name'    => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'artist',
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
                'name'     => 'title',
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
        }
        
        $this->inputFilter = $inputFilter;
        
        return $this;
    }

    public function getInputFilter()
    {
        
        if (!isset($this->inputFilter)) {
            $this->setInputFilter();        
        }
        
        return $this->inputFilter;
    } 
}