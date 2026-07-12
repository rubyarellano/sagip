<?php

namespace App\Enums;

enum IncidentCategory: string
{
    case Fire = 'Fire';
    case Flood = 'Flood';
    case Medical = 'Medical';
    case Landslide = 'Landslide';
    case Earthquake = 'Earthquake';
    case Other = 'Other';
}
