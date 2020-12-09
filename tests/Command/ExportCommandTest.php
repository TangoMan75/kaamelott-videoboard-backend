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

use App\Command\ExportCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

class ExportCommandTest extends KernelTestCase
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

    public function testInvalidDestinationDirectoryShouldRaiseException(): void
    {
        $this->expectExceptionMessage('Destination directory cannot be empty');
        $this->commandTester->execute(
            [
                '-d' => '',
            ]
        );
    }

    public function testInvalidClassShouldRaiseException(): void
    {
        $this->expectExceptionMessage('Entity not found');
        $this->commandTester->execute(
            [
                '-c' => 'App\Entity\FooBar',
            ]
        );
    }

    public function testEmptyClassNameAndEncoderShouldRaiseException(): void
    {
        $this->expectExceptionMessage('Class name and encoder cannot both be empty');
        $this->commandTester->execute(
            [
                '-d' => 'foobar',
            ]
        );
    }

    public function testExportShouldDisplayExpectedSummaryTable(): void
    {
        $this->repository->method('findAll')->willReturn([]);

        // Entity should exist in project
        $this->commandTester->execute(
            [
                '-c' => 'App\Entity\Person',
                '-d' => __DIR__.'/../../assets/exports/'
            ]
        );

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('className              App\Entity\Person', $output);
        $this->assertStringContainsString('classShortName         Person', $output);
        $this->assertStringContainsString('destinationDirectory   '.__DIR__.'/../../assets/exports/', $output);
        $this->assertStringContainsString('encoder                json', $output);
        $this->assertStringContainsString('extension              json', $output);
        $this->assertStringContainsString('fileName               people.json', $output);
        $this->assertStringContainsString('filePath               '.__DIR__.'/../../assets/exports/people.json', $output);
        $this->assertStringContainsString('group                  export', $output);
        $this->assertStringContainsString('tableName              person', $output);
    }

    public function testExportWithGivenParametersShouldDisplayExpectedSummaryTable(): void
    {
        $this->repository->method('findAll')->willReturn([]);

        // Entity should exist in project
        $this->commandTester->execute(
            [
                '-c' => 'App\Entity\Person',
                '-d' => __DIR__.'/../../assets/exports/',
                '-g' => 'read:person',
                '-x' => 'person:csv',
            ]
        );

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('className              App\Entity\Person', $output);
        $this->assertStringContainsString('classShortName         Person', $output);
        $this->assertStringContainsString('destinationDirectory   '.__DIR__.'/../../assets/exports/', $output);
        $this->assertStringContainsString('encoder                person:csv', $output);
        $this->assertStringContainsString('extension              csv', $output);
        $this->assertStringContainsString('fileName               people.csv', $output);
        $this->assertStringContainsString('filePath               '.__DIR__.'/../../assets/exports/people.csv', $output);
        $this->assertStringContainsString('group                  read:person', $output);
        $this->assertStringContainsString('tableName              person', $output);
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

        /* @var $fileSystem Filesystem */
        $fileSystem = $this->getMockBuilder(FileSystem::class)->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->setMethods(
                [
                    'serialize',
                    'deserialize',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        // init command
        $application->add(new ExportCommand($em, $fileSystem, $this->serializer));

        $command = $application->find('app:export');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->commandTester = null;
        $this->serializer = null;
    }
}