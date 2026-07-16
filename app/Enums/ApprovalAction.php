<?php

namespace App\Enums;

enum ApprovalAction: string
{
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Declined = 'declined';
    case Returned = 'returned';
    case Resubmitted = 'resubmitted';
    case Published = 'published';
}
