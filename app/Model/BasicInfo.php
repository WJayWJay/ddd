<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class BasicInfo extends Model
{
//    use Notifiable;

    protected $table = 'basicinfo';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
//    protected $hidden = [
//        'password', 'remember_token',
//    ];

    public function cats() {
        return $this->hasMany('App\Model\BasicInfoAttach', 'bid');
    }

}
