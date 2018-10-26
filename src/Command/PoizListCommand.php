<?php

    namespace App\Command;

    use App\Command\PoizCommandHelper as PCH;
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

class PoizListCommand extends ContainerAwareCommand
{
    const APP_ROOT  = __DIR__ . "/../../";
    const SRC_DIR   = __DIR__ . "/../";

    protected function configure()
    {
        $this
            ->setName('poiz:run:list')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, "Type of Classes to list eg: --type=\"controllers | views | services\"")
            ->setDescription('Creates a new Twig Template, Controller or Service based on the Arguments.')
	        ->setDescription('Lists a Resource - EG: Twig Template, Controller, Service, etc based on the Arguments. example: poiz:run:list --controller')
	        ->setHelp('This command allows you to List Twig Template, Controller, Service, etc based on the Arguments...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper  = $this->getHelper('question');
        $options = $input->getOptions();

        if (isset($options['type']) && ($ctl = $options['type'])) {
            if($msg = PCH::listFiles($helper, $input, $output, $ctl)) {
                $output->writeln($msg);
            }
        }

    }


}
