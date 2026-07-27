<?php

namespace Tests\Feature\IndicatorReporting;

use App\Models\Department;
use App\Models\IndicatorReport;
use App\Models\OutputIndicator;
use App\Models\Permission;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\AuthenticatesWithJwt;
use Tests\TestCase;

/**
 * Evidence (proof) files are ministry records attached to indicator reports.
 * They must live on a private disk, be type-checked on the way in, and only be
 * retrievable through an authorizing endpoint — never via a guessable public URL.
 */
class EvidenceStorageTest extends TestCase
{
    use AuthenticatesWithJwt, RefreshDatabase;

    private Department $dept;

    private User $director;

    private IndicatorReport $report;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->dept = Department::create(['name' => 'Livestock', 'slug' => 'livestock']);
        $indicator = OutputIndicator::create(['title' => 'Vax', 'department_id' => $this->dept->id]);
        $period = ReportingPeriod::create(['name' => 'Q1 2026', 'type' => 'quarter', 'year' => 2026, 'period_number' => 1]);

        $perm = Permission::create(['permission' => 'report-indicator-data', 'label' => 'Report Indicator Data']);
        $role = Role::create(['name' => 'Director', 'slug' => 'director']);
        $role->permissions()->attach($perm->id);
        $this->director = User::create(['full_name' => 'Dir', 'email' => 'dir@x.io', 'password' => 'secret123']);
        $this->director->roles()->attach($role->id);
        $this->director->departments()->attach($this->dept->id);

        $this->report = IndicatorReport::create([
            'indicator_type' => OutputIndicator::class,
            'indicator_id' => $indicator->id,
            'department_id' => $this->dept->id,
            'reporting_period_id' => $period->id,
            'created_by' => $this->director->id,
        ]);
    }

    private function upload(UploadedFile $file)
    {
        return $this->withHeaders($this->authHeaders($this->director))
            ->postJson("/api/v1/indicator-reports/{$this->report->uuid}/proofs", ['file' => $file]);
    }

    public function test_evidence_is_written_to_the_private_disk(): void
    {
        $this->upload(UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf'))->assertCreated();

        $proof = $this->report->proofs()->firstOrFail();
        Storage::disk('local')->assertExists($proof->path);
    }

    public function test_executable_and_unexpected_file_types_are_rejected(): void
    {
        foreach (['payload.php', 'script.js', 'run.exe', 'shell.sh'] as $name) {
            $this->upload(UploadedFile::fake()->create($name, 10))
                ->assertJsonValidationErrors('file');
        }

        $this->assertSame(0, $this->report->proofs()->count());
    }

    public function test_oversized_files_are_rejected(): void
    {
        $this->upload(UploadedFile::fake()->create('huge.pdf', 20480, 'application/pdf'))
            ->assertJsonValidationErrors('file');
    }

    public function test_api_never_exposes_the_raw_storage_path(): void
    {
        $this->upload(UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf'))->assertCreated();
        $proof = $this->report->proofs()->firstOrFail();

        // Compare against the decoded payload — raw JSON escapes forward slashes,
        // so a naive string search on the body would pass even when `path` leaks.
        $payload = $this->withHeaders($this->authHeaders($this->director))
            ->getJson("/api/v1/indicator-reports/{$this->report->uuid}")
            ->assertOk()
            ->json('data.proofs.0');

        $this->assertNotEmpty($proof->path);
        $this->assertArrayNotHasKey('path', $payload);
        $this->assertNotContains($proof->path, $payload);
    }

    public function test_owner_can_download_their_evidence(): void
    {
        $this->upload(UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf'))->assertCreated();
        $proof = $this->report->proofs()->firstOrFail();

        $this->withHeaders($this->authHeaders($this->director))
            ->get("/api/v1/indicator-reports/{$this->report->uuid}/proofs/{$proof->id}")
            ->assertOk()
            ->assertDownload('proof.pdf');
    }

    public function test_unauthenticated_and_unrelated_users_cannot_download_evidence(): void
    {
        $this->upload(UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf'))->assertCreated();
        $proof = $this->report->proofs()->firstOrFail();
        $url = "/api/v1/indicator-reports/{$this->report->uuid}/proofs/{$proof->id}";

        // withHeaders() persists on the test case, so the upload above would
        // otherwise leave the director's bearer token on this request.
        $this->flushHeaders();
        $this->getJson($url)->assertUnauthorized();

        $stranger = User::create(['full_name' => 'Nosy', 'email' => 'nosy@x.io', 'password' => 'secret123']);
        $this->withHeaders($this->authHeaders($stranger))->getJson($url)->assertForbidden();
    }

    public function test_evidence_cannot_be_read_across_reports(): void
    {
        $this->upload(UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf'))->assertCreated();
        $proof = $this->report->proofs()->firstOrFail();

        $other = IndicatorReport::create([
            'indicator_type' => OutputIndicator::class,
            'indicator_id' => $this->report->indicator_id,
            'department_id' => $this->dept->id,
            'reporting_period_id' => $this->report->reporting_period_id,
            'created_by' => $this->director->id,
        ]);

        $this->withHeaders($this->authHeaders($this->director))
            ->getJson("/api/v1/indicator-reports/{$other->uuid}/proofs/{$proof->id}")
            ->assertNotFound();
    }
}
