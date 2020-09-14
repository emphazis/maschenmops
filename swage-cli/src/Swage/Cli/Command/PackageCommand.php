<?php 

namespace Swage\Cli\Command;

use Swage\Cli\Config\Parameters;
use Swage\Cli\Helper\{CustomFilterIterator, Zip};
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class PackageCommand extends Command
{
    const SUCCESS = 0;
    const ERROR = 1;
    const ARTIFACTS_FOLDER = 'swage-artifacts';

    /**
     * Command name
     * 
     * @var string
     */
    protected static $defaultName = 'package';

    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    public function __construct(string $cwd)
    {
        parent::__construct();

        $this->cwd = $cwd;
        $this->filesystem = new Filesystem();
        $this->finder = new Finder();
    }

    protected function configure()
    {
        $this
            ->setDescription("Creates the swage.yaml file in your project folder.")
            ->setHelp("This command helps you to create the swage.yaml file.")
            ->addOption('skip-build', null, InputOption::VALUE_NONE, 'Skip build operation.')
            ->addOption('skip-copy', null, InputOption::VALUE_NONE, 'Skip copy operation.')
            ->addOption('zip-only', null, InputOption::VALUE_REQUIRED, 'Pack and update single folder.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $skipBuild = $input->getOption('skip-build');
        $skipCopy = $input->getOption('skip-copy');
        $zipOnly = $input->getOption('zip-only');

        $output->writeln([
            'swage-cli', 'Version 1.0', ' ', strtotime("Y-m-d", time()), '',
        ]);

        $config = Yaml::parse($this->cwd.'/swage.yaml');
        $config = new Collection($config);

        $artifacts = realpath('/home/vagrant');
        $artifacts = $artifacts . "/" . static::ARTIFACTS_FOLDER;

        if ($this->filesystem->exists($artifacts)) {
            $this->filesystem->remove($artifacts);
        }

        $this->filesystem->mkdir($artifacts, 0777, true);

        if (! $skipBuild) {

            $this->runProcess(Process::fromShellCommandline(
                '"${:PATH_TO_SCRIPT}"', 
                null,
                ['PATH_TO_SCRIPT' => $this->cwd.'/bin/build-js.sh' ],
                null,
                null
            ));
    
            $this->runProcess(Process::fromShellCommandline(
                '"${:PATH_TO_CONSOLE}" theme:compile', 
                null,
                ['PATH_TO_CONSOLE' => $this->cwd.'/bin/console' ],
                null,
                null
            ));
            
        }

        if (! $skipCopy) {

            $this->filesystem->mkdir($artifacts."/storefront", 0777, true);

            $this->copyArtifacts($this->cwd."/swage-cli/template/storefront", 
                                 $artifacts."/storefront");

            $target = $artifacts . "/storefront/src/custom";
            $this->filesystem->mkdir($target, 0777, true);
            $this->copyArtifacts($this->cwd . "/custom", 
                                $target, 
                                $options = ['files' => []]);


            $target = $artifacts . "/storefront/src/public";
            $this->filesystem->mkdir($target, 0777, true);
            $this->copyArtifacts($this->cwd . "/public", 
                                $target, 
                                $options = ['paths' => ['public/index.php']]);


            if ($this->filesystem->exists($this->cwd."/.env.prod")) {

                echo "Copy .env.prod --> .env".PHP_EOL;

                $this->runProcess(Process::fromShellCommandline(
                    'cp -f "${:SRC}" "${:DEST}" ', 
                    null,
                    [
                        'SRC' => $this->cwd."/.env.prod",
                        'DEST' => $artifacts."/storefront/src/.env"
                    ],
                    null,
                    null
                ));

            }
            
            $this->filesystem->copy(
                $this->cwd."/swage-cli/template/storefront/src/composer.json", 
                $artifacts."/storefront/src/composer.json"
            );
            
            if (true) {
                echo "Run composer in: ".$this->cwd."/swage-cli/template/storefront/src".PHP_EOL;
                $this->runProcess(Process::fromShellCommandline(
                    'composer install -d "${:CWD}" --prefer-dist --no-dev -o', 
                    null,
                    ['CWD' => $this->cwd."/swage-cli/template/storefront/src" ],
                    null,
                    null
                ));
            }

            $target = $artifacts."/storefront/src/vendor";
            $this->filesystem->mkdir($target, 0777, true);
            $this->copyArtifacts(
                $this->cwd."/swage-cli/template/storefront/src/vendor", 
                $target, 
                $options = [
                    'extensions' => ['pdf', 'md'],
                    'files' => ['node_modules'],
                    'paths' => [
                        'shopware/storefront/Resources/app/storefront/build',
                        'shopware/storefront/Resources/app/storefront/src',
                        'shopware/storefront/Resources/app/storefront/vendor',
                        'shopware/storefront/Resources/app/administration/src',
                        'shopware/storefront/Test',
                    ] 
                ]);

        }

        $this->filesystem->chmod($artifacts . "/storefront", 0777, 0000, true);

        /**
         * Zipping
         */
        if (!$zipOnly || $zipOnly == "vendor") {

            $this->zipFolder($artifacts . "/storefront/vendor-layer.zip", 
                             $artifacts . "/storefront/src/vendor",
                             $artifacts . "/storefront");

            $this->filesystem->chmod($artifacts . "/storefront/vendor-layer.zip", 0777, 0000, true);
            $this->filesystem->remove($artifacts . "/storefront/src/vendor");

        }

        $this->zipFiles(
            $artifacts . "/storefront/task-layer.zip",
            [
                new \SplFileInfo($artifacts . "/storefront/src/public/index.php"),
                new \SplFileInfo($artifacts . "/storefront/src/bin/console")
            ],
            $artifacts . "/storefront"
        );

        $this->filesystem->remove($artifacts . "/storefront/src/public/index.php");

        if (!$zipOnly || $zipOnly == "src") {

            $this->zipFolder($artifacts . "/storefront/src-layer.zip", 
                             $artifacts . "/storefront/src",
                             $artifacts . "/storefront");

            $this->filesystem->chmod($artifacts . "/storefront/src-layer.zip", 0777, 0000, true);

        }

        return Command::SUCCESS;
        
    }

    private function zipFolder(string $pathToArchive, $folder, string $baseFolder): void
    {
        if ($this->filesystem->exists($pathToArchive)) {
            $this->filesystem->remove($pathToArchive);
        }

        if (! is_array($folder)) {
            $folder = [$folder];
        }

         // Create ZipArchive
         $archive = new Zip($pathToArchive);
         // Add: folder
         $finder = new Finder();
         $finder->ignoreDotFiles(false)->in($folder);

         foreach ($finder as $file) {
             $archive->add($file, $baseFolder);
         }
         // Close archive
         $archive->close();

         unset($archive);
    }

    private function zipFiles(string $pathToArchive, array $files, string $baseFolder): void
    {
        if ($this->filesystem->exists($pathToArchive)) {
            $this->filesystem->remove($pathToArchive);
        }

         // Create ZipArchive
         $archive = new Zip($pathToArchive);
         // Add: folder
         foreach ($files as $file) {
             $archive->add($file, $baseFolder);
         }
         // Close archive
         $archive->close();
    }

    private function runProcess(Process $process): void
    {
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
               echo $buffer;
            } else {
                echo $buffer;
            }
        });
    }


    private function copyArtifacts($src, $target, array $options = []): void 
    {
        $filter = new CustomFilterIterator(new \RecursiveDirectoryIterator($src));
        $filter->setOptions($options);

        $iterator = new \RecursiveIteratorIterator(
                        $filter, \RecursiveIteratorIterator::SELF_FIRST
                    );

        (new Filesystem())->mirror($src, $target, $iterator);
    }

}
