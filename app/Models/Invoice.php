<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //
    protected $fillable = ['uuid', 'folio', 'emisor', 'receptor', 'moneda', 'total', 'tipo_cambio'];
}
