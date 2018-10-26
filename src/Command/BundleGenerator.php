<?php
    namespace App\Command;

    use App\Command\BundleGeneratorHelper as BGH;
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

class BundleGenerator extends ContainerAwareCommand
{
    const APP_ROOT  = __DIR__ . "/../../";
    const SRC_DIR   = __DIR__ . "/../";

    protected function configure()
    {
        $this
            ->setName("bundle:generate")
            ->setDescription('Generates a Symfony 4 S-Linked Bundle')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('bundle', null, InputOption::VALUE_REQUIRED, 'The new Bundle Name...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();
        $helper  = $this->getHelper('question');
        $bundle = $options['bundle'];
        if(!$bundle || empty(trim($bundle))){
            $output->write(BGH::colorize("Did you forget to pass the Bundle-Option with ", "RED"));
            $output->write(BGH::colorize("--bundle=\"BundleName\"?", "CYAN"));
            $output->writeln(BGH::colorize("\nTry ", "RED") . BGH::colorize("\"php bin/console bundle:generate --bundle='BundleName'\"?", "PURPLE"));
            return;
        }
        $baseDirectory = static::APP_ROOT . "{$bundle}";
        $bundleDirectories = [
            "controller"=> "{$baseDirectory}/Controller/",
            "di"=> "{$baseDirectory}/DependencyInjection/",
            "config"=> "{$baseDirectory}/Resources/config/",
            "views"=> "{$baseDirectory}/Resources/views/",
            "public"=> "{$baseDirectory}/Resources/public/",
            "tests"=> "{$baseDirectory}/Tests/",
            "services"=> "{$baseDirectory}/Services/",
            "commands"=> "{$baseDirectory}/Commands/",
        ];

        foreach ( $bundleDirectories as $key => $bundleDirectory ) {
            if(!file_exists($bundleDirectory)){
                mkdir($bundleDirectory, 0777, true);
            }
            switch ($key){
                case "controller":
                    $msg = BGH::createControllerFile($helper, $input, $output, $bundle."Controller", $bundle);
                    $output->writeln($msg);
                    break;
                case "commands":
                    $msg = BGH::createCommandFile($helper, $input, $output, $bundle."Command", $bundle);
                    $output->writeln($msg);
                    break;
                case "services":
                    $msg =  BGH::createAndConfigureService($helper, $input, $output, $bundle."Service", $bundle);
                    $output->writeln($msg);
                    break;
                case "views":
                case "template":
                    $stub = BGH::createTemplateStub();
                    $path = $bundleDirectories['views']."index.html.twig";
                    if(BGH::saveGeneratedFile($path, $stub) ) {
	                    $templateAlias  = BGH::getCharacterSeparatedSnakeCasedTransposition($bundle, ".");
                    	$templateStatus = BGH::addAliasedTemplatePathToTwigConfig("%kernel.project_dir%/{$bundle}/Resources/views", $templateAlias);
                        $msg =  BGH::colorize("View Template ", "GREEN") .
                                BGH::colorize(" successfully created in... ", "GREEN") .
                                BGH::colorize("\"" . $bundleDirectories['views']. "index.html.twig\"", "CYAN");
                        $output->writeln($msg);
                        if($templateStatus){
	                        $msg =  BGH::colorize("The Bundle's Template Path was successfully added to Twig's Configuration File as", "GREEN") .
	                                BGH::colorize("\"" . $templateAlias . "\"", "CYAN");
	                        $output->writeln($msg);
                        }
                    }
                    break;
            }
        }
        if(!file_exists($baseDirectory. "/{$bundle}Bundle.php")){
            $bundleText = BGH::getBundleStub($bundle."Bundle", $bundle);
            if(BGH::saveGeneratedFile($baseDirectory. "/{$bundle}Bundle.php", $bundleText) ) {
                $msg =  BGH::colorize("The Bundle was ", "GREEN") .
                        BGH::colorize(" successfully created in... ", "GREEN") .
                        BGH::colorize("\"" . $baseDirectory . "/{$bundle}Bundle.php\"", "CYAN");
                $output->writeln($msg);
            }
        }

        // SYM LINK THE BUNDLE
        self::symLinkAndRegisterBundle($baseDirectory, $bundle."\\{$bundle}Bundle", $output);
    }

    protected static function symLinkAndRegisterBundle($baseDirectory, $fullyQualifiedBundleClass, $output){
    	// SYM-LINK THE BUNDLE INTO THE VENDOR DIRECTORY
        $cmd = "ln -s " . realpath("{$baseDirectory}") . " " . static::APP_ROOT . "vendor/" . basename($baseDirectory);
        if(!is_link(static::APP_ROOT . "vendor/" . basename($baseDirectory))){
            shell_exec($cmd);
        }

        $bundleFile = static::APP_ROOT . "config/bundles.php";
        $annotationFile = static::APP_ROOT . "config/routes.yaml";
	    $routesContent = explode(PHP_EOL, file_get_contents($annotationFile));
        $composerFile = static::APP_ROOT . "composer.json";
        $bundleFileContent = require $bundleFile;
        if($bundleFileContent){
        	if(!array_key_exists("{$fullyQualifiedBundleClass}::class", $bundleFileContent)){
		        $bundleFileContent["{$fullyQualifiedBundleClass}"] = ['all'=>true];
	        }
        }

        $bundleUpdate = "<?php\n\nreturn\n" . var_export($bundleFileContent, true) . ";";
        if(BGH::saveGeneratedFile($bundleFile, $bundleUpdate) ) {
            $msg =  BGH::colorize("The Bundle ", "GREEN") .
                    BGH::colorize("\"{$fullyQualifiedBundleClass}\"", "CYAN") .
                    BGH::colorize(" was successfully  registered and sym-linked...", "GREEN");
            $output->writeln($msg);
        }
        $parts  = explode('\\', $fullyQualifiedBundleClass);
        $key = preg_replace("#([A-Z])#", "_$1", end($parts));
        $isRegistered = false;
        foreach ( $routesContent as $iKey => $routeData ) {
            if ( !is_bool(strpos( trim($routeData), trim(strtolower( $key ).":" )))) {
                $isRegistered = true;
                break;
            }
        }
        if(!$isRegistered) {
	        $ymlRoutesArr = BGH::getYmlDataAsArray($annotationFile);
	        if( !array_key_exists(strtolower($key), $ymlRoutesArr) ){
		        $ymlRoutesArr[strtolower($key)] = [
			        'resource' => "@{$parts[sizeof($parts)-1]}/Controller",
			        'type' => 'annotation'
		        ];
	        }
	        $ymlRoutesYml = BGH::reSaveYmlData($ymlRoutesArr, $annotationFile);
            if($ymlRoutesYml) {
                $msg =  BGH::colorize("Annotation Route has also been enable for this Bundle...", "GREEN") ;
                $output->writeln($msg);
            }
        }

        $json = json_decode( file_get_contents($composerFile), true);
        $json['autoload']['psr-4'][$parts[0] .'\\'] = $parts[0] . "/";
        file_put_contents($composerFile, json_encode($json, JSON_PRETTY_PRINT));
        $cmd = "composer dump-autoload";
        $output->writeln(BGH::colorize(shell_exec($cmd), "GREEN"));
    }
}