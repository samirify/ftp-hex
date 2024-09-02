<?php
declare(strict_types=1);

namespace App\Shared\Tests\Unit\Domain\Message\Command;

use App\Shared\Domain\Message\Command\BaseCommand;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\Shared\Domain\Exception\InvalidCommandException;
use ReflectionClass;
use ReflectionException;

class BaseCommandTest extends TestCase
{
    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function test_constructor_throws_InvalidCommandException_when_isValid_returns_false()
    {
        $this->expectException(InvalidCommandException::class);

        $baseCommandMock = $this->createMock(BaseCommand::class, []);

        $baseCommandMock
            ->method('isValid')
            ->willReturn(false);

        $reflectedClass = new ReflectionClass(BaseCommand::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($baseCommandMock, []);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function test_constructor_passes_when_isValid_returns_true()
    {
        $baseCommandMock = $this->createMock(BaseCommand::class, []);

        $baseCommandMock
            ->method('isValid')
            ->willReturn(true);

        $reflectedClass = new ReflectionClass(BaseCommand::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($baseCommandMock, []);

        $this->assertTrue(true);
    }
}