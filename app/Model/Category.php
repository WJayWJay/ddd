<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Category extends Model
{
//    use Notifiable;

    protected $table = 'category';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type','uid', 'projectName', 'proAliasName', 'submit', 'basic', 'filter', 'cardList', 'options'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
//    protected $hidden = [
//        'password', 'remember_token',
//    ];

    public function getSubmit() {
        $query = ['submit' => 1];
        return $this->where($query)->orderBy('sortId', 'desc')->get()->toArray();
    }

    public function getFilter() {
        $query = ['filter' => 1];
        return $this->where($query)->orderBy('sortId', 'desc')->get()->toArray();
    }

    public function getCardList() {
        $query = ['cardList' => 1];
        return $this->where($query)->orderBy('sortId', 'desc')->get()->toArray();
    }
    public function getBasicList() {
        $query = ['basic' => 1];
        return $this->where($query)->orderBy('sortId', 'desc')->get()->toArray();
    }
}
