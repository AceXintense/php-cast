<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string url
 * @property string status
 * @property string fileName
 */
class URLRequest extends Model {

    protected $table = 'url_requests';

    public function previous(){
        //Get previous record
        return URLRequest::where('id', '<', $this->id)->orderBy('id','asc')->first();
    }
    public  function next(){
        //Get next record
        return URLRequest::where('id', '>', $this->id)->orderBy('id','desc')->first();
    }
}
