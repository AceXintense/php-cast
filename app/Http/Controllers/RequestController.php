<?php

namespace App\Http\Controllers;

use App\System\Utilities;
use App\URLRequest;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RequestController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    private $shuffle = false;

    /**
     * Get the playing song.
     * @return array
     */
    public function getPlaying() {
        return URLRequest::all()->where('status', 'Playing')->toArray();
    }

    /**
     *  Get all the Requested URLs in the database.
     *  @return array
     */
    public function getRequestedURLs() {
        return URLRequest::all()->toArray();
    }

    /**
     * Sets shuffle mode.
     * @param Request $request
     * @return array
     */
    public function setShuffle(Request $request) {
        $this->shuffle = $request->get('toggle');
        return [
            'state' => $this->shuffle,
            'type' => 'Success',
            'content' => 'Shuffle ' . $this->shuffle
        ];
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

        /** @var URLRequest $alreadyPlaying */
        $alreadyPlaying = URLRequest::where('status', 'Playing')->first();
        if ($alreadyPlaying) {
            return [
                'type' => 'Warning',
                'content' => $alreadyPlaying->fileName . " is playing please wait until the file is finished."
            ];
        }

        $fileName = $request->get('fileName');

        $output = $this->playFromFileName($fileName);

        return "<pre>$output</pre>";

    }

    /**
     * This function clears the url_requests table of all the requests it also removes the files linked to the records.
     */
    public function clearQueue() {

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

    /**
     * Sets the volume for the amixer output.
     * Returns the shells output for debugging.
     * @param Request $request
     * @return string
     */
    public function changeVolume(Request $request) {

        $volume = $request->get('volume');
        $output = shell_exec("sudo amixer set PCM -- -$volume");

        return "<pre>$output</pre>";

    }

    private function playFromFileName($fileName) {

        /** @var URLRequest $record */
        $record = URLRequest::where('fileName', $fileName)->first();
        if ($record) {
            $record->status = 'Playing';
            $record->save();
        }

        $output = shell_exec('sudo mplayer /Stream/"' . $fileName .'"');

        if ($record) {
            $record->status = 'Played';
            $record->save();
        }

        if ($this->shuffle) {
            $nextRecord = URLRequest::all()->random();
            $this->playFromFileName($nextRecord->fileName);
        }
        return $output;
    }
}
