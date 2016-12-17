<?php

namespace App\Http\Controllers;

use App\System\Utilities;
use App\URLRequest;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class RequestController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
            $this->downloadFile($requestedURL);
        } catch (\Exception $e) {
            return [
                'type' => 'Error',
                'content' => $e
            ];
        }

        return [
            'type' => 'Success',
            'content' => "Successfully added $requestedURL to the queue."
        ];
    }

    /**
     * Download the file from the URL and then add the fileName to the database.
     * @param $url string
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
                'type' => 'Error',
                'content' => $alreadyPlaying->fileName . " is playing please wait until the file is finished."
            ];
        }

        $fileName = $request->get('fileName');
        /** @var URLRequest $record */
        $record = URLRequest::where('fileName', $fileName)->first();
        $record->status = 'Playing';
        $record->save();
        $output = exec("sudo mplayer /Stream/\"$fileName\"");
        $record->status = 'Played';
        $record->save();

        return "<pre>$output</pre>";

    }

    /**
     * Remove Request from the Database.
     *
     * @param Request $request
     * @return string
     */
    public function removeRequest(Request $request) {
        $utils = new Utilities();
        $requestedURL = $request->get('requestedURL');

        if (!$utils->validURL($requestedURL)) {
            return 'The requested URL is not a URL please try again.';
        }

        try {
            /** @var URLRequest $record */
            $record = URLRequest::all()
                ->where('status', 'Requested')
                ->where('url', $requestedURL);
            $record->delete();

        } catch (\Exception $e) {
            return $e;
        }

        return "Successfully removed $requestedURL from the queue.";
    }

    /**
     * Update Request from the Database.
     *
     * @param Request $request
     * @return string
     */
    public function updateRequest(Request $request) {
        return 'API Hit';
    }
}
