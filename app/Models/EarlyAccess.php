<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

// use Illuminate\Database\Eloquent\Model;

class EarlyAccess extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
