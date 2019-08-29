<?php 

class PluginModel {
    
    public $activePlugins;
    
    public $inactivePlugins;
    
    public $nonExistingPlugins;
    
    private $allPlugins;
    
    private $pluginsDb;
    
    private $con;
    
    private $prefix;
    
    public function __construct(){
        $db = new DatabaseModel;
        $this->con = $db->con;
        $this->prefix = $db->prefix;
        $this->allPlugins = $this->fetchAllPlugins();
        $this->pluginsDb = $this->fetchPluginsDb();
        $this->activePlugins = $this->fetchActivePlugins();
        $this->inactivePlugins = $this->fetchInactivePlugins();
        $this->nonExistingPlugins = $this->fetchNonExistingPlugins();
        if (empty($this->nonExistingPlugins)){
            $this->nonExistingPlugins = false;
        }
        $this->pluginsRevertDump();
    }
    
    public function enablePlugins($plugins){
        if (!is_array($plugins)){
            $plugins = array($plugins);
        }
        $activePlugins = $this->pluginsDb;
        $pluginsArray = [];
        foreach($plugins as $plugin){
            $plugin = $this->findPluginFile($plugin);
            if (in_array($plugin, $activePlugins)){
                continue;
            }
            array_push($pluginsArray, $plugin);
        }
        $result = array_merge($activePlugins, $pluginsArray);
        sort($result, SORT_NATURAL | SORT_FLAG_CASE);
        $newPlugins = serialize($result);
        $this->lastPlugins();
        $query = "update `".$this->prefix."options` set `option_value`='".$newPlugins."' where `".$this->prefix."options`.`option_name`='active_plugins'";
        $this->con->query($query);
        
        return $this->stripPluginFile($pluginsArray);
    }
    
    public function disablePlugins($plugins){
        if (!is_array($plugins)){
            $plugins = array($plugins);
        }
        $activePlugins = $this->pluginsDb;
        $pluginsArray = [];
        foreach($plugins as $plugin){
            $pluginFile = $this->findPluginFile($plugin);
            if (!$pluginFile){
                $pluginsArray[] = $this->nonExistingPlugins[$plugin];
            } else {
                $pluginsArray[] = $pluginFile;
            }
        }
        $result = array_diff($activePlugins, $pluginsArray);
        $newPlugins = serialize(array_values($result));
        $this->lastPlugins();
        $query = "update `".$this->prefix."options` set `option_value`='".$newPlugins."' where `".$this->prefix."options`.`option_name`='active_plugins'";
        $this->con->query($query);
        
        return $this->stripPluginFile($pluginsArray);
    }
    
    public function downloadPlugin($plugin, $version, $result = []){
        $result['success'] = false;
        if ($version === 'CURRENT'){
            $version = $this->pluginVersion($plugin);
            if (empty($version)){
                $result['error'] = 'Cannot find the current plugin version.';
                return $result;
            }
        }
        $result['version'] = $version;
        if ($version == 'LATEST'){
            if(!$this->fetchLatestVer($plugin)){
                $result['plugin-repo'] = false;
                return $result;
            }
            $link = $this->fetchLatestVer($plugin);
        } else {
            if ($this->checkWpRepoVersion($plugin, $version)){
                $link = 'https://downloads.wordpress.org/plugin/'.$plugin.'.'.$version.'.zip';
            } else {
                $result['version-repo'] = false;
                if (!$this->checkWpRepo($plugin)){
                    $result['plugin-repo'] = false;
                } else {
                    $result['plugin-repo'] = true;
                }
                return $result;
            }
        }
        $file = file_get_contents($link);
        if ($file === false) {
            $result['error'] = 'Error getting plugin';
            if (!ini_get('allow_url_fopen')){
                $result['error'] = 'Error getting plugin. It seems like allow_url_fopen is disabled. Please enable it to proceed.';
            }
            return $result;
        }
        $name = $plugin.'.zip';
        file_put_contents($name, $file);
        $zip = new ZipArchive;
        if ($zip->open($name) === TRUE) {
            $zip->extractTo('tmp');
            $zip->close();
        }  else {
            $result['error'] = 'Error during arhive extraction';
            return $result;
        }
        if(is_dir(WP_DIR."/wp-content/plugins/$plugin")){
            $result['old'] = $plugin.'.dis.'.rand(1,999);
            rename( WP_DIR."/wp-content/plugins/".$plugin, WP_DIR."/wp-content/plugins/".$result['old']);
        } else {
            $result['old'] = false;
        }
        $result['success'] = true;
        unlink($name);
        rename("tmp/$plugin", WP_DIR."/wp-content/plugins/".$plugin);
        
        return $result;
    }

    public function pluginRevert(){
        if (!file_exists(TOOLKIT.'/tmp/plugins.tmp')){
            return false;
        }
        $plugins = implode(" ", file(TOOLKIT.'/tmp/plugins.tmp'));
        if (!unserialize($plugins)){
            return false;
        }
        $query = "update `".$this->prefix."options` set `option_value`='".$plugins."' where `".$this->prefix."options`.`option_name`='active_plugins'";
        $this->con->query($query);
        
        return true;
    }
    
    public function pluginUndo(){
        if (!file_exists(TOOLKIT.'/tmp/plugins-last.tmp')){
            return false;
        }        
        $plugins = implode(" ", file(TOOLKIT.'/tmp/plugins-last.tmp'));
        if (!unserialize($plugins)){
            return false;
        }
        $query = "update `".$this->prefix."options` set `option_value`='".$plugins."' where `".$this->prefix."options`.`option_name`='active_plugins'";
        $this->con->query($query);
        
        return true;
    }
    
    public function getPluginsVersions($plugins = []){
        foreach ($this->allPlugins as $plugin){
            $version = $this->pluginVersion($plugin);
            if (!$version){
                $plugins = $plugins + [$plugin => false];
                continue;
            }
            $plugins = $plugins + [$plugin => $version];
        }
        
        return $plugins;
    }
    
    public function removePlugins(array $plugins){
        $result = [];
        foreach ($plugins as $plugin){
            $pluginFolder = WP_DIR.'/wp-content/plugins/'.$plugin;
            if (!$this->pluginExists($pluginFolder)){
                $result[$plugin]['success'] = false;
                $result[$plugin]['error'] = sprintf('Folder for %s plugin not found', $plugin);
                continue;
            }
            if (in_array($plugin, $this->activePlugins)){
                $this->disablePlugins($plugin);
            }
            Helper::delTree($pluginFolder);
            $result[$plugin]['success'] = true;
        }
        
        return $result;
    }
    
    private function fetchAllPlugins($allPlugins = [], $plugins = []){
        $dirs = glob('../wp-content/plugins/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir){
            $results = preg_replace('/\.\.\/wp-content\/plugins\//', '', $dir);
            array_push($allPlugins, $results);
        }
        foreach ($allPlugins as $plugin){
            if (!$this->findPluginFile($plugin)){
                continue;
            }
            array_push($plugins, $plugin);
        }

        return $plugins;
    }
    
    private function fetchPluginsDb(){
        $query = "SELECT * FROM ".$this->prefix."options where option_name = 'active_plugins'";
        $result = $this->con->query($query);
        while($pluginsArray = mysqli_fetch_array($result)){
            $plugins = $pluginsArray['option_value'];
            $activePlugins = unserialize($plugins);
        }
        if (!is_array($activePlugins)){
            $activePlugins = [];
        }
        return $activePlugins;
    }
    
    private function stripPluginFile($plugins, $pluginArray = []){
        if (!is_array($plugins)){
            $plugins = [$plugins];
        }
        foreach ($plugins as $plugin){
            $plugin = strstr($plugin, '/', true);
            array_push($pluginArray, $plugin);
        }

        return $pluginArray;
    }
    
    private function fetchNonExistingPlugins(){
        $plugins = [];
        foreach ($this->pluginsDb as $plugin){
            $pluginFile = WP_DIR.'/wp-content/plugins/'.$plugin;
            if (!$this->pluginExists($pluginFile)){
                $plugins = $plugins + [$this->stripPluginFile($plugin)[0] => $plugin];
            }
        }
        
        return $plugins;
    }
    
    private function fetchActivePlugins(){

        return $this->stripPluginFile($this->pluginsDb);
    }
    
    private function fetchInactivePlugins($inactivePlugins = []){
        foreach ($this->allPlugins as $plugin){
            if (!in_array($plugin, $this->activePlugins)){
                array_push($inactivePlugins, $plugin);
            }
        }
        
        return $inactivePlugins;
    }
    
    private function findPluginFile($plugin){
        if (!is_dir("../wp-content/plugins/".$plugin)){
            return false;
        } 
        $files = glob("../wp-content/plugins/".$plugin."/*.php");
        foreach ($files as $file){
            if(preg_grep("/\bPlugin\sName\b/i", file($file))){
                return preg_replace('/\.\.\/wp-content\/plugins\//', '', $file);
            }
        }
        
        return false;
    }
        
    private function pluginVersion($plugin){
        if (!is_dir("../wp-content/plugins/".$plugin)){
            throw new \Exception(sprintf('No such plugin available: %s', $plugin));
        }
        $searchthis = "Version:";
        $matches = array();
        $handle = @fopen(WP_DIR."/wp-content/plugins/".$this->findPluginFile($plugin), "r");
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
    
    private function checkWpRepo($plugin){
        $link = 'https://wordpress.org/plugins/'.$plugin.'/';
        $handle = curl_init($link);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode == 404 || $httpCode == 302) {
            return false;
        }
        
        return true;
    }
    
    private function checkWpRepoVersion($plugin, $version){
        $link = 'https://downloads.wordpress.org/plugin/'.$plugin.'.'.$version.'.zip';
        $handle = curl_init($link);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode == 404) {
            return false;
        }
        
        return true;
    }
    
    private function fetchLatestVer($plugin){
        if ($this->checkWpRepo($plugin)){
            $fetch = file_get_contents("https://wordpress.org/plugins/$plugin/");
            preg_match('/\<(.*?)\>Download/', $fetch, $match);
            preg_match_all('/"([^"]*)"/', $match[1], $result);
            return $result[1][1];
        }
        
        return false;
    }
    
    private function lastPlugins(){
        $plugins = serialize($this->pluginsDb);
        file_put_contents('tmp/plugins-last.tmp', $plugins);
        return true;
    }
    
    private function pluginsRevertDump(){
        if (!file_exists(TOOLKIT.'/tmp/plugins.tmp')){
            $plugins = serialize($this->pluginsDb);
            file_put_contents('tmp/plugins.tmp', $plugins);
            return true;
        }
        
        return false;
    }
    
    private function pluginExists($plugin){
        if (!file_exists($plugin)){
            return false;
        }
        
        return true;
    }
    
}
