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

}
