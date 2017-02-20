<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 1/10/17
 * Time: 8:21 PM
 */

namespace App\System;


use App\Option;
use App\URLRequest;

class MPlayerWrapper {

    private static $instance;
    private static $shuffle;
    private static $playThrough;
    private static $playThroughDirection;
    private $stopping;
    private $table;
    private $extention = '.mp3';

    /**
     * Singleton class.
     * @return MPlayerWrapper
     */
    public static function getInstance() {

        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;

    }

    /**
     * Create the named pipe for MPlayer.
     */
    private function initializeControlFile() {

        if (!file_exists('/tmp/control')) { //Check to see if it already exists.
            exec('sudo mkfifo /tmp/control'); //Create the named pipe.
            exec('sudo chmod 777 /tmp/control'); //Change the permissions to read, write, execute to anyone.
        }

    }

    /**
     * Get the file that has the status of "Playing"
     */
    private function getPlayingFile() {

        try {
            $file = URLRequest::where('status', 'Playing')->first();
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }

        return $file;
    }

    /**
     * Update the fileName and the status.
     * @param $fileName
     * @param $status
     * @param bool $addToCounter
     */
    private function updateFileRecord($fileName, $status, $addToCounter = false) {

        /** @var URLRequest $record */
        $record = URLRequest::where('fileName', $fileName)->first();

        if ($record) {//Check to see if the record exists.
            if ($addToCounter) {
                $record->times_played ++;
            }
            $record->status = $status;
            $record->save();
        }

    }

    /**
     * Get the shuffle status.
     * @return bool
     */
    public function getShuffle() {
        /** @var Option $shuffle */
        $shuffle = Option::where('name', 'shuffle')->first();
        return (boolean) $shuffle->value; //Return the value.

    }

    /**
     * Toggles shuffle in the database.
     */
    public function toggleShuffle() {
        /** @var Option $shuffle */
        $shuffle = Option::where('name', 'shuffle')->first(); //Check to see if we have shuffle in the Options table.
        $shuffle->value = ($shuffle->value = !$shuffle->value); //Toggle the value from true to false
        $shuffle->save();

    }

    /**
     * Get the play_through status.
     * @return bool
     */
    public function getPlayThrough() {
        /** @var Option $playThrough */
        $playThrough = Option::where('name', 'play_through')->first();
        return (boolean) $playThrough->value; //Return the value.

    }

    /**
     * Toggles play_through in the database.
     */
    public function togglePlayThrough() {
        /** @var Option $playThrough */
        $playThrough = Option::where('name', 'play_through')->first(); //Check to see if we have play_through in the Options table.
        $playThrough->value = ($playThrough->value = !$playThrough->value); //Toggle the value from true to false
        $playThrough->save();

    }

    /**
     * Get the play_through_direction from the database.
     * @return string
     */
    public function getPlayThroughDirection() {
        /** @var Option $playThroughDirection */
        $playThroughDirection = Option::where('name', 'play_through_direction')->first();
        return $playThroughDirection->value; //Return the value.

    }

    /**
     * Toggles the play_through_direction in the database.
     */
    public function togglePlayThroughDirection() {
        /** @var Option $playThroughDirection */
        $playThroughDirection = Option::where('name', 'play_through_direction')->first(); //Check to see if we have play_through in the Options table.
        if ($playThroughDirection->value === 'up') { //If the value is up then set the value to down.
            $playThroughDirection->value = 'down';
        } else { //Toggle the oposite.
            $playThroughDirection->value = 'up';
        }
        $playThroughDirection->save();
    }

    /**
     * Returns true if there is a file getPlaying.
     */
    private function isAlreadyPlaying() {
        return (boolean) URLRequest::where('status', 'Playing')->orWhere('status', 'Paused')->first();
    }

    /**
     * Check to see if there is a difference in the table structure.
     * @param $array
     * @return bool
     */
    public function isTableDifferentFromArray($array) {

        //Make sure that the ids in the array are integers! JS removes the datatype.
        $array = $this->sanitizeJSArray($array);
        //Get the latest table in an array.
        $this->table = URLRequest::all()->toArray();
        sort($this->table);
        sort($array);

        //Compare the JS table and the one on the server.
        if ($this->table == $array) {
            //There is no difference between the arrays.
            return false;
        }
        //Return true if there is a difference between the arrays.
        return true;

    }

    /**
     * Play the file from MPlayer using the fileName parameter.
     * @param $fileName
     * @return bool
     */
    public function playFile($fileName) {

        //Create the control pipe file if there is not one present.
        $this->initializeControlFile();

        //Check to see if there is already a file getPlaying if so stop it!
        if ($this->isAlreadyPlaying()) {
            $this->stopPlayingFiles(); //Stop all playback from the MPlayer wrapper.
        }

        $this->updateFileRecord($fileName, 'Playing', true); //Update the file status to played in the database.
        //Play the specified fileName.
        //TODO: Simplify this command and also remove the /tmp/control and put it in the config. Same for the /Stream directory.
        shell_exec("sudo mplayer -input file=/tmp/control /Stream/\"$fileName\"");
        $this->updateFileRecord($fileName, 'Played'); //Update the file status to played in the database.

        if ($this->stopping) {//Stopping is true then stop execution.
            $this->stopping = false;
            return true; //Return true to stop execution.
        }

        self::$shuffle = $this->getShuffle(); //Get the shuffle status.
        if (self::$shuffle) { //Check to see if shuffle is active.
            $fileName = URLRequest::all()->random(); //Get a random record in the table.
            $this->playFile($fileName);
        }

        self::$playThrough = $this->getPlayThrough(); //Get the playThough status.
        if (self::$playThrough) { //Check to see if play_through is active.
            self::$playThroughDirection = $this->getPlayThroughDirection();
            if (self::$playThroughDirection == 'up') { //If the value up then go up the list.
                $this->playPreviousFileFromPreviousFileName($fileName);
            }//Else go down the list.
            $this->playNextFileFromPreviousFileName($fileName);
        }

    }

    /**
     * Stop MPlayers playback it also resets the getPlaying files in the database.
     */
    private function stopPlayingFiles() {

        //Stop the MPlayer instance via the control named pipe.
        exec('sudo echo "quit" > /tmp/control');
        $this->stopping = true;

        /** @var URLRequest $playingFile */
        $playingFile = $this->getPlayingFile(); //Get the currently getPlaying file.
        $this->updateFileRecord($playingFile->fileName, 'Played'); //Update the file status to played in the database.

    }

    /**
     * Stop the file with the fileName
     * @param $fileName
     */
    public function stopFile($fileName) {

        /** @var URLRequest $playingFile */
        try {
            $playingFile = $this->getPlayingFile();
        } catch (\Exception $exception) {
            die($exception);
        }

        if (!empty($playingFile->fileName)) {
            $this->stopping = true;
            $this->updateFileRecord($fileName, 'Played'); //Update the file status to played in the database.
            //Stop the MPlayer instance via the control named pipe.
            if ($playingFile->fileName == $fileName) {
                exec('sudo echo "quit" > /tmp/control');
            }
        }

    }

    /**
     * Play the next file in the queue from the previously played fileName.
     * @param $fileName
     */
    public function playNextFileFromPreviousFileName($fileName) {

        $index = 0;

        //Get all the requests from the table.
        $records = URLRequest::all()->toArray();

        foreach ($records as $record) {
            $index ++; //Increment $index as this will indicate where we are in the array.
            if ($record['fileName'] == $fileName) { //Check to see if the fileName matches the one in the array.
                $index += 1; //Minus one to get the next request in the array.
                $nextFile = $records[$index - 1]['fileName']; //Get the next file from the array -1 again to fix the 0, 1 issue.
            }
        }

        //Play the next file in the queue.
        $this->playFile($nextFile);

    }

    /**
     * Play the previous file in the queue from the previously played fileName.
     * @param $fileName
     */
    public function playPreviousFileFromPreviousFileName($fileName) {

        $index = 0;

        //Get all the requests from the table.
        $records = URLRequest::all()->toArray();

        foreach ($records as $record) {
            $index ++; //Increment $index as this will indicate where we are in the array.
            if ($record['fileName'] == $fileName) { //Check to see if the fileName matches the one in the array.
                $index -= 1; //Minus one to get the previous request in the array.
                $previousFile = $records[$index - 1]['fileName']; //Get the previous file from the array -1 again to fix the 0, 1 issue.
            }
        }

        //Play the previous file in the queue.
        $this->playFile($previousFile);

    }

    /**
     * Play the next file in the queue.
     */
    public function skipToNextFile() {

        $index = 0;
        /** @var URLRequest $file */
        $file = $this->getPlayingFile(); //Get the file that is currently getPlaying.

        //Get all the requests from the table.
        $records = URLRequest::all()->toArray();

        foreach ($records as $record) {
            $index ++; //Increment $index as this will indicate where we are in the array.
            if ($record['fileName'] == $file->fileName) { //Check to see if the fileName matches the one in the array.
                $index += 1; //Minus one to get the next request in the array.
                $nextFile = $records[$index - 1]['fileName']; //Get the next file from the array -1 again to fix the 0, 1 issue.
            }
        }

        //Play the next file in the queue.
        $this->playFile($nextFile);

    }

    /**
     * Play the previous file in the queue.
     */
    public function skipToPreviousFile() {

        $index = 0;
        /** @var URLRequest $file */
        $file = $this->getPlayingFile(); //Get the file that is currently getPlaying.

        //Get all the requests from the table.
        $records = URLRequest::all()->toArray();

        foreach ($records as $record) {
            $index ++; //Increment $index as this will indicate where we are in the array.
            if ($record['fileName'] == $file->fileName) { //Check to see if the fileName matches the one in the array.
                $index -= 1; //Minus one to get the previous request in the array.
                $previousFile = $records[$index - 1]['fileName']; //Get the previous file from the array -1 again to fix the 0, 1 issue.
            }
        }

        //Play the previous file in the queue.
        $this->playFile($previousFile);

    }

    /**
     * Resets all the records in the database and stops getPlaying content.
     */
    public function reset() {

        $this->stopPlayingFiles(); //Stop all playback from all the records.

        /** @var URLRequest $playingFile */
        $playingFile = $this->getPlayingFile(); //Get the getPlaying file.
        $this->updateFileRecord($playingFile->fileName, 'Played'); //Reset the playingFile to have the status of played.

    }

    /**
     * Loop though the JS array and make the id field an integer.
     * @param $array
     * @return mixed
     */
    private function sanitizeJSArray($array) {

        //Loop over the array and get the elements from it.
        foreach ($array as &$element) {
            $element['id'] = (int) $element['id']; //Get the id element and set it to itself but cast it to a int.
        }
        return $array; //Return the modified array to the function.

    }

    /**
     * Set the volume on the hardware.
     * @param $volume
     */
    public function setVolume($volume) {
        //Change this from PCM to Master or what ever amixer defines. PCM is the Pi's default this is why I am using it!
        //TODO: Remove PCM and replace it with a global variable where we can define it in a config class!
        shell_exec("sudo amixer --quiet set PCM $volume%");
    }

    /**
     * Get the current volume of the hardware.
     * @return string
     */
    public function getVolume() {
        //Change this from PCM to Master or what ever amixer defines. PCM is the Pi's default this is why I am using it!
        //TODO: Remove PCM and replace it with a global variable where we can define it in a config class!
        return shell_exec("sudo amixer get PCM | awk '$0~/%/{print $4}' | tr -d '[]%';");
    }

    /**
     * Set the file to paused or to getPlaying all dependant on the current status.
     * @param $fileName
     * @return string
     */
    public function setPaused($fileName) {

        if (!strpos($fileName, $this->extention)) {
            $fileName = $fileName . $this->extention;
        }

        //Write to the named pipe 'pause' which will command MPlayer to pause playback.
        //TODO: Remove /tmp/control and replace it with a global value in the config too!
        exec('sudo echo "pause" > /tmp/control');

        /** @var URLRequest $record */
        $record = URLRequest::where('fileName', $fileName)->first();
        if ($record->status == 'Playing') { //If the record is plating then toggle it.
            $record->status = 'Paused';
        } else { //Toggle the opposite values.
            $record->status = 'Playing';
        }
        $record->save();

        //Return the status to the API.
        return $record->status;

    }

    /**
     * Returns true if the playback is paused and false if the playback is getPlaying.
     * @return bool
     */
    public function isPaused() {

        //Get the paused record in the table if there is one.
        $record = URLRequest::where('status', 'Paused')->first();
        if (!empty($record)) {//Check to see if the array has a paused record in it.
            return true;
        } else {
            return false;
        }

    }

}