<?php

namespace App\Models;

class Waf extends BaseModel
{

    protected $fillable = [
        'name',
        'title',
        'rules',
        'type',
        'status',
    ];

    protected $casts = [
        'rules' => 'array',
    ];

}
