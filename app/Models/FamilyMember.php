<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'dob',
        'family_register_number',
        'amka',
        'vat_number',
        'pa_number',
        'id_number',
        'passport_number',
        'id_image_front',
        'id_image_back',
        'passport_image',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    protected $appends = [
        'zodiac_sign',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(FamilyDocument::class);
    }

    public function getZodiacSignAttribute(): ?string
    {
        if (!$this->dob) {
            return null;
        }

        $date = Carbon::parse($this->dob);
        $month = $date->month;
        $day = $date->day;

        switch ($month) {
            case 1:
                return $day <= 19 ? 'Capricorn' : 'Aquarius';
            case 2:
                return $day <= 18 ? 'Aquarius' : 'Pisces';
            case 3:
                return $day <= 20 ? 'Pisces' : 'Aries';
            case 4:
                return $day <= 19 ? 'Aries' : 'Taurus';
            case 5:
                return $day <= 20 ? 'Taurus' : 'Gemini';
            case 6:
                return $day <= 20 ? 'Gemini' : 'Cancer';
            case 7:
                return $day <= 22 ? 'Cancer' : 'Leo';
            case 8:
                return $day <= 22 ? 'Leo' : 'Virgo';
            case 9:
                return $day <= 22 ? 'Virgo' : 'Libra';
            case 10:
                return $day <= 22 ? 'Libra' : 'Scorpio';
            case 11:
                return $day <= 21 ? 'Scorpio' : 'Sagittarius';
            case 12:
                return $day <= 21 ? 'Sagittarius' : 'Capricorn';
            default:
                return null;
        }
    }
}
