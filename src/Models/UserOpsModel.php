<?php

class UserOpsModel {
    
    public $users;
    
    public $roles;
    
    private $con;
    
    private $prefix;
    
    private $defaultRoles;
    
    public $userPrivileges;
    
    public $capabilities;
    
    public $userlevel;
    
    public $userPermissions;
    
    public function __construct(){
        $db = new DatabaseModel;
        $this->con = $db->con;
        $this->prefix = $db->prefix;
        $this->users = $this->fetchUsers();
        $this->roles = $this->fetchRoles();
        $this->defaultRoles = implode (" ", file(TOOLKIT.'/data/roles.txt'));
        $this->capabilities = $this->fetchCapabilities();
        $this->userlevel = $this->fetchLevels();
        $this->userPrivileges = $this->fetchCapabilityNames();
        $this->userPermissions = $this->fetchPermissions();
    }
    
    
    public function newUser($user, $password, $email, $nicename, $capabilities){
        if (!array_key_exists($capabilities, $this->capabilities)){
            return false;
        }
        $this->con->query("INSERT INTO `".$this->prefix."users` (`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_status`)
            VALUES ('".$user."', MD5('".$password."'), '".$nicename."', '".$email."', '0')");
        $this->con->query("INSERT INTO `".$this->prefix."usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`)
            VALUES (NULL, (Select max(id) FROM ".$this->prefix."users), '".$this->prefix."capabilities', '".$this->capabilities[$capabilities]."')");
        $this->con->query("INSERT INTO `".$this->prefix."usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`)
            VALUES (NULL, (Select max(id) FROM ".$this->prefix."users), '".$this->prefix."user_level', '".$this->userlevel[$capabilities]."')");
        return $user;
    }
    
    public function setDefaultRoles(){
        if ($this->checkRoles() == "CREATE"){
            $this->con->query("INSERT INTO `".$this->prefix."options` (option_name, option_value) VALUES ('".$this->prefix."user_roles', '".addslashes($this->defaultRoles)."')");
            return true;
        } elseif ($this->checkRoles() == 'MODIFY'){
            $this->con->query("UPDATE `".$this->prefix."options` SET `option_value`='".addslashes($this->defaultRoles)."' WHERE `option_name` = '".$this->prefix."user_roles'");
            return true;
        } else {
            return false;
        }
    }
    
    public function userExists($user){
        if (is_int($user)){
            if (isset($this->users[$user])){
                return true;
            }
        }
        if (in_array($user, $this->users)){
            return true;
        }
        
        return false;
    }    
    
    private function fetchUsers($users = []){
        $query = "SELECT * FROM ".$this->prefix."users";
        $result = $this->con->query($query);
        while($row = mysqli_fetch_array($result)){
            $users = $users + [$row['ID'] => $row['user_login']];
        }
        
        return $users;
    }
    
    private function fetchRoles(){
        $queryRoles = $this->con->query("SELECT * FROM `".$this->prefix."options` WHERE `option_name` = '".$this->prefix."user_roles'");
        while($roles = mysqli_fetch_array($queryRoles)){
            $userinfo['roles'] = $roles['option_value'];
        }
        
        return unserialize($userinfo['roles']);
    }
    
    private function fetchCapabilityNames(){
        $capabilities = [];
        foreach ($this->roles as $role => $array){
            $capabilities = $capabilities + [$role => $array['name']];
        }
        $capabilities = $capabilities + ['unknown' => 'Unknown','empty' => 'Empty' , 'not-set' => 'Not Set'];
        
        return $capabilities;
    }
    
    private function fetchCapabilities(){
        $capabilities = [];
        foreach ($this->roles as $role => $array){
            $capabilities = $capabilities + [$role => serialize([$role => true])];
        }
        
        return $capabilities;
    }
    
    private function fetchPermissions(){
        $abilities = [];
        foreach ($this->roles as $role => $array){
            $abilities = $abilities + [$role => $array['capabilities']];
        }
        
        return $abilities;
    }
    
    private function getLevel(){
        $levels = [];
        foreach ($this->roles as $role => $array){
            $levels[$role] = [];
            foreach ($array['capabilities'] as $capability => $value){
                if (preg_match('/level/', $capability)){
                    array_push($levels[$role], $capability);
                }
            }
        }
        
        return $levels;
    }
    
    public function fetchLevels(){
        $levelArray = [];
        $levels = $this->getLevel();
        foreach ($levels as $capability => $level){
            $levelArray[$capability] = count($level) - 1;
        }
        
        return $levelArray;
    }
    
    private function checkRoles(){
        $result = $this->con->query("SELECT * FROM `".$this->prefix."options` WHERE `option_name` = '".$this->prefix."user_roles'");
        if (mysqli_num_rows($result) < 1){
            return 'CREATE';
        }
        while ($arr = mysqli_fetch_array($result)){
            $currentRoles = $arr['option_value'];
        }
        if ($currentRoles !== $this->defaultRoles){
            return 'MODIFY';
        } else {
            return false;
        }
    }
    
}