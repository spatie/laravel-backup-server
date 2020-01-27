<?php

namespace Spatie\BackupServer\Tests\Feature;

use Spatie\BackupServer\Support\Ssh;
use Spatie\BackupServer\Tests\TestCase;

class SshTest extends TestCase
{
    /** @test */
    public function it_can_run_an_ssh_command()
    {
        $ssh = new Ssh('forge', 'spatie.be');

        $process = $ssh->execute(['whoami']);

        $this->assertTrue($process->isSuccessful());
        $this->assertStringContainsString('forge', $process->getOutput());
    }

    /** @test */
    public function it_will_fail_if_the_ssh_command_fails()
    {
        $ssh = new Ssh('forge', 'spatie.be');

        $process = $ssh->execute(['non-existing-command']);

        $this->assertFalse($process->isSuccessful());
    }
}
