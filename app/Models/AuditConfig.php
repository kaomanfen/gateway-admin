<?php

namespace App\Models;

class AuditConfig extends BaseModel
{

    protected $fillable = ['project_id', 'env', 'name', 'value', 'versions', 'remark'];

}
