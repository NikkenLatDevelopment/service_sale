<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sales_tv extends Model
{
    use HasFactory;
    protected $connection = 'mysqlTV';
    protected $table = 'sales';
    protected $guarded = []; 
}
