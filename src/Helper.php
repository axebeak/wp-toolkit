<?php

class Helper {
    
    public static function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        
        return rmdir($dir);
    }
    
    public static function reuploadWP(){
        require_once(WP_DIR.'/wp-includes/version.php');
        $name="wordpress-".$wp_version.".zip";
        $link="https://wordpress.org/wordpress-".$wp_version.".zip";
        $file = file_get_contents("$link");
        if ($file === false) {
            if (!ini_get('allow_url_fopen')){
                throw new \Exception('Error getting WordPress installation. It seems like allow_url_fopen is disabled. Please enable it to proceed. ');
        }
        throw new \Exception('Error getting WordPress installation. ');
        }
        file_put_contents($name, $file);
        $dir = TOOLKIT.'/tmp';
        mkdir($dir, 0755);
        $zip = new ZipArchive;
        if ($zip->open($name) === TRUE) {
            $zip->extractTo($dir);
            $zip->close();
        }  else {
            throw new \Exception('Error during arhive extraction.');
        }
    }

    public static function removeWPInstall(){
        $dir = TOOLKIT.'/tmp/wordpress';
        require('../wp-includes/version.php');
        $name="wordpress-".$wp_version.".zip";
        unlink($name);
        Self::delTree($dir);
        
        return true;
    }

    public static function isMultisite(){
        $config = file(WP_DIR.'/wp-config.php');
        $multiSite[] = implode(",",preg_grep('/MULTISITE/i', $config));
        $multiSite[] = implode(",",preg_grep('/WP_ALLOW_MULTISITE/i', $config));
        foreach ($multiSite as $item){
            if (!empty($item)){
                return true;
            }
        }
    
        return false;
    }
    
    public static function checkPhpVer(){
        if (version_compare(phpversion(), '5.6', '<')) {
            return false;
        }
        
        return true;
    }

    public static function checkFopen(){
        if (ini_get('allow_url_fopen')){
            return true;
        }
        
        return false;
    }

    public static function checkVer(){
        if (!Helper::checkFopen()){
            return false;
        }
        $newVer = file_get_contents('https://raw.githubusercontent.com/axebeak/wp-toolkit/master/data/ver.txt');
        $curVer = file_get_contents('data/ver.txt');
        $check = $newVer - $curVer;
        if ($check == 0){
            return true;
        } 
        
        return false;
    }
}