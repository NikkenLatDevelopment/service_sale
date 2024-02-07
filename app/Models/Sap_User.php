<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sap_User extends Model
{
    use HasFactory;
    protected $connection = '173';
    protected $table = 'validacionCorreo_vistaLictradNumvalidate';
}
