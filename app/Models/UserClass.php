<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserClass extends Model
{

    protected $table = 'classes';
    
    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    
    public function users(){
        return $this->hasMany(User::class, 'id_class');
    }
    
    public function getNameAttribute($value) {
        return ucfirst($value);
    }
    
    public function setNameAttribute($value) {
        $this->attributes['name'] = trim($value);
    }
    
    public function userCount(): int {
        return $this->users()->count();
    }

    public function voters(){
        return $this->users()->where('can_vote', true);
    }
    
}
