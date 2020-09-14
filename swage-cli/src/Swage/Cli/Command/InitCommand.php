<?php 

namespace Swage\Cli\Command;

use Swage\Cli\Config\Parameters;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;


class InitCommand extends Command
{
    const SUCCESS = 0;
    const ERROR = 1;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'init';

    protected $cwd;

    public function __construct(string $cwd)
    {
        parent::__construct();

        $this->cwd = $cwd;
    }

    protected function configure()
    {
        $this
            ->setDescription("Creates the swage.yaml file in your project folder.")
            ->setHelp("This command helps you to create the swage.yaml file.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Init swage.yaml',
            '===============',
            '',
        ]);

        $swage = new Collection();
        $helper = $this->getHelper('question');
        $question = new Question('AWS ID: ');

        if (! $awsId = $helper->ask($input, $output, $question)) {

            return Command::ERROR;

        }

        $swage->put(Parameters::AWS_ID, $awsId);

        file_put_contents($this->cwd.'/swage-cli/swage.yaml', Yaml::dump($swage->toArray()));

        return Command::SUCCESS;

    }
}
