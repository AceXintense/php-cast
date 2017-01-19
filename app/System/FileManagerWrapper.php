<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 1/11/17
 * Time: 11:00 PM
 */

namespace App\System;


use App\URLRequest;

class FileManagerWrapper {

    private static $instance;

    /**
     * Singleton class.
     * @return FileManagerWrapper
     */
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Deletes the file with the specified $fileName from the database and the /Stream directory.
     * @param $fileName
     */
    private function deleteFileRecord($fileName) {
        /** @var URLRequest $record */
        $record = URLRequest::where('fileName', $fileName)->first();
        shell_exec('cd /Stream && sudo rm ' . $record->fileName);//Remove the file from the /Stream directory.
        $record->delete(); //Delete $record from the database.
    }

    /**
     * Creates a new record in the URLRequest table and also assigns the fileName to it.
     * @param $url
     * @param $fileName
     */
    private function newFileRecord($url, $fileName) {

        /** @var URLRequest $record */
        $record = new URLRequest();
        $record->url = $url;
        $record->status = 'Requested';
        $record->fileName = $fileName;
        $record->save();

    }

    /**
     * Download a file to the server and create a record in the database.
     * @param $url
     * @param bool $formatFileName
     * @return string
     * @throws \Exception
     */
    public function downloadFile($url, $formatFileName = false) {

        try {
            //Changes directory to /Stream and downloads the specified file from the $url.
            exec('cd /Stream; sudo scdl -l ' . $url);
            $fileName = exec('cd /Stream && ls -t1 |  head -n 1'); //Get the latest created file in the directory.
            $this->newFileRecord($url, $fileName); //Create the record in the database.

            if ($formatFileName) {
                $utilities = Utilities::getInstance();
                $fileName = $utilities->removeExtension($fileName);
            }

            return $fileName;
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }

    }

    /**
     * Removes a file from the /Stream directory and from the database.
     * @param $fileName
     * @throws \Exception
     */
    public function removeFile($fileName) {

        try {
            $this->deleteFileRecord($fileName);
            shell_exec('sudo rm /Stream/"' . $fileName . '"');
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }

    }

    /**
     * Clears the /Stream directory and also removes all records in the database.
     * @return bool
     * @throws \Exception
     */
    public function clearQueue() {

        try {
            if (count(URLRequest::all()) > 0) { //Get the count of all the records in the database.
                URLRequest::truncate(); //Remove all records in the database.
                shell_exec('sudo rm -R /Stream && sudo mkdir /Stream'); //Remove the /Stream directory and then recreate it.
                return true;
            }
            return false;
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }

    }

}