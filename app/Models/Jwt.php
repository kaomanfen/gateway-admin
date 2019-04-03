<?php
/**
 * Created by PhpStorm.
 * User: Jin Hongyang
 * Date: 2019/3/20
 * Time: 11:42
 */

namespace App\Models;


class Jwt extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'project_name',
        'secret_key',
        'secret_alog',
    ];


}