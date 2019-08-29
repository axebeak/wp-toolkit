<?php

class FilesModel {
    
    public $defaultFiles;
    
    public $cacheFolder;
    
      public function __construct(){
        $this->defaultFiles = ["index.php",
            "wp-activate.php",
            "wp-admin",
            "wp-blog-header.php",
            "wp-comments-post.php",
            "wp-config-sample.php",
            "wp-cron.php",
            "wp-includes",
            "wp-links-opml.php",
            "wp-load.php",
            "wp-login.php",
            "wp-mail.php",
            "wp-settings.php",
            "wp-signup.php",
            "wp-trackback.php",
            "xmlrpc.php"
        ];
        $this->cacheFolder = WP_DIR.'/wp-content/cache';
        $this->cacheConfig = WP_DIR.'/wp-content/w3tc-config';
    }
    
    public function reanimate($result = []){
        $result['version'] = true;
        if (!file_exists(WP_DIR.'/wp-includes/version.php')) {
            $result['version'] = false;
            return $result;
        } else {
            $dir = WP_DIR.'/wp-oldfiles';
            Helper::reuploadWP();
            mkdir($dir, 0755);
            foreach($this->defaultFiles as $file){
                rename(WP_DIR.'/'.$file, $dir.'/'.$file);
            }
            foreach($this->defaultFiles as $file){
                rename(TOOLKIT.'/tmp/wordpress/'.$file, '../'.$file);
            }
            Helper::removeWPInstall();
            $result['success'] = true;
            
            return $result;
    }
    }
    
    public function checkCacheFolder(){
        if (!is_dir($this->cacheFolder)){
            return false;
        } 
            
        return true;
    }

    public function checkDisCacheFolder(){
        if (!is_dir($this->cacheFolder.'.dis')){
            return false;
        }
        
        return true;
    }
    
    public function removeCache(){
        if (!$this->checkCacheFolder()){
            return false;
        }
        Helper::delTree($this->cacheFolder);
        
        return true;
    }
    
    public function disableCache(){
        $dir = $this->cacheFolder.'.dis';
        if (!$this->checkCacheFolder()){
            return false;
        }
        if (is_dir($dir)){
            $dir = $dir.rand(1,999);    
        }
        rename($this->cacheFolder, $dir);
        
        return $dir;
    }
    
    public function enableCache(){
        $result['success'] = false;
        $dir = $this->cacheFolder.'.dis';
        if (!is_dir($dir)){
            $result['exists'] = false;
            return $result;
        }
        if ($this->checkCacheFolder()){
            $result['enabled'] = false;
            return $result;
        }
        rename($dir, $this->cacheFolder);
        $result['success'] = true;
        
        return $result;
    }
    
    public function checkOldFiles(){
        $dir = WP_DIR.'/wp-oldfiles';
        if (is_dir($dir)){
            return true;
        }
        
        return false;
    }
    
    public function removeOldFiles(){
        if (!$this->checkOldFiles()){
            return false;
        }
        Helper::delTree(WP_DIR.'/wp-oldfiles');
        
        return true;
    }
    
    public function checkTCConfig(){
        if (!is_dir($this->cacheConfig)){
            return false;
        }

        return true;
    }
    
    public function checkDisTCConfig(){
        if (!is_dir($this->cacheConfig.'.dis')){
            return false;
        }
        
        return true;
    }
    
    public function removeTCConfig(){
        if (!$this->checkTCConfig()){
            return false;
        }
        Helper::delTree($this->cacheConfig);

        return true;        
    }
    
    public function enableTCConfig(){
        $result['success'] = false;
        $dir = $this->cacheConfig.'.dis';
        if (!is_dir($dir)){
            $result['exists'] = false;
            return $result;
        }
        if ($this->checkTCConfig()){
            $result['enabled'] = false;
            return $result;
        }
        rename($dir, $this->cacheConfig);
        $result['success'] = true;
        
        return $result;      
    }

    public function disableTCConfig(){
        $dir = $this->cacheConfig.'.dis';
        if (!$this->checkTCConfig()){
            return false;
        }
        if (is_dir($this->cacheConfig)){
            $dis = $dis.rand(1,999);    
        }
        rename($this->cacheConfig, $dir);
        
        return $dir;        
    }
    
}

