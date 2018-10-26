<?php
    namespace App\Command;

    use App\Command\PoizCommandHelper as PCH;
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

class StyleFixer extends ContainerAwareCommand
{
    const APP_ROOT  = __DIR__ . "/../../";
    const SRC_DIR   = __DIR__ . "/../";

    protected function configure()
    {
        $this
            ->setName("poiz:tracer:fix-style")
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Directory to Scan and Fix')
            ->setDescription('Command-Line based PHP Style Checker example: poiz:tracer:fix-style --path=PATH/TO/DIRECTORY/WITH/PHP_CODES')
            ->setHelp('This command allows you to fix Coding Styles...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper  = $this->getHelper('question');
        $options = $input->getOptions();
        if (isset($options['path']) && ($path = $options['path'])) {
            $cmd = "phpcbf $path";
            $out = shell_exec($cmd);
            $output->writeln($out);
        }
    }
}