<?php

    namespace App\Command;

    use App\Command\PoizCommandHelper as PCH;
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

class PoizDeleteCommand extends ContainerAwareCommand
{
    const APP_ROOT  = __DIR__ . "/../../";
    const SRC_DIR   = __DIR__ . "/../";

    protected function configure()
    {
        $this
            ->setName('poiz:run:delete')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('controller', null, InputOption::VALUE_REQUIRED, 'Controller Name')
            ->addOption('view', null, InputOption::VALUE_REQUIRED, 'Template Name')
            ->addOption('template', null, InputOption::VALUE_REQUIRED, 'Template Name')
            ->addOption('twig_extension', null, InputOption::VALUE_REQUIRED, 'Twig Extension')
            ->addOption('twig', null, InputOption::VALUE_REQUIRED, 'Twig Extension')
            ->addOption('service', null, InputOption::VALUE_REQUIRED, 'Deletes a registered Service')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
	        ->setDescription('Deletes a Resource - EG: Twig Template, Controller, Service, etc based on the Arguments. example: poiz:run:delete --controller="TheControllerName" --view="TheViewName"')
	        ->setHelp('This command allows you to delete a Twig Template, Controller, Service, etc based on the Arguments...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper  = $this->getHelper('question');
        $options = $input->getOptions();

        if (isset($options['controller']) && ($ctl = $options['controller'])) {
            if($msg = PCH::deleteController($helper, $input, $output, $ctl)) {
                $output->writeln($msg);
            }
        }

        if (isset($options['template']) && ($vw = $options['template'])) {
            if($msg = PCH::deleteTemplate($helper, $input, $output, $vw)) {
                $output->writeln($msg);
            }
        }

        if (isset($options['view']) && ($vw = $options['view'])) {
            if($msg = PCH::deleteTemplate($helper, $input, $output, $vw)) {
                $output->writeln($msg);
            }
        }

        if (isset($options['twig_extension']) && ($ext = $options['twig_extension'])) {
            if($msg = PCH::deleteTwigExtension($helper, $input, $output, $ext)) {
                $output->writeln($msg);
            }
        }

        if (isset($options['twig']) && ($ext = $options['twig'])) {
            if($msg = PCH::deleteTwigExtension($helper, $input, $output, $ext)) {
                $output->writeln($msg);
            }
        }

        if (isset($options['service']) && ($ext = $options['service'])) {
            if($msg = PCH::deleteService($helper, $input, $output, $ext)) {
                $output->writeln($msg);
            }
        }
    }


}
