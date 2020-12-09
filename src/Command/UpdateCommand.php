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

use App\Serializer\Traits\Utils;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @version 2.0.0
 * @author  "Matthias Morin" <mat@tangoman.io>
 * @package App\Command
 */
class UpdateCommand extends Command
{
    use Utils;

    const SOURCE_DIRECTORY = __DIR__.'/../../assets/imports/';
    const SOURCE_FILE = 'notion.csv';
    const UNIQUE_PROPERTY = 'id';

    /**
     * @var string
     */
    protected static $defaultName = 'app:update';

    /**
     * @var string[]
     */
    private $options = [
        'className'       => null,
        'classShortName'  => null,
        'encoder'         => null,
        'extension'       => null,
        'fileName'        => null,
        'overwrite'       => null,
        'property'        => null,
        'sourceDirectory' => null,
        'tableName'       => null,
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var ReflectionExtractor
     */
    private $reflectionExtractor;

    public function __construct(
        EntityManagerInterface $em,
        SerializerInterface $serializer
    ) {
        parent::__construct();

        $this->em = $em;
        $this->serializer = $serializer;

        // Finder is not available as a service and cannot be injected
        $this->finder = new Finder();

        // Inflector is not available as a service and cannot be injected
        $this->inflector = InflectorFactory::create()->build();

        // PropertyAccessor is not available as a service and cannot be injected
        // https://symfony.com/doc/current/components/property_access.html
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        // ReflectionExtractor is not available as a service and cannot be injected
        $this->reflectionExtractor = new ReflectionExtractor();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates database table from file')
            ->addOption(
                'className',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Destination Entity Class Name (eg: "App\Entity\User")'
            )
            ->addOption(
                'sourceDirectory',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Source directory',
                self::SOURCE_DIRECTORY
            )
            ->addOption(
                'fileName',
                'f',
                InputOption::VALUE_REQUIRED,
                'Source file name'
            )
            ->addOption(
                'encoder',
                'x',
                InputOption::VALUE_OPTIONAL,
                'File encoder (csv, json, xml, custom:json...)'
            )
            ->addOption(
                'property',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Unique property to match existing Entity by',
                self::UNIQUE_PROPERTY
            )
            ->addOption(
                'overwrite',
                'o',
                InputOption::VALUE_NONE,
                'Overwrite (except when value is empty)'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('className')) {
            $this->options['className'] = $input->getOption('className');
        }
        if ($input->getOption('encoder')) {
            $this->options['encoder'] = $input->getOption('encoder');
        }
        if ($input->getOption('sourceDirectory')) {
            $this->options['sourceDirectory'] = $input->getOption('sourceDirectory');
        }
        if ($input->getOption('fileName')) {
            $this->options['fileName'] = $input->getOption('fileName');
        }
        if ($input->getOption('property')) {
            $this->options['property'] = $input->getOption('property');
        }
        if ($input->getOption('overwrite')) {
            $this->options['overwrite'] = $input->getOption('overwrite');
        }
        // print options in a table
        $io->table(['option', 'value'], $this->optionResolver());

        // find file in the `imports` directory
        $finder = $this->finder->files()->in($this->options['sourceDirectory'])->name($this->options['fileName']);
        if (!$finder->hasResults() || count($finder) !== 1) {
            throw new FileNotFoundException(
                sprintf('File "%s" not found in "%s"', $this->options['fileName'], self::SOURCE_DIRECTORY), 1
            );
        }
        $content = null;
        foreach ($finder as $file) {
            $content = $file->getContents();
        }
        if ($content === '' || $content === null) {
            throw new \Exception(
                sprintf('File "%s" in "%s" is empty', $this->options['fileName'], self::SOURCE_DIRECTORY), 1
            );
        }

        // get repository dynamically (returns "MappingException" when not found)
        $repository = $this->em->getRepository($this->options['className']);

        // decode imports as array
        $items = $this->serializer->decode($content, $this->options['encoder']);

        $progressBar = new ProgressBar($output, count($items));

        /**
         * Main
         */
        foreach ($items as $item) {
            // Unique property required to find in database
            if (!array_key_exists($this->options['property'], $item)) {
                throw new \Exception(
                    sprintf('Required unique property "%s" not found in current item', $this->options['property']), 1
                );
            }

            // Unique property required to find in database
            if (!isset($this->options['property'], $item)) {
                throw new \Exception(
                    sprintf('"%s" value cannot be empty', $this->options['property']), 1
                );
            }

            // find destination entity by value from given unique property
            $entity = $repository->findOneBy([$this->options['property'] => $item[$this->options['property']]]);

            // entity manager handles denormalized objects somehow
            // no choice but to remove, update and persist again
            // this will break relationships with `cascade={'remove'}`
            if ($entity) {
                // normalize entity to merge with current item
                $copy = $this->serializer->normalize($entity, $this->options['className']);

                $this->em->remove($entity);
                $this->em->flush();

                if ($this->options['overwrite']) {
                    $entity = $this->mergeNormalizedObjects($copy, $item, true);
                } else {
                    $entity = $this->mergeNormalizedObjects($copy, $item);
                }

                // denormalize again for persistence
                $entity = $this->serializer->denormalize($entity, $this->options['className']);
            } else {
                $entity = $this->serializer->denormalize($item, $this->options['className']);
            }

            $this->em->persist($entity);
            $this->em->flush();

            // increment progress
            $progressBar->advance();
        }

        $progressBar->finish();

        $io->writeln("\n");
        $io->success(sprintf('%s %s imported.', count($items), count($items) === 1 ? 'row' : 'rows'));

        return 0;
    }

    /**
     * guess correct options from filename or encoder
     *
     * @return array
     * @throws \ReflectionException
     */
    private function optionResolver(): array
    {
        if (!$this->options['sourceDirectory']) {
            throw new \Exception('Source directory cannot be empty', 1);
        }

        if (!$this->options['fileName']) {
            throw new \Exception('Source file cannot be empty', 1);
        }

        $temp = explode('.', $this->options['fileName']);
        $this->options['extension'] = $temp[1];

        // guess className from encoder
        if (!$this->options['className'] && $this->options['encoder']) {
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

        // guess table name, extension and class name from file name
        if (!$this->options['className']) {
            if (strpos($this->options['fileName'], '.')) {
                $this->options['tableName'] = $this->inflector->singularize($temp[0]);
            } else {
                $this->options['tableName'] = $this->inflector->singularize($this->options['fileName']);
            }
            $this->options['className'] = sprintf(
                'App\Entity\%s',
                $this->inflector->classify($this->options['tableName'])
            );
        }

        if (!class_exists($this->options['className'])) {
            throw new \Exception('Entity not found', 1);
        }

        // get class short name from class name
        $this->options['classShortName'] = (new \ReflectionClass($this->options['className']))->getShortName();

        if (!$this->options['tableName']) {
            $this->options['tableName'] = $this->inflector->tableize($this->options['classShortName']);
        }

        // get encoder name from file extension
        if (!$this->options['encoder']) {
            if ($this->options['extension']) {
                $this->options['encoder'] = sprintf('%s:%s', $this->options['tableName'], $this->options['extension']);
            } else {
                $this->options['encoder'] = $this->options['tableName'];
            }
        }

        $table = [];
        foreach ($this->options as $key => $value) {
            $table[] = [$key, $value];
        }

        return $table;
    }
}
