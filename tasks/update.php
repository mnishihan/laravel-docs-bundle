<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Docs_Update_Task
 *
 * @author Shihan
 */
class Docs_Update_Task {
    public function run(){
        $dest_path = Bundle::path('docs') . 'source';
        if(is_dir($dest_path)){
            echo "There already exists a folder named source in bundle directory!" . PHP_EOL;
            echo "Backing up existing source folder." . PHP_EOL;
            $backup_path = $dest_path .".bak";
            File::mvdir($dest_path,$backup_path);
            echo "Backup operation successful." . PHP_EOL;
        }
        $provider = IoC::resolve('bundle.provider: github');
        try{
            $provider->install(array('location'=>'laravel/docs'),$dest_path);
            echo 'Update operation succesfull :-)' . PHP_EOL;
            if(isset($backup_path)){
                File::rmdir($backup_path);
                echo 'Backup folder removed.' . PHP_EOL;
            }
        }
        catch (\Exception $e){
            echo 'Update operation failed :-(' . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            if(isset($backup_path)){
                File::mvdir($backup_path,$dest_path);
                echo "Previous source folder restored from backup." . PHP_EOL;
            }
        }
    }
}

