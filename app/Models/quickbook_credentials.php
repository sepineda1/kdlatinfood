<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class quickbook_credentials extends Model
{
    use HasFactory;
    protected $table = 'quickbooks_credentials';

    protected $fillable = [
        'client_id',
        'client_secret',
        'redirect_uri',
        'access_token',
        'refresh_token',
        'realm_id',
        'base_url',
        'api_url',
        'others',
        'status',
        'updating_time',
        'created_at',
        'updated_at',
    ];
}
