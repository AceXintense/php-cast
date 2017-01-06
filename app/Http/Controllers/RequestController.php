<?php

namespace App\Http\Controllers;

use App\Option;
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
     * Get the playing song.
     * @return array
     */
    public function getPlaying() {
        return URLRequest::where('status', 'Playing')->orWhere('status', 'Paused')->first();
    }

    /**
     *  Get all the Requested URLs in the database.
     *  @return array
     */
    public function getRequestedURLs() {
        return URLRequest::all()->toArray();
    }

    /**
     * Gets the value of shuffle from the database.
     * @return array
     */
    public function getShuffle() {
        /** @var Option $shuffle */
        $shuffle = Option::where('name', 'shuffle')->first();
        if ($shuffle) {
            return [
                'state' => $shuffle->value
            ];
        } else {
            return [
                'state' => 'false'
            ];
        }
    }

    /**
     * Sets shuffle mode.
     * @return array
     */
    public function setShuffle() {
        //Check to see if we have shuffle in the Options table.
        /** @var Option $shuffle */
        $shuffle = Option::where('name', 'shuffle')->first();
        if (is_null($shuffle)) {
            $newShuffle = new Option();
            $newShuffle->name = 'shuffle';
            $newShuffle->value = true;
            $newShuffle->save();
        } else {
            //Toggle the value from true to false
            $shuffle->value = ($shuffle->value = !$shuffle->value);
            $shuffle->save();
        }
    }

    /**
     * Add Request to the Database.
     *
     * @param Request $request
     * @return string
     */
    public function addRequest(Request $request) {

        $utils = new Utilities();
        $requestedURL = $request->get('requestedURL');

        if (!$utils->validURL($requestedURL)) {
            return [
                'type' => 'Error',
                'content' => 'The requested URL is not a URL please try again.'
            ];
        }

        /** @var URLRequest $existingRecord */
        $existingRecord = URLRequest::all()->where('status', 'Requested')->where('url', $requestedURL);
        if (count($existingRecord)) {
            return [
                'type' => 'Error',
                'content' => "$requestedURL is all ready in the queue."
            ];
        }
        try {
            $fileName = $this->downloadFile($requestedURL);
        } catch (\Exception $e) {
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

        /** @var URLRequest $record */
        $record = new URLRequest();
        $record->url = $url;
        $record->status = 'Requested';

        exec('cd /Stream; sudo scdl -l ' . $url);
        $getSongName = exec('cd /Stream && ls -t1 |  head -n 1');

        $record->fileName = $getSongName;
        $record->save();

        return $getSongName;

    }

    /**
     * Plays the file with the Filename it also updates the database with the status.
     * @param Request $request
     * @return string
     */
    public function playFile(Request $request) {

        $fileName = $request->get('fileName');

        $output = $this->playFromFileName($fileName);

        return "<pre>$output</pre>";

    }

    /**
     * Skip to the next file in the queue.
     *
     * @return \Exception|string
     */
    public function skipToNext() {

        try {
            /** @var URLRequest $playing */
            $playing = $this->getPlaying();
            /** @var URLRequest $next */
            $next = $playing->next();
            $this->playFromFileName($next->fileName);

            return 'true';

        } catch (\Exception $e) {
            return $e;
        }

    }

    /**
     * Skip to the previous file in the queue.
     *
     * @return \Exception|string
     */
    public function skipToPrevious() {

        try {
            /** @var URLRequest $playing */
            $playing = $this->getPlaying();
            /** @var URLRequest $next */
            $previous = $playing->previous();
            $this->playFromFileName($previous->fileName);

            return 'true';

        } catch (\Exception $e) {
            return $e;
        }

    }

    /**
     * This function clears the url_requests table of all the requests it also removes the files linked to the records.
     * @param Request $request
     * @return array
     */
    public function clearQueue(Request $request) {

        $fileName = $request->get('fileName');
        $this->stop($fileName);

        if (count(URLRequest::all()) > 0) {
            URLRequest::truncate();
            shell_exec('sudo rm -R /Stream && sudo mkdir /Stream');
            return [
                'type' => 'Success',
                'content' => 'Cleared the queue successfully.'
            ];
        } else {
            return [
                'type' => 'Warning',
                'content' => 'There is nothing in the queue to clear.'
            ];
        }

    }

    /**
     * Removes a file from the Server and the database.
     * @param Request $request
     * @return array
     */
    public function removeFile(Request $request) {

        $fileName = $request->get('fileName');
        $record = URLRequest::where('fileName', $fileName)->first();
        shell_exec('sudo rm /Stream/"' . $fileName . '"');
        $record->delete();

        return [
            'type' => 'Success',
            'content' => 'Successfully removed ' . $fileName
        ];

    }

    public function stopFile(Request $request) {

        $fileName = $request->get('fileName');

        $this->stop($fileName);

        return [
            'type' => 'Success',
            'content' => 'Stopped ' . $fileName . ' Successfully'
        ];

    }

    /**
     * Stops the playback from MPlayer it also updates the record in the Database.
     * @param $fileName
     */
    private function stop($fileName) {
        $this->unsetPaused();
        exec('sudo echo "quit" > /tmp/control');

        /** @var URLRequest $record */
        $record = URLRequest::where('fileName', $fileName)->first();
        $record->status = 'Played';
        $record->save();
    }

    /**
     * Pauses the file that is being used by mplayer it also updates the database with these changes.
     * @param Request $request
     * @return string
     */
    public function setPaused(Request $request) {
        $fileName = $request->get('fileName');
        exec('sudo echo "pause" > /tmp/control');
        /** @var URLRequest $record */
        $record = URLRequest::where('fileName', $fileName)->first();
        if ($record->status == 'Playing') {
            $record->status = 'Paused';
        } else {
            $record->status = 'Playing';
        }
        $record->save();

        return $record->status;
    }

    /**
     * Unsets the paused record in the database.
     */
    public function unsetPaused() {
        /** @var URLRequest $record */
        $record = URLRequest::where('status', 'Paused')->first();
        if (count($record) > 0) {
            $record->status = 'Played';
            $record->save();
        }
    }

    /**
     * Get paused record from the database.
     * @return mixed
     */
    public function getPaused() {
        return URLRequest::where('status', 'Paused')->first()->toArray();
    }

    /**
     * Return boolean of the state of the database.
     * @return mixed
     */
    public function isPaused() {
        $record = URLRequest::where('status', 'Paused')->first();
        if (!empty($record)) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * Gets the volume value from the PCM.
     * @return string
     */
    public function getVolume() {
        //Change this from PCM to Master or what ever amixer defines. PCM is the Pi's default this is why I am using it!
        $output = shell_exec("sudo amixer get PCM | awk '$0~/%/{print $4}' | tr -d '[]%';");
        return "$output";
    }

    /**
     * Sets the volume for the amixer output.
     * Returns the shells output for debugging.
     * @param Request $request
     * @return string
     */
    public function setVolume(Request $request) {

        $volume = $request->get('volume');
        //Change this from PCM to Master or what ever amixer defines. PCM is the Pi's default this is why I am using it!
        $output = shell_exec("sudo amixer --quiet set PCM $volume%");

        return "<pre>$output</pre>";

    }

    /**
     * Plays file from the filename!
     * @param $fileName
     * @return string
     */
    private function playFromFileName($fileName) {

        /** @var URLRequest $alreadyPlaying */
        $alreadyPlaying = URLRequest::where('status', 'Playing')->first();
        if ($alreadyPlaying) {
            //Allow user to play any file in the queue at any time!
            $this->stop($alreadyPlaying->fileName);
        }

        /** @var URLRequest $record */
        $record = URLRequest::where('fileName', $fileName)->first();
        if ($record) {
            $record->status = 'Playing';
            $record->save();
        }

        //Creates the fifo file for mplayer to read!
        if (!file_exists('/tmp/control')) {
            exec('sudo mkfifo /tmp/control');
            exec('sudo chmod 777 /tmp/control');
        }

        $output = shell_exec('sudo mplayer -input file=/tmp/control /Stream/"' . $fileName .'"');

        if ($record) {
            $record->status = 'Played';
            $record->save();
        }

        /** @var Option $shuffle */
        $shuffle = Option::where('name', 'shuffle')->first();
        if ($shuffle->value) {
            $nextRecord = URLRequest::all()->random();
            $this->playFromFileName($nextRecord->fileName);
        }
        return $output;
    }
}
