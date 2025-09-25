<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;
    public $table = "pegawai";
    protected $fillable = [
        'id',
        'fullname',
        'no_hp',
        'no_rek',
        'no_rek_bni',
        'no_rek_bri',
    ];
}
