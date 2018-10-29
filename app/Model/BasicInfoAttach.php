<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class BasicInfoAttach extends Model
{
//    use Notifiable;

    protected $table = 'basicinfoattach';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property','value', 'type', 'bid', 'uid', 'categoryId'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
//    protected $hidden = [
//        'password', 'remember_token',
//    ];


    public function basicInfo() {
        return $this->belongsTo('App\BasicInfo', 'bid', 'id');
    }
}
