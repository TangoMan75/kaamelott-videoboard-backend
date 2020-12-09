<?php

/**
 * This file is part of the TangoMan package.
 *
 * Copyright (c) 2020 "Matthias Morin" <mat@tangoman.io>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Command;

use App\Command\ImportCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Serializer\SerializerInterface;

class ImportCommandTest extends KernelTestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function testEmptySourceDirectoryShouldRaiseException(): void
    {
        $this->expectExceptionMessage('Source directory cannot be empty');
        $this->commandTester->execute(
            [
                '-d' => '',
            ]
        );
    }

    public function testEmptySourceFileShouldRaiseException(): void
    {
        $this->expectExceptionMessage('Source file cannot be empty');
        $this->commandTester->execute(
            [
                '-f' => '',
            ]
        );
    }

    public function testInvalidClassShouldRaiseException(): void
    {
        $this->expectExceptionMessage('Entity not found');
        // File should exist in `Fixture` folder
        $this->commandTester->execute(
            [
                '-d' => __DIR__.'/../Fixtures/',
                '-f' => 'foobar.csv',
            ]
        );
    }

    public function testImportFileShouldDisplayExpectedSummaryTable(): void
    {
        // `decode` should be declared in `mock->setMethods()`
        $this->serializer->method('decode')->willReturn([]);

        // Entity should exist in project
        $this->commandTester->execute(
            [
                '-d' => __DIR__.'/../Fixtures/',
                '-f' => 'people.csv',
            ]
        );

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('className         App\Entity\Person', $output);
        $this->assertStringContainsString('classShortName    Person', $output);
        $this->assertStringContainsString('encoder           person:csv', $output);
        $this->assertStringContainsString('extension         csv', $output);
        $this->assertStringContainsString('fileName          people.csv', $output);
        $this->assertStringContainsString('sourceDirectory   '.__DIR__.'/../Fixtures/', $output);
        $this->assertStringContainsString('tableName         person', $output);
    }

    public function testImportClassShouldDisplayExpectedSummaryTable(): void
    {
        // `decode` should be declared in `mock->setMethods()`
        $this->serializer->method('decode')->willReturn([]);

        // Entity should exist in project
        $this->commandTester->execute(
            [
                '-d' => __DIR__.'/../Fixtures/',
                '-f' => 'people.csv',
                '-c' => 'App\Entity\Person',
                '-x' => 'foobar:json',
            ]
        );

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('className         App\Entity\Person', $output);
        $this->assertStringContainsString('classShortName    Person', $output);
        $this->assertStringContainsString('encoder           foobar:json', $output);
        $this->assertStringContainsString('extension         csv', $output);
        $this->assertStringContainsString('fileName          people.csv', $output);
        $this->assertStringContainsString('sourceDirectory   '.__DIR__.'/../Fixtures/', $output);
        $this->assertStringContainsString('tableName         person', $output);
    }

    public function testZeroImportShouldDisplayOK(): void
    {
        $this->serializer->method('decode')->willReturn([]);

        $this->commandTester->execute(
            [
                '-d' => __DIR__.'/../Fixtures/',
                '-f' => 'people.csv',
            ]
        );

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('[OK] 0 rows imported.', $output);
    }

    public function testOneImportShouldDisplayOK(): void
    {
        $this->serializer->method('decode')->willReturn(['foobar']);
        // `mock->denormalize` returns anonymous class
        $this->serializer->method('denormalize')->willReturn(
            new class {
            }
        );

        $this->commandTester->execute(
            [
                '-d' => __DIR__.'/../Fixtures/',
                '-f' => 'people.csv',
            ]
        );

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('[OK] 1 row imported.', $output);
    }

    protected function setup(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        // create mocks
        /* @var $em EntityManagerInterface */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->disableOriginalConstructor()->getMock();

        // Mock should implement required methods from interface
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->setMethods(
                [
                    'decode',
                    'denormalize',
                    'serialize',
                    'deserialize',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        // init command
        $application->add(new ImportCommand($em, $this->serializer));

        $command = $application->find('app:import');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->commandTester = null;
        $this->serializer = null;
    }
}