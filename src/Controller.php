<?php

class Controller {
    
    public function __construct($loggingIn = false){
        session_start([
            'cookie_lifetime' => 3600,
        ]);
        if (!$loggingIn && !$this->checkCookie()){
            die($this->error("Unfortunately, your session has expired. Please refresh the page and try logging in again."));
        }
    }
    
    public function checkCookie(){
        if (!$_SESSION['wp_toolkit_logged_in']){
            return false;
        }
        
        return true;
    }
    
    public function checkLogin($password){
        if ($this->checkPass($password)){
            $_SESSION['wp_toolkit_logged_in'] = true;
            return $this->response(['loggedIn' => true, 'loginMessage' => '']);
        }
        $file = TOOLKIT.'/tmp/counter.tmp';
        if (!file_exists($file)){
            $counter = 0;
        } else {
           $counter = file_get_contents($file);
        }
        if (!is_numeric($counter) || $counter >= 3){
            return $this->selfDestruct();
        }
        $counter = $counter + 1;
        file_put_contents($file, $counter);
        
        return $this->response(['loggedIn' => false, 'loginMessage' => sprintf('The password is not correct. You have %s tries left.', 4 - $counter)]);
    }
    
    public function checkPass($password){
        if (defined('TOOLKIT_PASS')){
            $hash = password_hash(TOOLKIT_PASS, PASSWORD_DEFAULT);
        } else {
            $hash = '$2y$10$yDyq4U6whfC5QFQOoYFyMeiaG7RMz4gaTF89Ulvcc0kfWpCHtAtyi';
        }
        if (password_verify($password, $hash)){
            return true;
        }
        
        return false;
    }
    
    public function makeCheck(){
        
        return $this->response($this->databaseCheck() + [
            "fopen" => Helper::checkFopen(),
            "multisite" => Helper::isMultisite(),
            "toolkit-ver" => Helper::checkVer()
        ]);
    }
    
    public function databaseCheck(){
        $mysql = new DatabaseModel;
        $dbCheck = $mysql->check();
        if (!$dbCheck['success']){
            foreach ($dbCheck['errors'] as $error){
                return [
                    "database" => false,
                    "error" => $error,
                    "details" => $mysql->details,
                    $error => $dbCheck[$error]];
            }
        }

        return ["database" => true];
    }
    
    public function databaseSubmit($details){
        $mysql = new DatabaseModel($details);
        $dbCheck = $mysql->check();
        if (!$dbCheck['success']){
            foreach ($dbCheck['errors'] as $error){
                return $this->response([
                    "database" => false,
                    "error" => $error,
                    "details" => $mysql->details,
                    $error => $dbCheck[$error]]);
            }
        }

        return $this->response(["database" => true]);
    }
    
    public function selfDestruct(){
        $other = new OtherModel;
        $other->deleteWpToolkit();
        
        return $this->response([
                "removed" => true
            ]);
    }
    
    public function flush(){
        $other = new OtherModel;
        $other->flush();
        
        return $this->success("Temporary files has been removed succesfully",
            $this->getMain()
        );
    }
    
    public function fetchMain(){
        
        return $this->success(false,
            $this->getMain()
        );
    }
    
    public function fetchPlugins(){
        
        return $this->success(false, 
           $this->getPlugins()
        );
    }
    
    
    public function fetchTheme(){
        
        return $this->success(false, 
            $this->getThemes()
        );
    }
    
    public function fetchUsers(){
        
        return $this->success(false, 
            $this->getUsers()
        );
    }

    public function fetchUserInfo($id){
        $id = (int) $id;
        $userOps = new UserOpsModel;
        if (!$userOps->userExists($id)){
            return $this->error('This user does not exist!');
        }
        
        return ($this->success(false,
            $this->getUserinfo($id))
        );
    }
    
    public function fetchVersions(){
        
        return $this->success(false, $this->getThemeVersions() + $this->getPluginVersions() + $this->getFolderData());
    }
    
    public function updateMain($data){
        $main = new MainModel;
        $result = $main->updateData($data);
        if (empty($result)){
            return $this->error(sprintf('No changes were submitted.'));
        }
        $messages = [];
        foreach($result as $key => $item){
            if (isset($item['old']) && isset($item['new'])){
                $messages[$key] = sprintf('"%1$s" has been succesfully updated from "%2$s" to "%3$s"', $key, $item['old'], $item['new']);
            }
        }

        return $this->success(
            implode(". ", $messages),
            $this->getMain()    
        );
    }
    
    public function setStylesheet($theme){
        $themes = new ThemeModel;
        if (empty($theme)){
            return $this->error(sprintf('No theme name was provided. Select one to proceed.'));
        }
        $newTheme = $themes->setTemplate($theme);
        if (!$newTheme){
            return $this->error(sprintf("The %s theme does not exist", $theme));
        }
        
        return $this->success(
            sprintf("%s has been set as a new stylesheet.", $newTheme), 
            $this->getThemes()
        );
    }
    
    public function setTemplate($theme){
        $themes = new ThemeModel;
        if (empty($theme)){
            return $this->error(sprintf('No theme name was provided. Select one to proceed.'));
        }
        $newTheme = $themes->setTemplate($theme);
        if (!$newTheme){
            return $this->error(sprintf("The %s theme does not exist", $theme));
        }
        
        return $this->success(
            sprintf("%s has been set as a new template.", $newTheme), 
            $this->getThemes()
        );
    }
    
    public function changeTheme($theme){
        $themes = new ThemeModel;
        if (empty($theme)){
            return $this->error(sprintf('No theme name was provided. Select one to proceed.'));
        }
        $newTheme = $themes->changeTheme($theme);
        if (!$newTheme){
            return $this->error(sprintf("The %s theme does not exist", $theme));
        }
        
        return $this->success(
            sprintf("%s has been set as a new theme.", $newTheme), 
            $this->getThemes()
        );
    }
    
    public function enablePlugins($plugins){
        $plugin = new PluginModel;
        if (empty($plugins)){
            return $this->error(sprintf('Select at least one plugin.'));
        }
        $enabled = $plugin->enablePlugins($plugins);
        if (is_array($enabled)){
            $enabled = implode(", ",$enabled);
        }
        
        return $this->success(
            sprintf("The following plugins have been enabled: %s", $enabled),
            $this->getPlugins()
        );
    }
    
    public function disablePlugins($plugins){
        $plugin = new PluginModel;
        if (empty($plugins)){
            return $this->error(sprintf('Select at least one plugin.'));
        }
        $disabled = $plugin->disablePlugins($plugins);
        if (is_array($disabled)){
            $disabled = implode(", ",$disabled);
        }
        
        return $this->success(
            sprintf("The following plugins have been disabled: %s", $disabled),
            $this->getPlugins()
        );
    }
    
    public function undoPlugins(){
        $plugin = new PluginModel;
        $undo = $plugin->pluginUndo();
        if (!$undo){
            $this->error("The temporary file containing the data does not exist or is not properly serialized.");
        }
        
        return $this->success(
            sprintf("The last plugin action has been succesfully undone"),
            $this->getPlugins()
        );    
    }
    
    public function revertPlugins(){
        $plugin = new PluginModel;
        $revert = $plugin->pluginRevert();
        if (!$revert){
            $this->error("The temporary file containing the data does not exist or is not properly serialized.");
        }
        
        return $this->success(
            sprintf("The plugins have been reverted back."),
            $this->getPlugins()
        );    
    }
    
    public function undoThemes(){
        $theme = new ThemeModel;
        $undo = $theme->themeUndo();
        if (!$undo){
            $this->error("The temporary file containing the data does not exist or is not properly serialized.");
        }

        return $this->success(
            sprintf("The last theme action has been succesfully undone"),
            $this->getThemes()
        );    
    }
    
    public function revertThemes(){
        $theme = new ThemeModel;
        $revert = $theme->themeRevert();
        if (!$revert){
            $this->error("The temporary file containing the data does not exist or is not properly serialized.");
        }

        return $this->success(
            sprintf("The themes have been reverted"),
            $this->getThemes()
        );    
    }
    
    public function newUser($user, $password, $email, $nicename, $capabilities){
        $userOps = new UserOpsModel;
        if (empty($user) || empty($password)){
            return $this->error("The user/password fields cannot be empty!");
        }
        if ($userOps->userExists($user)){
            return $this->error(sprintf("User '%s' already exists!", $user));
        }
        $newUser = $userOps->newUser($user, $password, $email, $nicename, $capabilities);
        if (!$newUser){
            return $this->error(sprintf("Uknown user level - '%s'.", $capabilities));
        }
        
        return $this->success(
            sprintf("The %s user has been succesfully created", $newUser),
            $this->getUsers()
        );
    }
    
    public function userRoles(){
        $userOps = new UserOpsModel;
        $rolesUpdate = $userOps->setDefaultRoles();
        if (!$rolesUpdate){
            $this->error(sprintf("An error occured during the update of user_roles."));
        }
        
        return $this->success(sprintf('user_roles succesfully updated.'), $this->getUsers());
    }
    
    public function reuploadDefaultTheme($set){
        $themes = new ThemeModel;
        $theme = $themes->reuploadDefault($set);
        if (!$theme){
            return $this->error(sprintf('Unexpected error occured! No default themes found in the reuploaded WordPress installation. It\'s possible this installation is too old.'));
        }
        
        return $this->success(
            sprintf('%s theme has been succesfully reuploaded.', $theme),
            $this->getThemes()
        );
    }
    
    public function reuploadPlugins($list){
        $errors = [];
        if (empty($list)){
            return $this->error(sprintf('No plugin names were provided. Please select at least one to proceed.'));
        }
        $reupload = [];
        foreach ($list as $plugin => $version){
            $result = $this->reuploadPlugin($plugin, $version);
            array_push($errors, $result['success']);
            array_push($reupload, $result['message']);
        }

        return $this->determineResponse(
            implode(" ", $reupload),
            $this->getPluginVersions() + $this->getThemeVersions() + $this->getFolderData(),
            $errors
        );
    }
    
    public function reuploadThemes($list){
        $errors = [];
        if (empty($list)){
            return $this->error(sprintf('No theme names were provided. Please select at least one to proceed.'));
        }
        $reupload = [];
        foreach ($list as $theme => $version){
            $result = $this->reuploadTheme($theme, $version);
            array_push($errors, $result['success']);
            array_push($reupload, $result['message']);
        }

        return $this->determineResponse(
            implode(" ", $reupload),
            $this->getPluginVersions() + $this->getThemeVersions() + $this->getFolderData(),
            $errors
        );
    }
    
    public function reuploadPlugin($plugin, $version){
        $plugins = new PluginModel;
        $reupload = $plugins->downloadPlugin($plugin, $version);
        if (!$reupload['success']){
            if (isset($reupload['error'])){
                return [
                    "message" => $reupload['error'],
                    "success" => false
                ];
            }
            if (!$reupload['plugin-repo']){
                $message = sprintf("Error! The %s plugin has not been found in the WordPress repository!", $plugin);
            } elseif ($reupload['plugin-repo'] && !$reupload['version-repo']){
                $message = sprintf('Error! The %1$s version of %2$s has not been found in the WordPress repository! The plugin itself has been found. Please try another version', 
                $version, $plugin);
            } else {
                $message = sprintf("Error! An unexpected error occurred during the reuploading of %s plugin. Please check the logs", $plugin);
            }
            return [
                "message" => $message,
                "success" => false
            ];
        }
        
        
        return [
            "message" => sprintf('Success! %1$s plugin of %2$s version has been successfully reuploaded. The old plugin\'s folder has been renamed to %3$s.',
            $plugin, $reupload['version'], $reupload['old']),
            "success" => true
            ];
    }
    
    public function reuploadTheme($theme, $version){
        $themes = new ThemeModel;
        $reupload = $themes->downloadTheme($theme, $version);
        if (!$reupload['success']){
            if (!$reupload['theme-repo']){
                $message = sprintf("Error! The %s theme has not been found in the WordPress repository!", $theme);
            } elseif ($reupload['theme-repo'] && !$reupload['version-repo']){
                $message = sprintf('Error! The %1$s version of %2$s has not been found in the WordPress repository! The theme itself has been found. Please try another version', 
                $version, $theme);
            } else {
                $message = sprintf("An unexpected error occurred during the reuploading of %s theme. Please check the logs", $theme);
            }
            return [
                "message" => $message,
                "success" => false
            ];
        }
        
        return [
            "message" => sprintf('%1$s theme of %2$s version has been successfully reuploaded. The old theme\'s folder has been renamed to %3$s.', $theme, $version, $reupload['old']),
            "success" => true
        ];
    }
    
    public function resetPass($user, $password){
        $user = (int) $user;
        $userOps = new UserOpsModel;
        if (!$userOps->userExists($user)){
            return $this->error('This user does not exist!');
        }
        $users = new UserModel($user);
        if (empty($password)){
            return $this->error('Password must not be empty.');
        }
        $users->resetPass($password);
        
        return $this->success(
            sprintf('The password for %s user has been succesfully set.', $users->userinfo['username']),
            []
        );
    }
    
    public function setUsermeta($user, $level){
        $user = (int) $user;
        $userOps = new UserOpsModel;
        if (!$userOps->userExists($user)){
            return $this->error('This user does not exist!');
        }
        $users = new UserModel($user);
        $set = $users->setUsermeta($level);
        if (!$set){
            return $this->error(sprintf('The "%s" user level does not exist in WordPress Toolkit configuration. Please select one of the available ones', $level));
        }
        
        return $this->success(
            sprintf('The "%1$s" user level has been succesfully set for the %2$s user.', $users->userPrivileges[$level], $users->userinfo['username']),
            $this->getUserInfo($user)
        );
    }
    
    public function deleteUser($user){
        $user = (int) $user;
        $userOps = new UserOpsModel;
        if (!$userOps->userExists($user)){
            return $this->error('This user does not exist!');
        }
        $users = new UserModel($user);
        $users->deleteUser();
        
        return $this->success(
            sprintf('The %s user has been succesfully deleted.', $users->userinfo['username']),
            $this->getUsers()
        );
    }
    
    public function reuploadDefault(){
        $files = new FilesModel;
        $reupload = $files->reanimate();
        if (!$reupload['version']){
            return $this->error(sprintf('Can\'t find the correct version. %s/wp-includes/version.php does not exist!', WP_DIR));
        }
        
        return $this->success(
            sprintf('The default files have been succesfully reuploaded. Old files have been put into %s.', WP_DIR.'/wp-oldfiles'), 
            $this->getThemeVersions() + $this->getPluginVersions() + $this->getFolderData()
        );
    }
    
    public function removeOldFiles(){
        $files = new FilesModel;
        $old = $files->removeOldFiles();
        if (!$old){
            return $this->error(sprintf('Error! wp-oldfiles folder not found!'));
        }
        
        return $this->success(
            sprintf("wp-oldfiles folder has been succesfully deleted."),
            $this->getThemeVersions() + $this->getPluginVersions() + $this->getFolderData()
        );
    }
    
    public function cacheOperations($action){
        $additional = '';
        $files = new FilesModel;
        if ($action === 'enable'){
            $result = $files->enableCache();
            if (!$result['success']){
                if (!$result['exists']){
                    return $this->error(sprintf('The disabled cache folder not found.'));
                }
                if (!$result['enabled']){
                    return $this->error(sprintf('Cache folder already exists.'));
                }
            }
        } else if ($action === 'disable'){
            $result = $files->disableCache();
            if (!$result){
                return $this->error(sprintf('Cache folder not found!'));
            }
            $additional = sprintf('The current folder name is %s.', $result);
        } else if ($action === 'remove'){
            $result = $files->removeCache();
            if (!$result){
                return $this->error(sprintf('Cache folder not found!'));
            }
        } else {
            return $this->error(sprintf('Unknown cache action: %s!', $action));
        }
        
        return $this->success(
            sprintf('Cache folder has been succesfully %1$sd. %2$s', $action, $additional),
            $this->getThemeVersions() + $this->getPluginVersions() + $this->getFolderData()
        );
    }
    
    public function cacheConfigOperations($action){
        $additional = '';
        $files = new FilesModel;
        if ($action === 'enable'){
            $result = $files->enableTCConfig();
            if (!$result['success']){
                if (!$result['exists']){
                    return $this->error(sprintf('The disabled cache folder not found.'));
                }
                if (!$result['enabled']){
                    return $this->error(sprintf('Total Cache config folder already exists.'));
                }
            }
        } else if ($action === 'disable'){
            $result = $files->disableTCConfig();
            if (!$result){
                return $this->error(sprintf('Total Cache config folder not found!'));
            }
            $additional = sprintf('The current folder name is %s.', $result);
        } else if ($action === 'remove'){
            $result = $files->removeTCConfig();
            if (!$result){
                return $this->error(sprintf('Total Cache config folder not found!'));
            }
        } else {
            return $this->error(sprintf('Unknown action for Total Cache config: %s!', $action));
        }
        
        return $this->success(
            sprintf('Total Cache config folder has been succesfully %1$sd. %2$s', $action, $additional),
            $this->getThemeVersions() + $this->getPluginVersions() + $this->getFolderData()
        );
    }
    
    public function removePlugins($plugins, $source){
        $errors = [];
        $pluginModel = new PluginModel;
        if (empty($plugins)){
            return $this->error('Select at least one plugin.');
        }
        if (!is_array($plugins)){
            $plugins = [$plugins];
        }
        $result = [];
        $removal = $pluginModel->removePlugins($plugins);
        foreach ($removal as $plugin => $value){
            if (!$value['success']){
                array_push($errors, false);
                array_push($result, sprintf('The following error has been encountered during the removal: %s.', $value['error']));
                continue;
            }
            array_push($errors, true);
            array_push($result, sprintf('The %s plugin has been removed succesfully.', $plugin));
        }
        if ($source === 'plugins'){
            $data = $this->getPlugins();
        } else if ($source === 'reupload') {
            $data = $this->getPluginVersions() + $this->getThemeVersions() + $this->getFolderData();
        } 
        
        return $this->determineResponse(implode(" ", $result), $data, $errors);
    }
    
    public function removeThemes($themes, $source){
        $errors = [];
        $themeModel = new ThemeModel;
        if (empty($themes)){
            return $this->error('Select a theme to remove.');
        }
        if (!is_array($themes)){
            $themes = [$themes];
        }
        $result = [];
        $removal = $themeModel->removeThemes($themes);
        foreach ($removal as $theme => $value){
            if (!$value['success']){
                array_push($errors, false);
                array_push($result, sprintf('The following error has been encountered during the removal: %s.', $value['error']));
                continue;
            }
                array_push($errors, true);
            array_push($result, sprintf('The %s theme has been removed succesfully.', $theme));
        }
        if ($source === 'themes'){
            $data = $this->getThemes();
        } else if ($source === 'reupload') {
            $data = $this->getPluginVersions() + $this->getThemeVersions() + $this->getFolderData();
        } 
        
        return $this->determineResponse(implode(" ", $result), $data, $errors);
    }
    
    public function success($message, $data){
        
        return $this->response([
            "success" => true,
            "message" => $message,
            "data" => $data]
        );
    }
    
    public function error($message, $data = false){
        if ($data){
            return $this->response([
                "success" => false,
                "message" => $message,
                "data" => $data
            ]);
        }
        
        return $this->response([
            "success" => false,
            "message" => $message
        ]);
    }
    
    public function info($message, $data){
        
        return $this->response([
            "success" => true,
            "info" => true,
            "message" => $message,
            "data" => $data]
        );
    }
    
    public function warn($message, $data){
        
        return $this->response([
            "success" => true,
            "warn" => true,
            "message" => $message,
            "data" => $data]
        );
    }
    
    private function response(array $data){
        
        return json_encode($data);
    }
    
    private function getMain(){
        $main = new MainModel;
        
        return [
            "siteurl" => $main->siteurl,
            "home" => $main->home,
            "title" => $main->title,
            "desc" => $main->desc
        ];
    }
    
    private function getPlugins(){
        $plugins = new PluginModel;
        
        $pluginsArray = [
            "active" => $plugins->activePlugins,
            "inactive" => $plugins->inactivePlugins
        ];
        if ($plugins->nonExistingPlugins){
            $nonExisting = [];
            foreach($plugins->nonExistingPlugins as $plugin => $file){
                array_push($nonExisting, $plugin);
            }
            $pluginsArray = $pluginsArray + ['nofolder' => $nonExisting];
        }
        
        
        return $pluginsArray;
    }
    
    private function getThemes(){
        $theme = new ThemeModel;
        
        return [
            "template" => $theme->template,
            "stylesheet" => $theme->stylesheet,
            "themes" => $theme->themes
        ];
    }
    
    private function getUsers(){
        $users = new UserOpsModel;
        $privileges = $users->userPrivileges;
        unset($privileges['unknown'], $privileges['empty'], $privileges['not-set']);
        
        return ['users' => $users->users, 'levels' => $privileges, 'roles' => $users->userPermissions];
    }
    
    private function getUserInfo($id) {
        $user = new UserModel($id);

        return $user->userinfo + $user->checkUsermeta($id);
    }
    
    private function getCache(){
        $files = new FilesModel;
        
        return ['cache' => $files->checkCacheFolder()];
    }
    
    private function getPluginVersions(){
        $plugins = new PluginModel;
        
        return ['plugins' => $plugins->getPluginsVersions()];
    }
    
    private function getThemeVersions(){
        $themes = new ThemeModel;
        
        return ['themes' => $themes->getThemeVersions()];
    }
    
    private function getFolderData(){
        $files = new FilesModel;
        
        return [
            'old-files' => $files->checkOldFiles(),
            'cache-folder' => $files->checkCacheFolder(),
            'cache-folder-dis' => $files->checkDisCacheFolder(),
            'tc-config' => $files->checkTCConfig(),
            'tc-config-dis' => $files->checkDisTCConfig()
        ];
    }
    
    private function determineResponse($message, $data, $errors){
        $check = $this->checkErrors($errors);
        if ($check['warn']){
            return $this->warn($message, $data);
        } elseif ($check['error']){
            return $this->error($message, $data);
        }
        
        return $this->success($message, $data);
    }
    
    private function checkErrors($result){
        $errors = [];
        if (!in_array(true, $result)){
            $errors['error'] = true;
            return $errors;
        }
        foreach ($result as $item){
            if ($item == false){
                $errors['warn'] = true;
                return $errors;
            }
        }
        $errors['success'] = true;
        
        return $errors;
    }
    
}
