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
     * Add Request to the Database.
     *
     * @param Request $request
     * @return string
     */
    public function addRequest(Request $request) {
        $utils = new Utilities();
        $requestedURL = $request->get('requestedURL');

        if (!$utils->validURL($requestedURL)) {
            return 'The requested URL is not a URL please try again.';
        }

        /** @var URLRequest $existingRecord */
        $existingRecord = URLRequest::all()->where('status', 'Requested')->where('url', $requestedURL);
        if (count($existingRecord)) {
            return "$requestedURL is all ready in the queue.";
        }
        try {
            /** @var URLRequest $record */
            $record = new URLRequest();
            $record->url = $requestedURL;
            $record->status = 'Requested';
            $record->save();
        } catch (\Exception $e) {
            return $e;
        }

        return "Successfully added $requestedURL to the queue.";

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

    /**
     * Get the URL that is currently playing.
     *
     * @param Request $request
     * @return string
     */
    public function getPlaying(Request $request) {
        return 'API Hit';
    }
}
