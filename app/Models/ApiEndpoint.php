<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiEndpoint extends Model
{
    protected $table = 'api_endpoints';

    protected $fillable = [
        'name','code','description',
        'base_url','path','method',
        'headers','params','body_template',
        'auth_type','auth_key','auth_value',
        'is_active'
    ];

    protected $casts = [
        'headers' => 'array',
        'params' => 'array',
        'body_template' => 'array',
        'is_active' => 'boolean'
    ];
}
