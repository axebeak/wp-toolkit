<?php

class UserModel {
    
    public $userinfo;
    
    public $userPrivileges;
    
    private $id;
    
    private $con;
    
    private $prefix;
    
    private $capabilities;
    
    private $userlevel;
    
    public function __construct($id){
        $db = new DatabaseModel;
        $this->con = $db->con;
        $this->prefix = $db->prefix;
        $userOps = new UserOpsModel;
        $this->capabilities = $userOps->capabilities;
        $this->userlevel = $userOps->userlevel;
        $this->userPrivileges = $userOps->userPrivileges;
        if (!$this->checkID($id)){
            throw new \Exception("Unknown user ID!");
        }
        $this->id = $id;
        $this->userinfo = $this->userInfo() + $this->checkUsermeta();
    }

    public function deleteUser(){
        $this->con->query("DELETE FROM `".$this->prefix."users` WHERE `ID` = '$this->id'");
        $this->con->query("DELETE FROM `".$this->prefix."usermeta` WHERE `user_id` = '$this->id'");
        
        return true;
    }

    public function resetPass($password){

        $this->con->query("UPDATE `".$this->prefix."users` SET `user_pass` = MD5('".$password."') WHERE `".$this->prefix."users`.`ID` = '$this->id'");
        return true;
    }    

    public function setUsermeta($capabilities){
        if (!array_key_exists($capabilities, $this->capabilities)){
            return false;
        }
        $usermeta = $this->checkUsermeta();
        if ($usermeta['capabilities'] === 'Not Set' && $usermeta['level'] === 'Not Set'){
            $this->updateUsermeta($capabilities, 'CREATE', 'CREATE');
        } elseif ($usermeta['capabilities'] === 'Not Set') {
            $this->updateUsermeta($capabilities, 'CREATE', 'MODIFY');
        } elseif ($usermeta['level'] === 'Not Set') {
            $this->updateUsermeta($capabilities, 'MODIFY', 'CREATE');
        } elseif ($usermeta['level'] === $usermeta['capabilities'] ||
            $usermeta['capabilities'] !== $usermeta['level']) {
            $this->updateUsermeta($capabilities, 'MODIFY', 'MODIFY');
        } else {
            throw new \Exception('An unexpected error occured during the configuration of the user level.');
        }
        
        return true;
    }
    
    private function userInfo(){
        $result = $this->con->query("SELECT * FROM `".$this->prefix."users` WHERE `ID` = '$this->id'");
        $userinfo = array();

        if ($result === false){
            throw new \Exception('Something wrong happened during the userinfo check.');
        }
        if (mysqli_num_rows($result) === 0){
            return false;
        }
        while($arr = mysqli_fetch_array($result)){
            $userinfo['id'] = $arr['ID'];
            $userinfo['username'] = $arr['user_login'];
            $userinfo['email'] = $arr['user_email'];
            $userinfo['nice_name'] = $arr['user_nicename'];
        }
        
        return $userinfo;
    }
    
    private function checkID($id){
        $result = $this->con->query("SELECT * FROM `".$this->prefix."users` WHERE `ID` = '".$id."'");
        if (mysqli_num_rows($result) == 0){
            return false;
        } else {
            return true;
        }
    }
    
    private function fetchCapabilities(){
        $queryCap = $this->con->query("SELECT * FROM `".$this->prefix."usermeta` WHERE `user_id` = '$this->id' AND `meta_key` = '".$this->prefix."capabilities'");
        $queryLvl = $this->con->query("SELECT * FROM `".$this->prefix."usermeta` WHERE `user_id` = '$this->id' AND `meta_key` = '".$this->prefix."user_level'");
        while($cap = mysqli_fetch_array($queryCap)){
            $userinfo['cap'] = $cap['meta_value'];
            $userinfo['cap-key'] = $cap['meta_key'];
        }
        while($lvl = mysqli_fetch_array($queryLvl)){
            $userinfo['lvl'] = $lvl['meta_value'];
            $userinfo['lvl-key'] = $lvl['meta_key'];
        }
        
        return $userinfo;
    }
    
    private function capabilityCheck($userinfo){
        if (empty($userinfo['cap-key'])){
            return false;
        }
        foreach ($this->capabilities as $key => $capability){
            if ($userinfo['cap'] == $capability){
                return $key;
            }
            if (empty($userinfo['cap'])){
                return 'empty';
            }
        }

        return 'unknown';
    }
    
    private function levelCheck($userinfo){
        if (empty($userinfo['lvl-key'])){
            return false;
        }
        foreach ($this->userlevel as $key => $level){
            if ($userinfo['lvl'] == $level){
                return $key;
            }
            if (empty($level) && empty($userinfo['lvl']) && is_numeric($level) && is_numeric($userinfo['lvl'])){
                return $key;
            }
            if (empty($userinfo['lvl']) && !is_numeric($userinfo['lvl'])){
                return 'empty';
            }
        }
        
        return 'unknown';
    }

    private function updateUsermeta($capabilities, $capFlag, $lvlFlag){
        $queryModCap = "UPDATE `".$this->prefix."usermeta` SET `meta_value` = '".$this->capabilities[$capabilities]."' WHERE `meta_key` = '".$this->prefix."capabilities' AND `user_id` = '$this->id'";
        $queryModLvl = "UPDATE `".$this->prefix."usermeta` SET `meta_value` = '".$this->userlevel[$capabilities]."' WHERE `meta_key` = '".$this->prefix."user_level' AND `user_id` = '$this->id'";
        $queryCreateCap = "INSERT INTO `".$this->prefix."usermeta` (user_id, meta_key, meta_value) VALUES ('$this->id', '".$this->prefix."capabilities', '".$this->capabilities[$capabilities]."')";
        $queryCreateLvl = "INSERT INTO `".$this->prefix."usermeta` (user_id, meta_key, meta_value) VALUES ('$this->id', '".$this->prefix."user_level', '".$this->userlevel[$capabilities]."')";
        
        switch ($capFlag):
            case "MODIFY":
                $this->con->query($queryModCap);
                break;
            case "CREATE":
                $this->con->query($queryCreateCap);
                break;
            case "NOTHING":
                break;
            default:
                throw new \Exception('Unknown operation during usermeta update!');
        endswitch;
        
        switch ($lvlFlag):
            case "MODIFY":
                $this->con->query($queryModLvl);
                break;
            case "CREATE":
                $this->con->query($queryCreateLvl);
                break;
            case "NOTHING":
                break;
            default:
                throw new \Exception('Unknown operation during usermeta update!');
        endswitch;

    }

    public function checkUsermeta(){
        $privileges = $this->fetchCapabilities();
        $capabilities = $this->capabilityCheck($privileges);
        $level = $this->levelCheck($privileges);
        if (!$capabilities){
            $capabilities = 'not-set';
        }
        if (!$level){
            $level = 'not-set';
        }
        if (empty($capabilities)){
            $capabilities = 'not-set';
        }
        if (empty($level)){
            $level = 'not-set';
        }
        $usermeta['capabilities'] = $this->userPrivileges[$capabilities];
        $usermeta['level'] = $this->userPrivileges[$level];
        
        return $usermeta;
    }
    
}