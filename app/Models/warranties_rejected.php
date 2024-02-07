<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class warranties_rejected extends Model
{
    use HasFactory;
    protected $connection = 'mysqlTV';
    protected $table = 'warranties_rejected';
    protected $guarded = []; 
}
