<?php

class ThemeModel {
    
    public $template;
    
    public $stylesheet;
    
    public $themes;
    
    private $con;
    
    private $prefix;
    
    public function __construct(){
        $db = new DatabaseModel;
        $this->con = $db->con;
        $this->prefix = $db->prefix;
        $this->template = $this->fetchTemplate();
        $this->stylesheet = $this->fetchStyle();
        $this->themes = $this->fetchThemes();
        $this->themeRevertDump();
    }
    
    public function setTemplate($theme){
        if (!in_array($theme, $this->fetchThemes())){
            return false;
        }
        $query = "update `".$this->prefix."options` set `option_value`='".$theme."' where `".$this->prefix."options`.`option_name`='template'";
        $this->lastTheme();
        $this->con->query($query);
        return $theme;        
    }
    
    public function setStyle($theme){
        if (!in_array($theme, $this->fetchThemes())){
            return false;
        }
        $query = "update `".$this->prefix."options` set `option_value`='".$theme."' where `".$this->prefix."options`.`option_name`='stylesheet'";
        $this->lastTheme();
        $this->con->query($query);
        return $theme;              
    }
    
    public function changeTheme($theme){
        if (!in_array($theme, $this->fetchThemes())){
            return false;
        }
        $query1 = "update `".$this->prefix."options` set `option_value`='".$theme."' where `".$this->prefix."options`.`option_name`='template'";
        $query2 = "update `".$this->prefix."options` set `option_value`='".$theme."' where `".$this->prefix."options`.`option_name`='stylesheet'";
        $this->lastTheme();
        $this->con->query($query1);
        $this->con->query($query2);
        
        return $theme;
    }
    
    public function themeRevert(){
         if (!file_exists(TOOLKIT.'/tmp/themes.tmp')){
            return false;
        }
        $theme = unserialize(file_get_contents(TOOLKIT.'/tmp/themes.tmp'));
        if (!$theme){
            return false;
        }
        $this->setTemplate($theme['template']);
        $this->setStyle($theme['style']);
        
        return true;
    }
    
    public function themeUndo(){
        if (!file_exists(TOOLKIT.'/tmp/themes-last.tmp')){
            return false;
        }
        $theme = unserialize(file_get_contents(TOOLKIT.'/tmp/themes-last.tmp'));
        if (!$theme){
            return false;
        }
        $this->setTemplate($theme['template']);
        $this->setStyle($theme['style']);
        
        return true;
    }
    
    public function downloadTheme($theme, $version, $result = []){
        $result['success'] = false;
        if (!is_string($theme)){
            return false;
        }
        $result['theme'] = $theme;
        $result['version'] = $version;
        if ($version === 'CURRENT'){
            $result['version'] = $this->checkVersion();
        }
        if ($version === 'LATEST'){
            if ($this->checkWpRepo($theme)){
                $link = $this->fetchLatestVer($theme);
            } else {
                $result['theme-repo'] = false;
                return $result;
            }
        } else {
             if(!$this->checkWpRepo($theme)){
                $return['theme-repo'] = false;
                return $result;
             } elseif($this->checkWpRepo($theme) && !$this->checkWpRepoVersion($theme, $version)) {
                $result['theme-repo'] = true;
                $result['version-repo'] = false;
                return $result;
             } else {
                $link = 'https://downloads.wordpress.org/theme/'.$theme.'.'.$version.'.zip';
             }    
        }
        $file = file_get_contents($link);
        if ($file === false) {
            $result['error'] = 'Error getting theme.';
            if (!ini_get('allow_url_fopen')){
                $result['error'] = 'Error getting theme. It seems like allow_url_fopen is disabled. Please enable it to proceed.';
            }
            return $result;
        }
        $name = $theme.'.zip';
        file_put_contents($name, $file);
        $zip = new ZipArchive;
        if ($zip->open($name) === TRUE) {
            $zip->extractTo('tmp');
            $zip->close();
        }  else {
            $result['error'] = 'Error during archive extraction.';
        }
        if(is_dir(WP_DIR."/wp-content/themes/$theme")){
            $result['old'] = $theme.".dis.".rand(1,999);
            rename( WP_DIR."/wp-content/themes/".$theme, WP_DIR."/wp-content/themes/".$result['old']);
        }
        $result['success'] = true;
        unlink($name);
        rename("tmp/$theme", WP_DIR."/wp-content/themes/".$theme);
        
        return $result;
    } 
    
    public function getThemeVersions($themes = []){
        foreach ($this->themes as $theme){
            $version = $this->checkVersion($theme);
            if (!$version){
                $themes = $themes + [$theme => false];
                continue;
            }
            $themes = $themes + [$theme => $version];
        }
        
        return $themes;
    }
    
    public function removeThemes(array $themes){
        $result = [];
        foreach ($themes as $theme){
            $themeFolder = WP_DIR.'/wp-content/themes/'.$theme;
            if (!$this->themeExists($themeFolder)){
                $result[$theme]['success'] = false;
                $result[$theme]['error'] = sprintf('Folder for %s theme not found', $theme);
                continue;
            }
            if ($theme === $this->template && $theme === $this->stylesheet){
                $result[$theme]['success'] = false;
                $result[$theme]['error'] = sprintf('%s is set as a theme. Set another theme is you want to remove it.', $theme);
                continue;
            }
            if ($theme === $this->stylesheet){
                $result[$theme]['success'] = false;
                $result[$theme]['error'] = sprintf('%s is set as a stylesheet. Set another theme is you want to remove it.', $theme);
                continue;
            }
            if ($theme === $this->template){
                $result[$theme]['success'] = false;
                $result[$theme]['error'] = sprintf('%s is set as a template. Set another theme is you want to remove it.', $theme);
                continue;
            }
            Helper::delTree($themeFolder);
            $result[$theme]['success'] = true;
        }
        
        return $result;
    }
    
    public function reuploadDefault($set){
        $defaultThemes = [
            'twentynineteen',
            'twentyseventeen',
            'twentysixteen',
            'twentyfifteen',
            'twentyforteen',
            'twentythirteen',
            'twentytwelve',
            'twentyeleven',
            'twentyten'
        ];
        Helper::reuploadWP();
        $folder = TOOLKIT.'/tmp/wordpress/wp-content/themes/';
        $themeFolders = array_filter(glob($folder.'*'), 'is_dir');
        $themes = [];
        foreach ($themeFolders as $theme){
            $themeName = end(explode("/", $theme));
            $themes = $themes + [$themeName => $theme];
        }
        foreach ($defaultThemes as $theme){
            if (!array_key_exists($theme, $themes)){
                continue;
            }
            $currentFolder = WP_DIR."/wp-content/themes/".$theme;
            if (is_dir($currentFolder)){
                rename($currentFolder, WP_DIR."/wp-content/themes/".$theme.".dis.".rand(0,999));
            }
            rename($themes[$theme], WP_DIR."/wp-content/themes/".$theme);
            Helper::removeWPInstall();
            if ($set){
                $this->changeTheme($theme);
            }
            return $theme;
        }
        Helper::removeWPInstall();

        return false;
    }
    
    private function fetchStyle(){
        $query = "SELECT * FROM ".$this->prefix."options where `".$this->prefix."options`.`option_name`='stylesheet'";
        $style = $this->con->query($query);
        while($styles = mysqli_fetch_array($style)){
            $stylesheet = $styles['option_value'];
        }
        
        return $stylesheet;
    }
    
    private function fetchTemplate(){
        $query = "SELECT * FROM ".$this->prefix."options where `".$this->prefix."options`.`option_name`='template'";
        $temp = $this->con->query($query);
        while($templ = mysqli_fetch_array($temp)){
            $template = $templ['option_value'];
        }
        
        return $template;
    }
    
    private function fetchThemes(){
        $folders = array_filter(glob('../wp-content/themes/*'), 'is_dir');
        $themes = array();
        foreach($folders as $folder){
            $theme = explode(" ", str_replace("../wp-content/themes/", "", $folder));
            $themes[] = array_pop($theme);
        }
        
        return $themes;
    }
    
    private function checkVersion($theme){
        $path = WP_DIR."/wp-content/themes/".$theme;
        $style = $path."/style.css";
        if (!file_exists($style)){
            return false;
        }
        $searchthis = 'Version:';
        $matches = array();
        $handle = @fopen($style, "r");
        if ($handle){
            while (!feof($handle)){
                $buffer = fgets($handle);
                if(strpos($buffer, $searchthis) !== FALSE)
                    $matches[] = $buffer;
            }
        fclose($handle);
        }
        $versionArray = explode(" ", implode(" ", $matches));
        $version = preg_replace('/\s/', '', implode(" ", preg_grep('/\d{1,}([\.,][\d{1,2}])?/', $versionArray)));
        if (empty($version)){
            return false;
        }
        
        return $version;
    }
    
    private function checkWpRepo($theme){
        $link = 'https://wordpress.org/themes/'.$theme.'/';
        $handle = curl_init($link);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode == 404 || $httpCode == 302) {
            return false;
        }
        
        return true;
    }
    
    private function checkWpRepoVersion($theme, $version){
        $link = 'https://downloads.wordpress.org/theme/'.$theme.'.'.$version.'.zip';
        $handle = curl_init($link);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return false;
        }
        
        return true;
    }
    
    private function fetchLatestVer($theme){
        if ($this->checkWpRepo($theme) !== false){
            $fetch = file_get_contents("https://wordpress.org/themes/$theme/");
            preg_match('/\<(.*?)\>Download/', $fetch, $match);
            preg_match_all('/"([^"]*)"/', $match[1], $result);
            return $result[1][0];
        }
        
        return false;
    }
    
    private function lastTheme(){
        file_put_contents('tmp/themes-last.tmp', serialize(["template" => $this->template, "style" => $this->stylesheet]));
        
        return true;
    }    
    
    private function themeRevertDump(){
        if (!file_exists('tmp/themes.tmp')){
            file_put_contents('tmp/themes.tmp', serialize(["template" => $this->template, "style" => $this->stylesheet]));
            return true;
        }
        
        return false;
    }
    
    private function themeExists($theme){
        if (!file_exists($theme)){
            return false;
        }
        
        return true;
    }
}