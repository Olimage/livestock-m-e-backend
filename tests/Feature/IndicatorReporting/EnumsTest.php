<?php

namespace Tests\Feature\IndicatorReporting;

use App\Enums\ApprovalAction;
use App\Enums\ReportStatus;
use App\Enums\StageApprovalMode;
use Tests\TestCase;

class EnumsTest extends TestCase
{
    public function test_enum_values_are_stable(): void
    {
        $this->assertSame('draft', ReportStatus::Draft->value);
        $this->assertSame('approved', ReportStatus::Approved->value);
        $this->assertSame('declined', ApprovalAction::Declined->value);
        $this->assertSame('all', StageApprovalMode::All->value);
    }
}
