<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Returned = 'returned';
    case Approved = 'approved';
}
