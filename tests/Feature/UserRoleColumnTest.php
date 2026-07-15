<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserRoleColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_string_column_is_removed(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'role'));
    }
}
