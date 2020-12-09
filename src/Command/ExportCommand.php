<?php

/**
 * This file is part of the TangoMan package.
 *
 * Copyright (c) 2020 "Matthias Morin" <mat@tangoman.io>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace App\Command;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ExportCommand
 * @version 2.0.0
 * @author  "Matthias Morin" <mat@tangoman.io>
 * @package App\Command
 */
class ExportCommand extends Command
{
    const DESTINATION_DIRECTORY = __DIR__.'/../../assets/exports/';
    const DEFAULT_ENCODER = 'json';
    const NORMALIZATION_GROUP = 'export';

    /**
     * @var string
     */
    protected static $defaultName = 'app:export';

    /**
     * @var null[]
     */
    private $options = [
        'className'            => null,
        'classShortName'       => null,
        'destinationDirectory' => null,
        'encoder'              => null,
        'extension'            => null,
        'fileName'             => null,
        'filePath'             => null,
        'group'                => null,
        'tableName'            => null,
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Inflector
     */
    private $inflector;

    public function __construct(
        EntityManagerInterface $em,
        Filesystem $filesystem,
        SerializerInterface $serializer
    ) {
        parent::__construct();

        $this->em = $em;
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;

        // Inflector is not available as a service and cannot be injected
        $this->inflector = InflectorFactory::create()->build();
    }

    protected function configure()
    {
        $this
            ->setDescription('Export table from database to file')
            ->addOption(
                'className',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Entity Class Name (eg: "App\Entity\User")'
            )
            ->addOption(
                'encoder',
                'x',
                InputOption::VALUE_OPTIONAL,
                'File encoder (csv, json, xml, custom:csv...)'
            )
            ->addOption(
                'group',
                'g',
                InputOption::VALUE_OPTIONAL,
                'Normalization group',
                self::NORMALIZATION_GROUP
            )
            ->addOption(
                'destinationDirectory',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Source directory',
                self::DESTINATION_DIRECTORY
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('className')) {
            $this->options['className'] = $input->getOption('className');
        }
        if ($input->getOption('encoder')) {
            $this->options['encoder'] = $input->getOption('encoder');
        }
        if ($input->getOption('group')) {
            $this->options['group'] = $input->getOption('group');
        }
        if ($input->getOption('destinationDirectory')) {
            $this->options['destinationDirectory'] = $input->getOption('destinationDirectory');
        }
        // print options in a table
        $io->table(['option', 'value'], $this->optionResolver());

        $items = $this->em->getRepository($this->options['className'])->findAll();

        // encode each piece of data into destination format
        $content = $this->serializer->serialize(
            $items,
            $this->options['encoder'],
            ['groups' => $this->options['group']]
        );

        // write file to destination folder
        $this->filesystem->dumpFile(
            $this->options['filePath'],
            $content
        );

        $io->writeln("\n");
        $io->success(sprintf('%s %s exported.', count($items), count($items) === 1 ? 'row' : 'rows'));

        return 0;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function optionResolver(): array
    {
        if (!$this->options['destinationDirectory']) {
            throw new \Exception('Destination directory cannot be empty', 1);
        }

        if (!$this->options['className'] && !$this->options['encoder']) {
            throw new \Exception('Class name and encoder cannot both be empty', 1);
        }

        if (!$this->options['encoder']) {
            $this->options['encoder'] = self::DEFAULT_ENCODER;
            $this->options['extension'] = self::DEFAULT_ENCODER;
        }

        // guess className from encoder
        if (!$this->options['className']) {
            // encoder is formatted like `encoder:extension`
            if (strpos($this->options['encoder'], ':')) {
                $this->options['className'] = sprintf(
                    'App\Entity\%s',
                    $this->inflector->classify(explode(':', $this->options['encoder'])[0])
                );
            } else {
                $this->options['className'] = sprintf(
                    'App\Entity\%s',
                    $this->inflector->classify($this->options['encoder'])
                );
            }
        }

        if (!class_exists($this->options['className'])) {
            throw new \Exception('Entity not found', 1);
        }

        // get class short name from class name
        $this->options['classShortName'] = (new \ReflectionClass($this->options['className']))->getShortName();

        // get table name from class short name
        $this->options['tableName'] = $this->inflector->tableize($this->options['classShortName']);

        // guess file extension from encoder
        // encoder is formatted like `encoder:extension`
        if (strpos($this->options['encoder'], ':')) {
            $this->options['extension'] = explode(':', $this->options['encoder'])[1];
        }

        // get file name
        if ($this->options['extension']) {
            $this->options['fileName'] = sprintf(
                '%s.%s',
                $this->inflector->pluralize($this->options['tableName']),
                $this->options['extension']
            );
        } else {
            $this->options['fileName'] = $this->inflector->pluralize($this->options['tableName']);
        }

        // get file path
        $this->options['filePath'] = $this->options['destinationDirectory'] . $this->options['fileName'];

        $table = [];
        foreach ($this->options as $key => $value) {
            $table[] = [$key, $value];
        }

        return $table;
    }
}
