<?php

class MainModel {
    
    public $home;
    
    public $siteurl;
    
    public $title;
    
    public $desc;
    
    private $con;
    
    private $prefix;
    
    public function __construct(){
        $db = new DatabaseModel;
        $this->con = $db->con;
        $this->prefix = $db->prefix;
        $this->home = $this->fetchHome();
        $this->siteurl = $this->fetchSiteUrl();
        $this->title = $this->fetchTitle();
        $this->desc =  $this->fetchDesc();
    }
    
    public function updateData(array $data){
        $result = [];
        if (isset($data['home']) && $data['home'] !== $this->home){
            $queries[] = "update `".$this->prefix."options` set `option_value`='".$data['home']."' where `".$this->prefix."options`.`option_name`='home'";
            $result['home']['old'] = $this->home;
            $result['home']['new'] = $data['home'];
        }
        if (isset($data['siteurl']) && $data['siteurl'] !== $this->siteurl){
            $queries[] = "update `".$this->prefix."options` set `option_value`='".$data['siteurl']."' where `".$this->prefix."options`.`option_name`='siteurl'";
            $result['siteurl']['old'] = $this->siteurl;
            $result['siteurl']['new'] = $data['siteurl'];
        }
        if (isset($data['title']) && $data['title'] !== $this->title){
            $queries[] = "update `".$this->prefix."options` set `option_value`='".$data['title']."' where `".$this->prefix."options`.`option_name`='blogname'";
            $result['title']['old'] = $this->title;
            $result['title']['new'] = $data['title'];
        }
        if (isset($data['desc']) && $data['desc'] !== $this->desc){
            $queries[] = "update `".$this->prefix."options` set `option_value`='".$data['desc']."' where `".$this->prefix."options`.`option_name`='blogdescription'";
            $result['desc']['old'] = $this->desc;
            $result['desc']['new'] = $data['desc'];
        }
        foreach($queries as $query){
            $this->con->query($query);
        }
        
        return $result;
    }
    
    private function fetchHome(){
        $query = "SELECT * FROM ".$this->prefix."options where option_name = 'home'";
        $result = $this->con->query($query);
        while($resultArray = mysqli_fetch_array($result)){
            $home = $resultArray['option_value'];
        }
        
        return $home;
    }
    
    private function fetchSiteUrl(){
        $query = "SELECT * FROM ".$this->prefix."options where option_name = 'siteurl'";
        $result = $this->con->query($query);
        while($resultArray = mysqli_fetch_array($result)){
            $siteurl = $resultArray['option_value'];
        }
        
        return $siteurl;
    }
    
    private function fetchTitle(){
        $query = "SELECT * FROM ".$this->prefix."options where option_name = 'blogname'";
        $result = $this->con->query($query);
        while($resultArray = mysqli_fetch_array($result)){
            $title = $resultArray['option_value'];
        }
        
        return $title;
    }
    
    private function fetchDesc(){
        $query = "SELECT * FROM ".$this->prefix."options where option_name = 'blogdescription'";
        $result = $this->con->query($query);
        while($resultArray = mysqli_fetch_array($result)){
            $desc = $resultArray['option_value'];
        }
        
        return $desc;
    }
    
}