<?php

namespace App\Enums;

enum TravelAccommodationTipo: string
{
    case Hotel   = 'hotel';
    case Hostel  = 'hostel';
    case Airbnb  = 'airbnb';
    case Casa    = 'casa';
    case Camping = 'camping';
    case Otro    = 'otro';

    public function label(): string
    {
        return match($this) {
            self::Hotel   => 'Hotel',
            self::Hostel  => 'Hostel',
            self::Airbnb  => 'Airbnb',
            self::Casa    => 'Casa particular',
            self::Camping => 'Camping',
            self::Otro    => 'Otro',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Hotel   => '🏨',
            self::Hostel  => '🛏️',
            self::Airbnb  => '🏠',
            self::Casa    => '🏡',
            self::Camping => '⛺',
            self::Otro    => '🏢',
        };
    }
}
