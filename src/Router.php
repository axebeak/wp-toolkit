<?php

class Router {
    
    private $request;
    
    private $controller;
    
    public function __construct(array $request){
        $this->request = $request;
        if ($this->request['log-in'] || $this->request['self-destruct']){
            $this->controller = new Controller(true);
            return true;
        }
        $this->controller = new Controller;
    }
        
    public function response(){
        if (empty($this->request)){
            return $this->controller->error('Please provide parameters when making request to API');
        }
        if (isset($this->request['log-in']) && isset($this->request['toolkit-password'])){
            return $this->login($this->request['toolkit-password']);
        }
        if (isset($this->request['self-destruct'])){
            return $this->selfDestruct();
        }
        if (isset($this->request['flush'])){
            return $this->flush();
        }
        if (isset($this->request['check'])){
            return $this->check();
        }
        if (isset($this->request['db-details'])){
            return $this->submitDatabase($this->request['db-details']);
        }
        if (isset($this->request['fetch'])){
            return $this->fetch($this->request['fetch']);
        }
        if (isset($this->request['main-data'])){
            return $this->updateMain($this->request['main-data']);
        }
        if (isset($this->request['theme']) && isset($this->request['name'])){
            return $this->theme($this->request['theme'], $this->request['name']);
        }
        if (isset($this->request['plugins']) && isset($this->request['action'])){
            return $this->plugins($this->request['plugins'], $this->request['action']);
        }
        if (isset($this->request['reupload']) && isset($this->request['list'])){
            return $this->reupload($this->request['reupload'], $this->request['list']);
        }
        if (isset($this->request['undo'])){
            return $this->undo($this->request['undo']);
        }
        if (isset($this->request['revert'])){
            return $this->revert($this->request['revert']);
        }
        if (isset($this->request['userinfo'])){
            return $this->userinfo($this->request['userinfo']);
        }
        if (isset($this->request['user']) && isset($this->request['action'])){
            return $this->user($this->request['user'], $this->request['action'], isset($this->request['value']) ? $this->request['value'] : false);
        }
        if (isset($this->request['new-user']) && isset($this->request['username']) &&
        isset($this->request['password']) && isset($this->request['email'])  &&
        isset($this->request['nicename']) && isset($this->request['capabilities'])){
            return $this->newUser($this->request['username'], $this->request['password'], $this->request['email'], $this->request['nicename'], $this->request['capabilities']);
        }
        if (isset($this->request['user-roles'])){
            return $this->roles();
        }
        if (isset($this->request['reupload-default'])){
            return $this->reuploadDefault();
        }
        if (isset($this->request['default-theme'])){
            return $this->reuploadDefaultTheme($this->request['default-theme']);
        }
        if (isset($this->request['remove']) && isset($this->request['list']) && isset($this->request['source'])){
            return $this->remove($this->request['remove'], $this->request['list'], $this->request['source']);
        }
        if (isset($this->request['cache-folder'])){
            return $this->cache($this->request['cache-folder']);
        }
        if (isset($this->request['tc-config'])){
            return $this->cacheConfig($this->request['tc-config']);
        }
        if (isset($this->request['old-files'])){
            return $this->removeOldFiles();
        }
        if (isset($this->request['versions'])){
            return $this->versions();
        }
        
        return $this->controller->error('Unknown parameters in the API request.');
    }
    
    private function login($password){
        
        return $this->controller->checkLogin($password);
    }
    
    private function check(){
        
        return $this->controller->makeCheck();
    }
    
    private function selfDestruct(){
        
        return $this->controller->selfDestruct();
    }
    
    private function flush(){
        
        return $this->controller->flush();
    }
    
    private function submitDatabase(array $details){
        
        return $this->controller->databaseSubmit($details);
    }
    
    private function fetch($type){
        switch($type){
            case 'main':
                return $this->controller->fetchMain();
            case 'plugins':
                return $this->controller->fetchPlugins();
            case 'themes':
                return $this->controller->fetchTheme();
            case 'users':
                return $this->controller->fetchUsers();
            case 'versions':
                return $this->controller->fetchVersions();
            default:
                return $this->controller->error(sprintf("Can't fetch '%s'. Unknown Parameter.", $type));
        }
    }
    
    private function updateMain($data){
        
        return $this->controller->updateMain($data);
    }
    
    private function theme($theme, $name){
        switch($theme){
            case 'template':
                return $this->controller->setTemplate($name);
            case 'stylesheet':
                return $this->controller->setStylesheet($name);
            case 'both':
                return $this->controller->changeTheme($name);
            default:
                return $this->controller->error(sprintf("Uknown directive to set theme: '%s'", $theme));
        }
    }
    
    private function plugins($plugins, $directive){
        switch($directive){
            case 'enable':
                return $this->controller->enablePlugins($plugins);
            case 'disable':
                return $this->controller->disablePlugins($plugins);
            default:
                return $this->controller->error(sprintf("Unknown directive for plugin actions: '%s'. Please use either 'enable' or 'disable'", $directive));
        }
    }
    
    private function undo($type){
        switch($type){
            case 'themes':
                return $this->controller->undoThemes();
            case 'plugins':
                return $this->controller->undoPlugins();
            default:
                return $this->controller->error(sprintf("Unknown directive for undo actions: '%s'. Please use either 'themes' or 'plugins'", $type));
        }
    }
    
    private function revert($type){
        switch($type){
            case 'themes':
                return $this->controller->revertThemes();
            case 'plugins':
                return $this->controller->revertPlugins();
            default:
                return $this->controller->error(sprintf("Unknown directive for revert actions: '%s'. Please use one of the following: 'themes' or 'plugins'", $type));
        }
    }
    
    private function reupload($type, $list){
        switch($type){
            case 'plugins':
                return $this->controller->reuploadPlugins($list);
            case 'themes':
                return $this->controller->reuploadThemes($list);
            default:
                return $this->controller->error(sprintf("Unknown directive for reupload actions: '%s'. Please use one of the following: 'themes' or 'plugins'", $type));
        }
    }
    
    private function user($user, $action, $parameter = false){
        switch($action){
            case 'password':
                return $this->controller->resetPass($user, $parameter);
            case 'user-level':
                return $this->controller->setUsermeta($user, $parameter);
            case 'delete':
                return $this->controller->deleteUser($user);
            default:
                return $this->controller->error(sprintf("Unknown directive for user actions: '%s'. Please use one of the following: 'password', 'user-level' or 'delete'", $action));
        }
    }
    
    private function remove($type, $list, $source){
        switch($type){
            case 'plugins':
                return $this->controller->removePlugins($list, $source);
            case 'themes':
                return $this->controller->removeThemes($list, $source);
            default:
                return $this->controller->error(sprintf("Unknown directive for removal actions: '%s'. Please use one of the following: 'plugins' or 'themes'", $type));
        }
    }
    
    private function reuploadDefault(){
        
        return $this->controller->reuploadDefault();
    }
    
    private function reuploadDefaultTheme($set){
        
        return $this->controller->reuploadDefaultTheme($set);
    }

    
    private function cache($action){
        
        return $this->controller->cacheOperations($action);
    }
    
    private function cacheConfig($action){
        
        return $this->controller->cacheConfigOperations($action);
    }
    
    private function removeOldFiles(){
        
        return $this->controller->removeOldFiles();
    }
    
    private function roles(){
        
        return $this->controller->userRoles();
    }
    
    private function userinfo($user){
        
        return $this->controller->fetchUserInfo($user);
    }

    
    private function newUser($user, $password, $email, $nicename, $capabilities){
        
        return $this->controller->newUser($user, $password, $email, $nicename, $capabilities);
    }
    
}