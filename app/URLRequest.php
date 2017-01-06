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

    public  function next(){
        //Get next record
        $id = ($this->id += 1);
        return URLRequest::where('id', '=', $id)->first();
    }

    public function previous(){
        //Get previous record
        $id = ($this->id -= 1);
        return URLRequest::where('id', '=', $id)->first();
    }
}
