<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserClass extends Model
{
    //

    protected $fillable = [
        'name'
    ];

    
    public function user(){
        return $this->hasMany(User::class, 'id_class');
    }

    
}
