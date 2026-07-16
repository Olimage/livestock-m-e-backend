<?php

namespace App\Enums;

enum ResubmitBehavior: string
{
    case FromStart = 'from_start';
    case FromDeclinedStage = 'from_declined_stage';
}
