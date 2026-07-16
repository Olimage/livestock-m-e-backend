<?php

namespace App\Enums;

enum StageApprovalMode: string
{
    case Any = 'any';
    case All = 'all';
}
