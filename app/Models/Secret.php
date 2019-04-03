<?php

namespace App\Models;

class Secret extends BaseModel
{
    protected $fillable = [

        'name',

        'app_key',
        'app_secret',
        'status',
    ];

}
