<?php 

class OtherModel {
    
    private $tmpFiles;
    
    private $toolkitFiles;
    
    public function __construct(){
        $this->tmpFiles = [
            TOOLKIT.'/tmp/config.json',
            TOOLKIT.'/tmp/plugins-last.tmp',
            TOOLKIT.'/tmp/plugins.tmp',
            TOOLKIT.'/tmp/themes.tmp',
            TOOLKIT.'/tmp/themes-last.tmp'
        ];
        $this->toolkitFiles = [
            WP_DIR.'/wp-toolkit.zip',
            WP_DIR.'/wp-toolkit-master.zip',
            WP_DIR.'/wp-toolkit-master',
            TOOLKIT
            ];
    }

    public function flush(){
        foreach($this->tmpFiles as $file){
            if (file_exists($file)){
                @unlink($file);
            }
        }
        
        return true;
    }

    public function deleteWpToolkit(){
        foreach ($this->toolkitFiles as $file){
            if (is_dir($file)){
                Helper::delTree($file);
                continue;
            }
            if (file_exists($file)){
                unlink($file);
            }
        }
    }


}
