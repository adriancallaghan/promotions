<?php

namespace Application\Model;

class Cluster {

    
    
    protected $_minDistance;
    protected $_markers;
    protected $_singleMarkers;
    protected $_clusterMarkers;
    
    public function getMinDistance(){
        
        if (!isset($this->_minDistance)){
            $this->setMinDistance();
        }
        
        return $this->_minDistance;
    }    
    
    protected function setMinDistance($minDistance = 1){
        
        $this->_minDistance = $minDistance;
        return $this;
    }
    
    public function getMarkers(){
        
        if (!isset($this->_markers)){
            $this->setMarkers();
        }
        
        return $this->_markers;
    }    
    
    protected function setMarkers(array $markers = array()){
        
        $this->_markers = $markers;
        return $this;
    }
    
    public function getSingleMarkers(){
        
        if (!isset($this->_singleMarkers)){
            $this->setSingleMarkers();
        }
        
        return $this->_singleMarkers;
    }    
    
    protected function setSingleMarkers(array $markers = array()){
        
        $this->_singleMarkers = $markers;
        return $this;
    }        
    
    public function getClusterMarkers(){
        
        if (!isset($this->_clusterMarkers)){
            $this->setClusterMarkers();
        }
        
        return $this->_clusterMarkers;
    }    
    
    protected function setClusterMarkers(array $markers = array()){
        
        $this->_clusterMarkers = $markers;
        return $this;
    }
        
    public function __construct(array $markers = null, $minDistance = null) {
              
        if (!is_null($minDistance)){
            $this->setMinDistance((int) $minDistance);
        }

        if (is_null($markers)){
            return;
        }
        
        $this->setMarkers($markers);
        
        $singleMarkers  = $this->getSingleMarkers();
        $clusterMarkers = $this->getClusterMarkers();

        // Minimum distance between markers to be included in a cluster - Zdenek: getting weird results with right bit shifting, using pow instead
        $DISTANCE = (10000000 / pow( 2, $this->getMinDistance() ) ) / 100000;
        //$DISTANCE = (10000000 >> $this->getMinDistance()) / 100000;

        //set minimum distance
        $minDistance = (10000000 / pow( 2, 300 ) ) / 100000;

        // not neccessary but in for readability
        $markers = $this->getMarkers();
        
        // Loop until all markers have been compared.
        while (count($markers)) {
            
            /*Zdenek: for every cluster, we'll need:
                - number of markers in cluster - 
                - position of the cluster - for now I simple do average of all positions of all markers within the cluster
                - bounding box of all markers withing cluster so we can than zoom in to see all markers within cluster
                - one image to represent the cluster - for now it's the most viewed 
                - for testing purpose add ids of markers in cluster 
            */

            $marker  = array_pop($markers);
            $cluster = array();

            //start Zdenek's stuff
            //vars for computing average location of each cluster
            $sumLat = 0;
            $sumLon = 0;
            $avgLat = 0;
            $avgLon = 0;
           
            //vars for computing bounding box every clusters - start with opposite maximum for each side
            $boundingBox = array( -90, -180, 90, 180 );

            //find a cluster image
            $maxViews = 0;
            $imageThumb = "";
            //store ids so that after clicking cluster individual artword details can be retrieved
            $ids = array();

            //can be cluster zoomed
            $distLat = 0;
            $distLon = 0;
            $zoomable = false;

            //end Zdenek's stuff

            // Compare against all markers which are left.
            foreach ($markers as $key => $target) {
                
                $pixels = abs($marker['lat']-$target['lat']) + abs($marker['lon']-$target['lon']);

                // If the two markers are closer than given distance remove target marker from array and add it to cluster.
                if ($pixels < $DISTANCE) {
                    unset($markers[$key]);
                    $cluster[] = $target;

                    //start Zdenek's stuff
                    //cache values as they are used multiple times
                    $lat = $target['lat'];
                    $lon = $target['lon'];
                    //increase sum of lat and lot with addition of every marker
                    $sumLat += $lat;
                    $sumLon += $lon;
                    //extend the bounding box to contain every marker
                    $boundingBox = $this->extendBoundingBox( $lat, $lon, $boundingBox );
                    //has the max views
                    if( $target['views'] > $maxViews || empty( $imageThumb ) ) {
                        $imageThumb = $target['image_thumb'];
                        $maxViews = $target['views'];
                    }

                    $ids[] = $target['id'];
                    $maxDistance = max( $pixels, $maxDistance );
                    //ends Zdenek's stuff
                } 
            }

            // If a marker has been added to cluster, add also the one we were comparing to.
            if (count($cluster) > 0) {
                $cluster[] = $marker;

                //start Zdenek's stuff - do the same process to marker to which we were comparing, duplication of code
                $lat = $marker['lat'];
                $lon = $marker['lon'];
                //increase sum of lat and lot with addition of every marker
                $sumLat += $lat;
                $sumLon += $lon;
                //extend the bounding box to contain every marker
                $boundingBox = $this->extendBoundingBox( $lat, $lon, $boundingBox );
                //has the max views
                if( $target['views'] > $maxViews || empty( $imageThumb ) ) {
                    $imageThumb = $marker['image_thumb'];
                    $maxViews = $marker['views'];
                }

                //get all info for final cluster             
                //get total of markers in cluster
                $numMarkers = count( $cluster );    
                //compute avarage location of marker
                $avgLat = $sumLat / $numMarkers;
                $avgLon = $sumLon / $numMarkers;

                $ids[] = $marker['id'];

                //can cluster be for available zoom levels ( up to 18 )
                $distLat = $boundingBox[0] - $boundingBox[2];
                $distLon = $boundingBox[1] - $boundingBox[3];

                if( $distLat > $minDistance || $distLon > $minDistance ) $zoomable = true;

                //return marker with information needed for cluster
                $clusterMarker = array( 
                                        'lat' => $avgLat, 
                                        'lon' => $avgLon, 
                                        'num_markers' => count( $cluster ), 
                                        'bounding_box' => $boundingBox, 
                                        'image_thumb' => $imageThumb,
                                        "ids" => $ids,
                                        "zoomable" => $zoomable
                                         );
                $clusterMarkers[] = $clusterMarker;
                //ends Zdenek's stuff

                //original bit commented
                //$clusterMarkers[] = $cluster;
            } else {
                $singleMarkers[] = $marker;
            }
        }
        
        $this->setSingleMarkers($singleMarkers);
        $this->setClusterMarkers($clusterMarkers);
        
    }

    protected function extendBoundingBox( $lat, $lon, $boundingBox ) {

        //top
        $boundingBox[0] = max( $boundingBox[0], $lat );
        //right
        $boundingBox[1] = max( $boundingBox[1], $lon );
        //bottom
        $boundingBox[2] = min( $boundingBox[2], $lat );
        //left
        $boundingBox[3] = min( $boundingBox[3], $lon );
        
        return $boundingBox;
    }
    
    public function toArray(){
        
        return array(
            'art'               =>  $this->getSingleMarkers(),
            'clusters'          =>  $this->getClusterMarkers(),
            'min-distance'      =>  $this->getMinDistance()
        );

    }
    
}

?>
