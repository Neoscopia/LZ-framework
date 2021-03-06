<?php

namespace Tests;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;
use NunoMaduro\LaravelDesktopNotifier\Contracts\Notifier;

class BaseCommandTest extends TestCase
{
    /** @test */
    public function it_has_a_container_getter(): void
    {
        $command = $this->makeCommand();

        $this->assertSame($command->getContainer(), app());
    }

    /** @test */
    public function it_allows_notifications(): void
    {
        $command = $this->makeCommand();

        $notifierMock = $this->createMock(Notifier::class);

        $notifierMock->expects($this->once())->method('send')->with(
            $this->callback(
                function ($notification) {
                    return $notification->getTitle() === 'foo' && $notification->getBody() === 'bar';
                }
            )
        );

        app()->instance(Notifier::class, $notifierMock);

        $command->notify('foo', 'bar');
    }

    /** @test */
    public function it_allows_success_tasks(): void
    {
        $command = $this->makeCommand();

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock->expects($this->once())->method('writeln')->with(
            'foo: <info>✔</info>'
        );

        $command->setOutput($outputMock);

        $command->task(
            'foo', function () {
                return true;
            });
    }

    /** @test */
    public function it_allows_fail_tasks(): void
    {
        $command = $this->makeCommand();

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock->expects($this->once())->method('writeln')->with(
            'bar: <error>failed</error>'
        );

        $command->setOutput($outputMock);

        $command->task(
            'bar', function () {
                return false;
            });
    }

    private function makeCommand()
    {
        $command = new class extends Command {
            protected $name = 'foo:bar';

            public function handle(): void
            {
            }

            public function setOutput($output)
            {
                $this->output = $output;
            }
        };

        $this->app->add($command);

        return $command;
    }
}
