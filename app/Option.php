<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string name
 * @property string value
 */

class Option extends Model
{
    protected $table = 'options';
}
