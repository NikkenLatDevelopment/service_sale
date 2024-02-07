<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class warranties_in_process extends Model
{
    use HasFactory;
    protected $connection = 'mysqlTV';
    protected $table = 'warranties_in_process';
    protected $guarded = []; 
}
