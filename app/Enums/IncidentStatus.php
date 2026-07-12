<?php

namespace App\Enums;

enum IncidentStatus: string
{
    case Dispatched = 'Dispatched';
    case Processing = 'Processing';
    case Resolved = 'Resolved';
    case Dismissed = 'Dismissed';
    case InProgress = 'In Progress';
}
