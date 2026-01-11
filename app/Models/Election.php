<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Election extends Model
{
    //

    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'voting_start',
        'voting_end',
        'status'
    ];
}
