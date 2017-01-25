<?php

namespace App\Http\Controllers;

use App\Option;
use App\System\FileManagerWrapper;
use App\System\MPlayerWrapper;
use App\System\Utilities;
use App\URLRequest;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\URL;

class RequestController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get the getPlaying song.
     * @return string
     */
    public function getPlaying() {

        /** @var URLRequest $playing */
        $playing = URLRequest::where('status', 'Playing')->orWhere('status', 'Paused')->first();
        if (empty($playing)) {
            return 'No song is currently playing.';
        }

        //Remove the .mp3 extension from the filename.
        return Utilities::getInstance()->removeExtension($playing->fileName);

    }

    /**
     *  Get all the Requested URLs in the database.
     *  @return array
     */
    public function getRequestedURLs() {
        //TODO: Remove .mp3 off the end of the records to make it look better.
        return URLRequest::all()->toArray();
    }

    /**
     * Gets the value of shuffle from the database.
     * @return boolean
     */
    public function getShuffle() {

        $MPlayer = MPlayerWrapper::getInstance();
        return $MPlayer->getShuffle() ? 'true' : 'false';

    }

    /**
     * Toggles shuffle mode.
     */
    public function toggleShuffle() {

        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->toggleShuffle();

    }

    /**
     * Gets the value of play_through from the database.
     * @return boolean
     */
    public function getPlayThrough() {

        $MPlayer = MPlayerWrapper::getInstance();
        return $MPlayer->getPlayThrough() ? 'true' : 'false';

    }

    /**
     * Toggles play_through mode.
     */
    public function togglePlayThrough() {

        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->togglePlayThrough();

    }

    /**
     * Gets the value of play_through_direction from the database.
     * @return boolean
     */
    public function getPlayThroughDirection() {

        $MPlayer = MPlayerWrapper::getInstance();
        return $MPlayer->getPlayThroughDirection();

    }

    /**
     * Toggles play_through_direction.
     */
    public function togglePlayThroughDirection() {

        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->togglePlayThroughDirection();

    }

    /**
     * Add Request to the Database.
     *
     * @param Request $request
     * @return string
     */
    public function addRequest(Request $request) {

        //Create a Utilities class so we can use the needed functions on it.
        $utilities = Utilities::getInstance();
        //Get the requestedURL from the API request.
        $requestedURL = $request->get('requestedURL');

        if (!$utilities->validURL($requestedURL)) { //Check to see if the requestedURL is a valid URL
            return [
                'type' => 'Error',
                'content' => 'The requested URL is not a URL please try again.'
            ];
        }

        /** @var URLRequest $existingRecord */
        $existingRecord = URLRequest::all()->where('status', 'Requested')->where('url', $requestedURL);
        if (count($existingRecord)) { //check to see if there is a record with that URL in the queue.
            return [
                'type' => 'Error',
                'content' => "$requestedURL is all ready in the queue."
            ];
        }
        try {
            $fileName = $this->downloadFile($requestedURL); //Download the file using the requestedURL.
        } catch (\Exception $e) { //Catch any errors from the downloadFile function.
            return [
                'type' => 'Error',
                'content' => $e
            ];
        }

        return [
            'type' => 'Success',
            'content' => "Successfully added $fileName to the queue."
        ];

    }

    /**
     * Download the file from the URL and then add the fileName to the database.
     * @param $url string
     * @return string
     */
    private function downloadFile($url) {

        //Use the FileManagerWrapper to download the file and add a new record to the database.
        $FileManager = FileManagerWrapper::getInstance();
        return $FileManager->downloadFile($url, true);

    }

    /**
     * Check to see if there is a difference between the server and the front-end.
     * @param Request $request
     * @return array
     */
    public function isQueueDifferent(Request $request) {

        //Get the JS queue array.
        $queue = $request->get('queue');

        //Check to see if the user has submitted an array.
        if (empty($queue)) {
            return [
                'type' => 'Error',
                'content' => "Passed array is empty!"
            ];
        }

        //Get the instance of MPlayerWrapper so we can call methods on it.
        $MPlayer = MPlayerWrapper::getInstance();
        if ($MPlayer->isTableDifferentFromArray($queue)) { //Check to see if there is a difference between the table and the front-end.
            return [
                'type' => 'Success',
                'content' => "There is a difference between the arrays.",
                'boolean' => 'true'
            ];
        }

        //Return the findings.
        return [
            'type' => 'Success',
            'content' => "No difference between the arrays.",
            'boolean' => 'false'
        ];

    }

    /**
     * Plays the file with the Filename it also updates the database with the status.
     * @param Request $request
     */
    public function playFile(Request $request) {

        //Get the fileName from the API request.
        $fileName = $request->get('fileName');

        //Get the instance of MPlayerWrapper so we can call methods on it.
        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->playFile($fileName); //Play the specified file from the fileName data.

    }

    /**
     * Skip to the next file in the queue.
     */
    public function skipToNext() {

        //Get the instance of MPlayerWrapper so we can call methods on it.
        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->skipToNextFile(); //Get and set the next record to play.

    }

    /**
     * Skip to the previous file in the queue.
     */
    public function skipToPrevious() {

        //Get the instance of MPlayerWrapper so we can call methods on it.
        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->skipToPreviousFile(); //Get and set the previous record to play.

    }

    /**
     * This function clears the url_requests table of all the requests it also removes the files linked to the records.
     * @param Request $request
     * @return array
     */
    public function clearQueue(Request $request) {

        //Get the fileName from the API request.
        $fileName = $request->get('fileName');

        //Get the instance of MPlayerWrapper so we can call methods on it.
        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->stopFile($fileName);

        //Get the instance of FileManagerWrapper so we can call methods on it.
        $FileManager = FileManagerWrapper::getInstance();
        if ($FileManager->clearQueue()) { //Check to see if there is any records in the queue and delete them.
            return [
                'type' => 'Success',
                'content' => 'Cleared the queue successfully.'
            ];
        }

        //There is no records in the database to delete / update.
        return [
            'type' => 'Warning',
            'content' => 'There is nothing in the queue to clear.'
        ];

    }

    /**
     * Removes a file from the Server and the database.
     * @param Request $request
     * @return array
     */
    public function removeFile(Request $request) {

        //Get the fileName from the API request.
        $fileName = $request->get('fileName');

        //Get the FileManagerWrapper instance so we can call functions on it.
        $FileManager = FileManagerWrapper::getInstance();
        $FileManager->removeFile($fileName); //Remove the file from the database and also remove it from the /Stream directory.

        return [
            'type' => 'Success',
            'content' => 'Successfully removed ' . $fileName
        ];

    }

    public function stopFile(Request $request) {

        //Get the fileName from the API request.
        $fileName = $request->get('fileName');

        //Get the MPlayerWrapper instance so we can call functions on it.
        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->stopFile($fileName); //Stop the file that is currently getPlaying.

        return [
            'type' => 'Success',
            'content' => 'Stopped ' . $fileName . ' Successfully'
        ]; //Return the fileName to the front end and display it in a notification form.

    }

    /**
     * Pauses the file that is being used by mplayer it also updates the database with these changes.
     * @param Request $request
     * @return string
     */
    public function setPaused(Request $request) {

        //Get the fileName from the API request.
        $fileName = $request->get('fileName');

        //Get the MPlayerWrapper instance so we can call functions on it.
        $MPlayer = MPlayerWrapper::getInstance();
        return $MPlayer->setPaused($fileName); //Return the status of the record after toggling the paused state.

    }

    /**
     * Return boolean of the state of the database.
     * @return mixed
     */
    public function isPaused() {

        //Get the MPlayerWrapper instance so we can call functions on it.
        $MPlayer = MPlayerWrapper::getInstance();
        return $MPlayer->isPaused() ? 'true' : 'false';

    }

    /**
     * Gets the volume value from the PCM.
     * @return string
     */
    public function getVolume() {

        //Get the MPlayerWrapper instance so we can call functions on it.
        $MPlayer = MPlayerWrapper::getInstance();
        return $MPlayer->getVolume(); //Return the value of the volume on the hardware.

    }

    /**
     * Sets the volume for the amixer output.
     * Returns the shells output for debugging.
     * @param Request $request
     */
    public function setVolume(Request $request) {

        //Get the volume from the API request.
        $volume = $request->get('volume');

        //Get the instance of MPlayerWrapper so we can call methods on it.
        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->setVolume($volume); //Set the volume of the hardware using the MPlayer Wrapper.

    }

    /**
     * Resets all the variables in the MPlayer Wrapper and stops all the playback.
     * @return bool
     */
    public function resetEnvironment() {

        //Get the instance of MPlayerWrapper so we can call methods on it.
        $MPlayer = MPlayerWrapper::getInstance();
        $MPlayer->reset(); //Reset all the records and the methods that are on the records.

        return true;

    }

    /**
     * Simple function to show the PHP Info to developers.
     */
    public function phpInfo() {
        phpinfo(); //Show the PHP info.
        die();
    }
}
