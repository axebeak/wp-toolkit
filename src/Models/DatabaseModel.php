<?php

class DatabaseModel {
    
    public $con;
    
    public $prefix;
    
    public $details;
    
    private $config;
    
    private $tmpConfig;

    public function __construct($details = false){
        $this->config = WP_DIR.'/wp-config.php';
        $this->tmpConfig = TOOLKIT.'/tmp/config.json';
        if ($details && is_array($details)){
            $this->details = $details;
            $this->putConfig($this->details);
        } else {
            $this->getDetails();
        }
    }
    
    public function check(){
        $response = ['success' => true, 'errors' => []];
        if (!$this->checkArrayKeys()){
            $response['success'] = false;
            array_push($response['errors'], 'keys');
            $response['keys'] = $this->checkArrayKeys();
        }
        if (is_array($this->checkDbArray())){
            $response['success'] = false;
            array_push($response['errors'], 'empty');
            $response['empty'] = $this->checkDbArray();
        }
        if (is_array($this->testConnection())){
            $response['success'] = false;
            array_push($response['errors'], 'connection');
            $response['connection'] = $this->testConnection();
        }

        return $response;
    }
    
    private function getDetails(){
        if (file_exists($this->tmpConfig)){
            if ($this->getDetailsTmp()){
                $this->details = $this->getDetailsTmp();
                $this->defineCon();
                $this->prefix = $this->details['prefix'];
                if (!is_array($this->testConnection())){
                    return true;
                }
            }
       }
       if ($this->getDetailsConfig()){
            $this->prefix = $this->getPrefix();
            $this->details = $this->getDetailsConfig();
            $this->defineCon();
            return true;
       }
       
       return false;
    }
    
    private function getPrefix(){
        $prefix = implode(",",preg_grep('/\$table_prefix/i', file($this->config)));
        preg_match_all('/\'(.*?)\'/', $prefix, $match);
        if (!isset($match[1][0])){
            return false;
        }
        return $match[1][0];
    }
    
    private function getDetailsConfig(){
        if (!file_exists($this->config)){
            return false;
        }
        $config = file($this->config);
        $dbDetails = $this->getConDetails($config);
        if (!$dbDetails){
            return false;
        }
        if (isset($this->prefix)){
            $dbDetails = $dbDetails + ['prefix' => $this->prefix];
        }
        $this->putConfig($dbDetails);
        
        return $dbDetails;
    }
    
    private function getDetailsTmp(){
        $dbDetails = json_decode(file_get_contents($this->tmpConfig), true);
        if (JSON_ERROR_NONE ===! json_last_error()){
            return false;
        }
        if (!is_array($dbDetails)){
            return false;
        }
        return $dbDetails;
    }
    
    private function defineCon(){
        $this->con = new Mysqli($this->details['host'], $this->details['user'], $this->details['pass'], $this->details['db']);
        return true;
    }
    
    private function getConDetails(array $config, $dbDetails = []){
        $dbDetails['user'] = implode(",",preg_grep('/DB_USER/i', $config));
        $dbDetails['pass'] = implode(",",preg_grep('/DB_PASSWORD/i', $config));
        $dbDetails['host'] = implode(",",preg_grep('/DB_HOST/i', $config));
        $dbDetails['db'] = implode(",",preg_grep('/DB_NAME/i', $config));
        foreach ($dbDetails as $key => $item){
            preg_match_all('/\'(.*?)\'/', $item, $match);
            $dbDetails[$key] = $match[1][1];
        }
        
        return $dbDetails;
    }
    
    private function checkArrayKeys($emptyKeys = []){
        $keys = ['user', 'pass', 'host', 'db'];
        foreach ($keys as $key){
            if (!array_key_exists($key, $this->details)){
                array_push($emptyKeys, $key);
            }
        }
        
        return true;
    }
    
    private function checkDbArray($empty = []){
        foreach ($this->details as $key => $item){
            if (empty($this->details[$key])){
                array_push($empty, $key);
            }
        }
        if (!empty($empty)){
            return $empty;
        }
        
        return true;
    }
    
    private function testConnection(){
        if ($this->con->connect_error) {
            return [$this->con->connect_error];
        }
        
        return true;
    }
    
    private function makeJson(array $config){
        
        return json_encode($config);
    }
    
    private function putConfig(array $config){
        
        return file_put_contents($this->tmpConfig, $this->makeJson($config));
    }
    
}