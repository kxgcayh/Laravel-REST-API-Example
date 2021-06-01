<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
        'image',
        'photo_uri'
    ];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }
}
