<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "name",
        "photo",
        "about",
        "yoe",  // year of experience
        "specialist_id",
        "hospital_id",
        "gender",
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function specialist()
    {
        return $this->belongsTo(Specialist::class);
    }

    public function bookingTransaction()
    {
        return $this->hasMany(BookingTransaction::class);
    }

    public function getPhotoAttribute($value)
    {
        if (!$value) {
            return null; // no image available
        }
        return url(Storage::url($value)); // mengembalikan URL gambar jika ada
    }
}
