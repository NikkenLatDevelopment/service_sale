<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BPLatam extends Model
{
    use HasFactory;
    protected $connection = 'REG_STG';
    protected $table = 'BPLatam';
    protected $guarded = []; 
}
