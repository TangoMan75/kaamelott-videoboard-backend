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

use App\Command\UpdateCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Serializer\SerializerInterface;

class UpdateCommandTest extends KernelTestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var ObjectRepository
     */
    private $repository;

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

    public function testUpdateFileShouldDisplayExpectedSummaryTable(): void
    {
        // `decode` should be declared in `mock->setMethods()`
        $this->serializer->method('decode')->willReturn([]);

        // Entity should exist in project
        $this->commandTester->execute(
            [
                '-d' => __DIR__.'/../Fixtures/',
                '-f' => 'people.csv',
                '-o' => null,
            ]
        );

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('className         App\Entity\Person', $output);
        $this->assertStringContainsString('classShortName    Person', $output);
        $this->assertStringContainsString('encoder           person:csv', $output);
        $this->assertStringContainsString('extension         csv', $output);
        $this->assertStringContainsString('fileName          people.csv', $output);
        $this->assertStringContainsString('overwrite         1', $output);
        $this->assertStringContainsString('sourceDirectory   '.__DIR__.'/../Fixtures/', $output);
        $this->assertStringContainsString('tableName         person', $output);
    }

    public function testUpdateClassShouldDisplayExpectedSummaryTable(): void
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

    protected function setup(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        // create mocks
        /* @var $em EntityManagerInterface */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->disableOriginalConstructor()->getMock();

        // Mock should implement required methods from interface
        $this->repository = $this->getMockBuilder(ObjectRepository::class)
            ->setMethods(
                [
                    'find',
                    'findAll',
                    'findBy',
                    'findOneBy',
                    'getClassName',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $em->method('getRepository')->willReturn($this->repository);

        // Mock should implement required methods from interface
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->setMethods(
                [
                    'decode',
                    'serialize',
                    'deserialize',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        // init command
        $application->add(new UpdateCommand($em, $this->serializer));

        $command = $application->find('app:update');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->commandTester = null;
        $this->serializer = null;
    }
}