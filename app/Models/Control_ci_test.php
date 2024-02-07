<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Control_ci_test extends Model
{
    use HasFactory;
    protected $connection = 'incorporacion';
    protected $table = 'nikkenla_marketing.control_ci_test';
    protected $guarded = []; 
    public $timestamps = false;
    
}
