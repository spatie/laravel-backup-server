<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Illuminate\Support\Facades\Mail;
use Spatie\BackupServer\Tests\TestCase;

class MonitorBackupsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /** @test */
    public function it_will_send_a_mail_when()
    {
        $this->artisan('backup-server:monitor')->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
