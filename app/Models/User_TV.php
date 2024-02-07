<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_TV extends Model
{
    use HasFactory;
    protected $connection = 'mysqlTV';
    protected $table = 'users';
    protected $guarded = []; 
    //public $timestamps = false;
    protected $primaryKey = "sap_code";
}
