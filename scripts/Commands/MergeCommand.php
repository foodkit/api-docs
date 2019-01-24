<?php
/**
 * @author Dmitriy Lezhnev <dmitry@foodkit.io>
 * Date: 20 Jan 2019
 */
declare(strict_types=1);

namespace Scripts\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeCommand extends Command
{
    protected static $defaultName = 'merge';

    # Cli links
    protected $swagger_cli = __DIR__ . "/../../node_modules/.bin/swagger-cli";
    protected $speccy = __DIR__ . "/../../node_modules/.bin/speccy";
    protected $yaml2json = __DIR__ . "/../../node_modules/.bin/yaml2json";


    protected function configure()
    {
        $this
            ->setDescription('Merges a multiple spec files into one')
            ->setHelp('This command reads a template spec files and resolves all "paths" references')
            ->addArgument('input', InputArgument::REQUIRED, 'Main OpenAPI spec YAML file')
            ->addArgument('output', InputArgument::REQUIRED, 'Merged spec YAML file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->checkCliLinks($output);

            $files = $this->getReferencesFromGivenInputSpec($input->getArgument('input'), $output);

            if ($files) {
                $this->validateOpenAPISpecFiles($files, $output);
                $tmpFiles = $this->resolveReferencesInFiles($files, $output);
                $this->mergeReferencedFilesIntoOutputSpec($tmpFiles, $input->getArgument('output'), $input->getArgument('input'), $output);
                $this->cleanupTmpFiles($tmpFiles, $output);
            }

            $output->writeln('✅ Done');
        } catch (\Throwable $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }

    /**
     * Remove tmp files
     *
     * @param $tmpFiles
     */
    private function cleanupTmpFiles($tmpFiles)
    {
        foreach ($tmpFiles as $tmpFile) {
            unlink($tmpFile);
        }
    }

    /**
     * Read the spec and get all references from the "paths/$ref" nodes
     *
     * ...
     * paths:
     *  - $ref: path/to/spec1.yaml
     *  - $ref: path/to/spec2.yaml
     *  ...
     *
     * @param $specPath
     * @param OutputInterface $output
     * @return array
     * @throws \Exception
     */
    private function getReferencesFromGivenInputSpec($specPath, OutputInterface $output): array
    {
        $output->writeln("🗄  Read References From The Spec");

        $files = [];

        $specRealPath = realpath($specPath);
        if (!$specRealPath && !is_readable($specRealPath)) {
            throw new \Exception("Input spec is not available");
        }
        $specBasePath = dirname($specRealPath);

        $specJSON = json_decode(`$this->yaml2json $specRealPath`, true);
        if (!$specJSON || !array_key_exists('paths', $specJSON)) {
            throw new \Exception("$specRealPath has unexpected JSON structure");
        }

        if (!$specJSON['paths']) {
            $output->writeln("No references found");
        } else {
            foreach ($specJSON['paths'] as $path) {
                if (isset($path['$ref'])) {

                    $referencedFile = realpath($specBasePath . "/" . $path['$ref']);
                    if (!$referencedFile || !is_readable($referencedFile)) {
                        throw new \Exception("$referencedFile is not available");
                    } else {
                        $output->writeln("$referencedFile\tFOUND");
                    }

                    $files[] = $referencedFile;
                }
            }
        }
        $output->write("\n\n");

        return $files;
    }

    /**
     * Check that required console tools are available
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function checkCliLinks(OutputInterface $output)
    {
        $problemsDetected = false;
        $tools = [$this->swagger_cli, $this->speccy, $this->yaml2json];
        foreach ($tools as $tool) {
            $toolPath = realpath($tool);

            if (!$toolPath) {
                $output->writeln("<error>Console tool " . basename($tool) . " does not exist</error>");
                $problemsDetected = true;
                continue;
            }
            if (!is_executable($toolPath)) {
                $output->writeln("<error>Console tool $tool is not executable</error>");
                $problemsDetected = true;
                continue;
            }
        }

        if ($problemsDetected) {
            throw new \Exception("Required console tools are not available. Stop.");
        }
    }

    /**
     * Validate files and check that they are valid OpenAPI3 specs
     *
     * @param $files
     * @param OutputInterface $output
     */
    private function validateOpenAPISpecFiles($files, OutputInterface $output)
    {
        $output->writeln("🔦 Validate YAML Files");
        foreach ($files as $file) {
            $fileRealPath = realpath($file);
            exec("$this->swagger_cli validate $fileRealPath");
            $output->writeln("$fileRealPath\tVALID");
        }
        $output->write("\n\n");
    }

    /**
     * Resolve dependencies and thus make complete huge files
     *
     * @param array $files
     * @param OutputInterface $output
     * @return array of tmp file paths
     * @throws \Exception
     */
    private function resolveReferencesInFiles(array $files, OutputInterface $output): array
    {
        $output->writeln("📎 Resolve References");

        $tmpFilesBuffer = [];
        foreach ($files as $file) {
            $fileRealPath = realpath($file);
            $fileTmpPath = tempnam(sys_get_temp_dir(), 'yaml_');
            exec("$this->speccy resolve $fileRealPath -o $fileTmpPath");
            $output->writeln("$fileRealPath\tRESOLVED DEPENDENCIES");
            $tmpFilesBuffer[] = $fileTmpPath;
        }
        $output->write("\n\n");

        return $tmpFilesBuffer;
    }

    /**
     *
     *
     * @param array $specFiles
     * @param $outputPath
     * @param $templateSpecPath
     * @param OutputInterface $output
     */
    private function mergeReferencedFilesIntoOutputSpec(array &$specFiles, $outputPath, $templateSpecPath, OutputInterface $output)
    {
        $output->writeln("📑 Merge Files Into A Single File");

        # 1 Convert YAML=>JSON
        foreach ($specFiles as $i => $specFile) {
            $fileJsonPath = tempnam(sys_get_temp_dir(), 'json_');
            exec("$this->yaml2json $specFile > $fileJsonPath");
            unlink($specFile);
            $specFiles[$i] = $fileJsonPath;
        }


        # 2 Collect paths tree
        $mergedPaths = [];
        foreach ($specFiles as $specFile) {
            $specJSON = json_decode(file_get_contents($specFile), true);
            if (!$specJSON) {
                $output->writeln("<info>$specFile is not a valid JSON file</info>");
            }

            if (!array_key_exists('paths', $specJSON)) {
                $output->writeln("<info>$specFile has not valid 'paths' key</info>");
            } else {
                $mergedPaths += $specJSON['paths'];
            }
        }

        # 3 Output the final file
        $templateSpecJSON = json_decode(`$this->yaml2json $templateSpecPath`, true);
        $templateSpecJSON['paths'] = $mergedPaths;
        file_put_contents($outputPath, json_encode($templateSpecJSON, JSON_PRETTY_PRINT));
        $output->writeln("<info>" . realpath($outputPath) . "\tMERGED</info>");

        $output->write("\n\n");
    }
}