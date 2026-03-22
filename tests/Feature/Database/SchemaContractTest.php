<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_permissions_and_audit_logs_match_planned_schema(): void
    {
        $this->assertTrue(Schema::hasColumns('permissions', ['id', 'key', 'created_at', 'updated_at']));
        $this->assertFalse(Schema::hasColumn('permissions', 'name'));

        $this->assertTrue(Schema::hasColumns('audit_logs', [
            'id',
            'user_id',
            'entity_type',
            'entity_id',
            'action',
            'payload',
            'created_at',
        ]));
        $this->assertFalse(Schema::hasColumn('audit_logs', 'event'));
        $this->assertFalse(Schema::hasColumn('audit_logs', 'old_values'));
        $this->assertFalse(Schema::hasColumn('audit_logs', 'new_values'));
    }

    public function test_role_pivots_use_primary_keys_ordered_for_expected_access_patterns(): void
    {
        $this->assertSame(
            ['role_id', 'permission_id'],
            $this->primaryKeyColumnsFor('permission_role'),
        );

        $this->assertSame(
            ['user_id', 'role_id'],
            $this->primaryKeyColumnsFor('role_user'),
        );
    }

    /**
     * @return list<string>
     */
    private function primaryKeyColumnsFor(string $table): array
    {
        return collect(DB::select("PRAGMA table_info('{$table}')"))
            ->filter(fn (object $column): bool => $column->pk > 0)
            ->sortBy('pk')
            ->pluck('name')
            ->values()
            ->all();
    }
}
