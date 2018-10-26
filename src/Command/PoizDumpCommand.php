<?php

    namespace App\Command;

    use App\Command\PoizCommandHelper as PCH;
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

class PoizDumpCommand extends ContainerAwareCommand
{
    const APP_ROOT  = __DIR__ . "/../../../";
    const SRC_DIR   = __DIR__ . "/../../";

    protected function configure()
    {
        $this
            ->setName('poiz:run:dump')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addArgument('controller', null, InputArgument::OPTIONAL, null)
            ->addArgument('view', null, InputArgument::OPTIONAL, null)
            ->addArgument('template', null, InputArgument::OPTIONAL, null)
            ->addArgument('twig_extension', null, InputArgument::OPTIONAL, null)
            ->addArgument('twig', null, InputArgument::OPTIONAL, null)
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
            ->setDescription('Dumps a Resource - EG: Twig Template, Controller, Service, etc based on the Arguments. example: poiz:run:dump controller')
            ->setHelp('This command allows you to List Twig Template, Controller, Service, etc based on the Arguments...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument   = $input->getArgument('argument');
        if ($argument) {
            if($msg = PCH::dumpFiles($argument)) {
                $output->writeln($msg);
            }
        }
    }


}
