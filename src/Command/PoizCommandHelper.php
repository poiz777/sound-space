<?php
    /**
     * Author      : Poiz
     * Date        : 20.03.18
     * Time        : 18:00
     * FileName    : PoizCommandHelper.php
     * ProjectName : simf.pz
     */
    
    namespace App\Command;

    use App\CodePool\Base\Poiz\Helpers\FolderBrowser;
    use Symfony\Component\Console\Formatter\OutputFormatterStyle;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ChoiceQuestion;
    use Symfony\Component\Console\Question\Question;

class PoizCommandHelper
{
    public static function createViewFile($helper, InputInterface $input, OutputInterface $output, $vw)
    {
            $questionX  = new Question('Enter a Dedicated Symfony4 View Folder Name: ');
            $viewsDir    = PoizCreateCommand::APP_ROOT . "templates/";
        if ($viewFolder = $helper->ask($input, $output, $questionX)) {
            $vw         = preg_replace("#\.html|\.twig#", "", $vw);
            $viewsDir  .= $viewFolder;
        }
        $baseLOut   = ['layouts/master.html.twig'];
        $viewsFld   = [$viewFolder];
        $view       = "";
        $files      = null;
        if(file_exists($viewsDir)) {
                $files = scandir($viewsDir);
        }
        if($files) {
            foreach ( $files as $file ) {
                if (is_dir("{$viewsDir}/{$file}") && ( $file != ".." && $file != "." ) ) {
                    $viewsFld[] = $file;
                } else if (is_file("{$viewsDir}/{$file}") ) {
                    $baseLOut[] = "{$file}";
                }
            }
        }
            $question   = new ChoiceQuestion('Select a Base View to extend: ', $baseLOut);
        if ($layout = $helper->ask($input, $output, $question)) {
            // READ THE LAYOUT, EXTRACT ALL {% block %} Tags..
            try {
                $blData = file_get_contents("{$viewsDir}/{$layout}");
            }catch (\Exception $e){
                $blData = file_get_contents(PoizCreateCommand::APP_ROOT . "templates/{$layout}");

            }
            preg_match_all("#\{%\s*?block\s(.)*?%\}*?#", $blData, $matches);
            $blocks = array_map(
                function ($a) {
                        return preg_replace("#\{%\s*?block\s|\s*?%#", "", $a);
                }, $matches[0]
            );

            $view      .= "{% extends \"{$layout}\" %}\n\n";
            foreach($blocks as $block){
                $view  .= "{% block {$block} %}\n\n";
                $view  .= "{% endblock %}\n\n\n";
            }
            $question   = new ChoiceQuestion('Select appropriate View Directory: ', $viewsFld);
            if ($viewBlock = $helper->ask($input, $output, $question)) {
                if(self::saveGeneratedFile("{$viewsDir}/{$vw}.html.twig", $view) ) {    // /{$viewBlock}/
                    return  self::colorize("View Template ", "GREEN") .
                    self::colorize("\"{$vw}.html.twig\"", "CYAN") .
                    self::colorize(" successfully created... ", "GREEN");
                }
            }
        }
        return null;
    }

    public static function createControllerFile($helper, InputInterface $input, OutputInterface $output, string $ctl, $ask=true)
    {
        $question  = new Question('Enter Bundle-Name: ');
        $nsp = is_bool($ask) && $ask === true ? $helper->ask($input, $output, $question) : $ask;
        if ($nsp) {
            $nspClean   = preg_replace("#[\\:]#", "/", $nsp);
            if(stristr($nspClean, "App") ) {
                $nspClean = str_replace("App", "", $nspClean);
                $path       = PoizCreateCommand::SRC_DIR . $nspClean .  "Controller/{$ctl}.php";
            }else{
                $path       = PoizCreateCommand::APP_ROOT . $nspClean .  "/Controller/{$ctl}.php";
            }
            $ctrData    = self::createControllerStub($nsp, $ctl);

            if(self::saveGeneratedFile($path, $ctrData) ) {
                return  self::colorize("Controller ", "GREEN") .
                self::colorize("\"{$ctl}\"", "CYAN") .
                self::colorize(" successfully created... ", "GREEN");
            }
        }
        return null;
    }

    public static function createControllerStub($nsp, $ctl){
        return <<<CTR
<?php

	namespace {$nsp}\Controller;

	# use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // FOR SYMFONY VERSIONS LOWER THAN 4.0
	use Symfony\Component\Routing\Annotation\Route;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Symfony\Component\HttpFoundation\Request;

	class {$ctl} extends Controller {

	    /**
	     * @Route("/", name="route_name")
	     */
	    public function indexAction(Request \$request) {
	        // replace this example code with whatever you need
	        return \$this->render('path/to-template.html.twig', []);
	    }
	}

CTR;
    }

    public static function createTemplateStub()
    {
        return <<<TPL
{% extends "layouts/master.html.twig" %}

{% block title %}

{% endblock %}


{% block inline_css %}

{% endblock %}


{% block pre_content %}

{% endblock %}


{% block content %}

{% endblock %}


{% block footer %}

{% endblock %}


{% block footer_scripts %}

{% endblock %}



TPL;
    }

    public static function createCommandFile($helper, InputInterface $input, OutputInterface $output, string $commandClass, $ask=true)
    {
        $question  = new Question('Enter Bundle-Name: ');
        $question2  = new Question('Enter Command-String (example - app:run:compile): ');
        $nsp = is_bool($ask) && $ask === true ? $helper->ask($input, $output, $question) : $ask;

        if ($nsp ) {
            $cs = self::getColonBasedServiceName($nsp);
            $commandString = is_bool($ask) && $ask === true ? $helper->ask($input, $output, $question2) : "{$cs}:run";
            $nspClean   = preg_replace("#[\\:]#", "/", $nsp);
            if(stristr($nspClean, "App") ) {
                $nspClean = str_replace("App", "", $nspClean);
                $path       = PoizCreateCommand::SRC_DIR . $nspClean .  "Command/{$commandClass}.php";
                $packageName  = "Command";
                $nameSpace  = "App\\Command";
            }else{
                $path       = PoizCreateCommand::APP_ROOT . $nspClean .  "/Commands/{$commandClass}.php";
                $packageName  = "Commands";
                $nameSpace  = "{$ask}\\Commands";
            }
            $key = strtolower($packageName);
            $command =<<<CMD
<?php
	namespace {$nameSpace};

	use App\Command\PoizCommandHelper as PCH;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;

	class {$commandClass} extends ContainerAwareCommand {
		const APP_ROOT  = __DIR__ . "/../../";
		const SRC_DIR   = __DIR__ . "/../";

		protected function configure() {
			\$this
				->setName("$commandString")
				->setDescription('Command Description')
				->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
                ->addOption('option', null, InputOption::VALUE_REQUIRED, 'Command Option');
		}

		protected function execute(InputInterface \$input, OutputInterface \$output) {
			\$helper  = \$this->getHelper('question');
			\$options = \$input->getOptions();
			if (isset(\$options['option']) && (\$option = \$options['option'])) {
                \$output->writeln(\$options['option']);
			}
            \$output->writeln('This Command needs some Work....so, now, get to work :-)');
		}
	}
CMD;

            $ymlFile = PoizCreateCommand::APP_ROOT . "config/services.yaml";
            $strContent = static::getSavableData($commandClass, $nsp, $ymlFile, "app.{$key}.", $packageName);
            self::saveGeneratedFile($ymlFile, $strContent);

            if(self::saveGeneratedFile($path, $command) ) {
                return  self::colorize("Command ", "GREEN") .
                self::colorize("\"{$commandClass}\"", "CYAN") .
                self::colorize(" successfully created... ", "GREEN");
            }
        }
        return null;
    }

    public static function listFiles($helper, InputInterface $input, OutputInterface $output, string $ctl)
    {
            $fs             = new FolderBrowser(['flatReturn'=>true]);
            $msgKey         = "";
            $pattern    = "";
        $question  = new Question('Enter Bundle-Name: ');
        if ($nsp = $helper->ask($input, $output, $question)) {
                $nspClean = preg_replace("#[\\:]#", "/", $nsp)  . "/";
            if (stristr($nspClean, "App/") ) {
                $nspClean = str_ireplace("App/", "", $nspClean);
            }
                switch ( $ctl ) {
            case "controller":
            case "controllers":
                $path = PoizCreateCommand::SRC_DIR . $nspClean . "Controller/";
                $pattern = "(.*)?(\.php)$";

                break;
            case "view":
            case "views":
            case "template":
            case "templates":
                $path = PoizCreateCommand::APP_ROOT . "templates/";
                $pattern = "(.*)?(\.twig|\.html.twig)";

                break;
            case "service":
            case "services":
                $path = PoizCreateCommand::SRC_DIR . $nspClean . "Services/";
                $pattern = "(.*)?(\.php)$";
                break;
            case "command":
            case "commands":
                $path = PoizCreateCommand::SRC_DIR . $nspClean . "Command/";
                $pattern = "(.*)?(\.php)$";
                break;
            default:
                $path = null;

                }

                if($path && file_exists($path)) {
                    FolderBrowser::setRootDir($path);
                    $files    = $fs->recursivelyExtractFilesWithPattern($pattern);
                    $files    = array_map(
                        function ( $file ) {
                                return "\t" . self::colorize(pathinfo($file, PATHINFO_BASENAME), "GREEN");
                        }, $files 
                    );
                    return implode("\n", $files);

                }else{
                    return self::colorize("\n\t\n\t\t"  .self::colorize("Are You sure the Bundle: ", "PURPLE"), "WARNING")  .
                    self::colorize("\n\t\n\t\t" . self::colorize("\"$nsp\" ", "CYAN") .  self::colorize("exists?", "PURPLE"), "WARNING")  .
                    self::colorize("\n\n  ", "WARNING")  .
                    "\t\t" . self::colorize(self::colorize("\n\t\n\t\tPath: \"$path\" is invalid.", "RED"), "WARNING") .
                    self::colorize("\n\t\n\t\tExiting...\n\t", "WARNING");
                }
        }
    }

    public static function createTwigExtension($helper, InputInterface $input, OutputInterface $output, string $ext)
    {
        $question  = new Question('Enter Bundle-Name: ');
        if ($nsp = $helper->ask($input, $output, $question)) {
            $nspClean   = preg_replace("#[\\:]#", "/", $nsp);
            $path       = PoizCreateCommand::SRC_DIR . "{$nspClean}/Twig/{$ext}.php";
            $ctrData    =<<<CTR
<?php

	namespace {$nsp}\Twig;

	class {$ext} extends \Twig_Extension {


		public function __construct(){
		}

		public function getFilters() {
			return [
				new \Twig_SimpleFilter('filter_name_1', [\$this, 'useFilterNr1'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('filter_name_2', [\$this, 'useFilterNr2'], ['is_safe'=>['html']]),
			];
		}

		public function useFilterNr1(\$str){
			return strtolower(\$str);
		}

		public function useFilterNr2(\$str){
			return strtoupper(\$str);
		}

	}

CTR;

            if(self::saveGeneratedFile($path, $ctrData) ) {
                return  self::colorize("Twig Extension (Filter) ", "GREEN") .
                self::colorize("\"{$ext}\"", "CYAN") .
                self::colorize(" successfully created... ", "GREEN");
            }
        }
        return null;
    }

    public static function createAndConfigureService($helper, InputInterface $input, OutputInterface $output, string $ext, $ask=true)
    {
        $question  = new Question('Enter Bundle-Name: ');
        $nsp = is_bool($ask) && $ask === true ? $helper->ask($input, $output, $question) : $ask;
        if ($nsp) {
            $ymlFile    = PoizCreateCommand::APP_ROOT . "config/services.yaml";

            $nspClean   = preg_replace("#[\\:]#", "/", $nsp);
            if(stristr($nspClean, "App") ) {
                $nspClean = str_replace("App", "", $nspClean);
                $path       = PoizCreateCommand::SRC_DIR . $nspClean .  "Services/{$ext}Service.php";
            }else{
                $path       = PoizCreateCommand::APP_ROOT . $nspClean .  "/Services/{$ext}Service.php";
            }

            $strContent = static::getSavableData($ext, $nsp, $ymlFile, 'app.services.', 'Services');
            self::saveGeneratedFile($ymlFile, $strContent);


            $ctrData    =<<<CTR
<?php

	namespace {$nsp}\Services;

	class {$ext} {

		public function __construct(){
		}

		public function runTask() {
			//todo
		}
	}

CTR;

            if(self::saveGeneratedFile($path, $ctrData) ) {
                return  self::colorize("The Service ", "GREEN") .
                self::colorize("\"{$ext}\"", "CYAN") .
                self::colorize(" was successfully generated... ", "GREEN");
            }
        }
        return null;
    }

    protected static function getSavableData($ext, $nsp, $ymlFile, $key="app.service.", $packageName="Services")
    {
        $content    = explode(PHP_EOL, file_get_contents($ymlFile));
        $token      = self::getDotBasedServiceName(str_ireplace($packageName, "", $nsp));
        $found      = false;

        foreach($content as $line){
            if(strpos($line, "{$key}{$token}:") != false) {
                $found  = true;
                break;
            }
        }
        if(!$found) {
            $t          = "    ";
            $content[]  = "{$t}" . "{$key}{$token}:";
            $content[]  = "{$t}{$t}" . "class: " . "{$nsp}\\{$packageName}\\$ext";
            $content[]  = "{$t}{$t}" . "public: true";
            $content[]  = "{$t}{$t}" . "# tags: [ { name: some_tag_or_event } ]";
            $content[]  = "{$t}{$t}" . "# arguments: ['@some_service_1', '@some_service_2']" . PHP_EOL;
        }
            return implode("\n", $content);
    }

    public static function getDotBasedServiceName($str)
    {
        $fieldName  = trim(preg_replace("#([A-Z])#", "_$1", $str), "_");
        $arrName    = preg_split("#[_\-\s]+#",    strtolower($fieldName));
        $strName    = trim(implode(".", $arrName), ".");
        return $strName;
    }

    public static function getColonBasedServiceName($str)
    {
        $fieldName  = trim(preg_replace("#([A-Z])#", "_$1", $str), "_");
        $arrName    = preg_split("#[_\-\s]+#",    strtolower($fieldName));
        $strName    = trim(implode(":", $arrName), ":");
        return $strName;
    }

    public static function deleteTwigExtension($helper, InputInterface $input, OutputInterface $output, string $name)
    {
        return self::deleteFile($name, "twig");
    }

    public static function deleteTemplate($helper, InputInterface $input, OutputInterface $output, string $name)
    {
        return self::deleteFile($name, "view");
    }

    public static function deleteController($helper, InputInterface $input, OutputInterface $output, string $name)
    {
        return self::deleteFile($name, "controller");
    }

    public static function deleteService($helper, InputInterface $input, OutputInterface $output, string $name)
    {
        return self::deleteServiceFile($name);
    }

    public static function dumpProjectFiles($helper, InputInterface $input, OutputInterface $output, string $name)
    {
        return self::dumpFiles($name);
    }

    public static function deleteServiceFile(string $alias)
    {
        $ymlFile    = PoizCreateCommand::APP_ROOT . "config/services.yml";
        $content    = explode(PHP_EOL, file_get_contents($ymlFile));
        $class      = null;
        $begin      = false;

        foreach ($content as $key=>&$line) {
            if (strstr($line, $alias)) {
                dump($alias);
                unset($content[$key]);
                $begin  = true;
                continue;
            }
            if($begin) {
                if (strstr($line, "class")) {
                    $class = PoizCreateCommand::SRC_DIR . "/" . str_replace("\\", "/", trim(str_replace("class:", "", $line)));
                    $class .= ".php";
                    unset($content[$key]);

                    if (file_exists($class)) {
                        unlink($class);
                    }
                    continue;
                }
                if (preg_match("#^\s{8}#", $line)) {
                    unset($content[$key]);
                }
                else {
                    if (preg_match("#^\s{4}#", $line)) {
                        break;
                    }
                }
            }
        }
        self::saveGeneratedFile($ymlFile, implode("\n", $content));

        return  self::colorize("The Service ", "GREEN") .
        self::colorize("\"{$alias}\"", "CYAN") .
        self::colorize(" was successfully deleted... ", "GREEN");

    }

    public static function deleteFile(string $name, string $type)
    {
        $fs             = new FolderBrowser(['flatReturn'=>true]);
        $msgKey         = "";
        $filePattern    = "";
        switch($type){
        case "controller":
            FolderBrowser::setRootDir(PoizDeleteCommand::SRC_DIR."Controller");
            $msgKey         = "Controller";
            $filePattern    = "Controller(.+)?\.php";
            break;
        case "service":
            FolderBrowser::setRootDir(PoizDeleteCommand::SRC_DIR."Services");
            $msgKey         = "Service";
            $filePattern    = "\.php$"; // /(Service)s?
            break;
        case "commands":
            FolderBrowser::setRootDir(PoizDeleteCommand::SRC_DIR."Commands");
            $msgKey         = "Command";
            $filePattern    = "\.php$"; // /(Command)s?
            break;
        case "view":
        case "twig":
        case "template":
            FolderBrowser::setRootDir(PoizDeleteCommand::APP_ROOT."templates");
            $msgKey         = "View Template";
            $filePattern    = "\.twig|\.html.twig";
            break;
        case "extension":
        case "twig_extension":
            FolderBrowser::setRootDir(PoizDeleteCommand::APP_ROOT . "Extensions");
            $msgKey         = "Twig Extension";
            $filePattern    = "\.php$";
            break;
        }
        $files          = $fs->recursivelyExtractFilesWithPattern($filePattern);
        $unlinked       = false;
        foreach($files as $file){
            if(strstr($file, $name)) {
                $unlinked = unlink($file);
                break;
            }
        }
        if ($unlinked) {
            return  self::colorize("The {$msgKey} ", "GREEN") .
            self::colorize("\"{$name}\"", "CYAN") .
            self::colorize(" was successfully deleted... ", "GREEN");
        }
        return null;
    }

    public static function dumpFiles(string $type)
    {
        $fs             = new FolderBrowser(['flatReturn'=>true]);
        $msgKey         = "";
        $filePattern    = "";
        switch($type){
        case "controller":
        case "controllers":
            FolderBrowser::setRootDir(PoizDeleteCommand::SRC_DIR);
            $msgKey         = "Controllers";
            $filePattern    = "Controller\.php";
            break;
        case "view":
        case "views":
        case "template":
        case "templates":
            FolderBrowser::setRootDir(PoizDeleteCommand::APP_ROOT."templates");
            $msgKey         = "Twig View-Templates";
            $filePattern    = "html\.twig";
            break;
        case "twig":
        case "twigs":
        case "twig_extension":
        case "twig_extensions":
            FolderBrowser::setRootDir(PoizDeleteCommand::SRC_DIR);
            $msgKey         = "Twig Extensions";
            $filePattern    = "\/Twig\/.*\.php";
            break;
        case "service":
        case "services":
            FolderBrowser::setRootDir(PoizDeleteCommand::SRC_DIR);
            $msgKey         = "Services";
            $filePattern    = "\/(Service)s?\/.*\.php";
            break;
        }
        $files          = $fs->recursivelyExtractFilesWithPattern($filePattern);
        if($files) {
            $filesArr       = array_map(
                function ($fl) {
                    return basename($fl);
                }, $files
            );
            sort($filesArr);
            $msg    =  self::colorize("List of {$msgKey} ", "GREEN") . PHP_EOL;
            foreach($filesArr as $i=>$file){
                  $msg .= self::colorize(($i+1).".", "RED") . "\t" .  self::colorize("{$file}", "CYAN") . PHP_EOL;
            }
            return $msg;
        }
        return self::colorize("No {$msgKey} found...", "RED");

    }

    public static function colorize($text, $status="BROWN")
    {
        $out = "";
        switch($status) {
        case "BLUE":
            $out = "[34m"; //BLUE TEXT
            break;
        case "RED":
            $out = "[31m"; //RED TEXT
            break;
        case "GREEN":
            $out = "[32m"; //GREEN TEXT
            break;
        case "CYAN":
            $out = "[36m"; //CYAN TEXT
            break;
        case "BLACK":
            $out = "[30m"; //BLACK TEXT
            break;
        case "BROWN":
            $out = "[33m"; //BROWN TEXT
            break;
        case "PURPLE":
            $out = "[35m"; //PURPLE TEXT
            break;
        case "LIGHT_GRAY":
            $out = "[37m"; //LIGHT_GRAY TEXT
            break;
        case "LIGHT_GREEN":
            $out = "[32m"; //LIGHT_GREEN TEXT
            break;
        case "LIGHT_RED":
            $out = "[31m"; //BLUE TEXT
            break;
        case "SUCCESS":
            $out = "[42m"; //Green background
            break;
        case "FAILURE":
            $out = "[41m"; //Red background
            break;
        case "WARNING":
            $out = "[43m"; //Yellow background
            break;
        case "NOTE":
            $out = "[44m"; //Blue background
            break;
        default:
            throw new \Exception("Invalid status: " . $status);
        }
        return chr(27) . "$out" . "$text" . chr(27) . "[0m";
    }

    public static function saveGeneratedFile($path, $ctrData)
    {
        $DIR   = pathinfo($path, PATHINFO_DIRNAME);
        if(!file_exists($DIR)) {
            mkdir($DIR, 0777, true);
        }
        return file_put_contents($path, $ctrData);
    }

    protected static function buildStyleFormats(OutputInterface $output)
    {
        // comment=>Yellow, info=>Green, question=>Black on Cyan  //$output->writeln('<fg=green>foo</>');
        $style  = new OutputFormatterStyle('red', 'yellow', array('bold')); // , 'blink'
        $blue   = new OutputFormatterStyle('red', null, array('bold')); // , 'blink'
        $red    = new OutputFormatterStyle('red', null, array());
        $cyan   = new OutputFormatterStyle('cyan', null, array());
        $output->getFormatter()->setStyle('fire', $style);
        $output->getFormatter()->setStyle('bull', $blue);
        $output->getFormatter()->setStyle('red', $red);
        $output->getFormatter()->setStyle('cool', $cyan);
    }

    public static function getBundleStub($bundleName, $nsp)
    {
        return <<<KSTB
<?php


namespace {$nsp};

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class {$bundleName} extends Bundle
{
}

KSTB;
    }

    public static function getKernelStub( $nameSpace)
    {
        return <<<KSTB
<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {$nameSpace};

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function getCacheDir()
    {
        return \$this->getProjectDir().'/var/cache/'.\$this->environment;
    }

    public function getLogDir()
    {
        return \$this->getProjectDir().'/var/log';
    }

    public function registerBundles()
    {
        \$contents = include \$this->getProjectDir().'/config/bundles.php';
        foreach (\$contents as \$class => \$envs) {
            if (isset(\$envs['all']) || isset(\$envs[\$this->environment])) {
                yield new \$class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder \$container, LoaderInterface \$loader)
    {
        \$container->setParameter('container.dumper.inline_class_loader', true);
        \$confDir = \$this->getProjectDir().'/config';
        \$loader->load(\$confDir.'/packages/*'.self::CONFIG_EXTS, 'glob');
        if (is_dir(\$confDir.'/packages/'.\$this->environment)) {
            \$loader->load(\$confDir.'/packages/'.\$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        }
        \$loader->load(\$confDir.'/services'.self::CONFIG_EXTS, 'glob');
        \$loader->load(\$confDir.'/services_'.\$this->environment.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder \$routes)
    {
        \$confDir = \$this->getProjectDir().'/config';
        if (is_dir(\$confDir.'/routes/')) {
            \$routes->import(\$confDir.'/routes/*'.self::CONFIG_EXTS, '/', 'glob');
        }
        if (is_dir(\$confDir.'/routes/'.\$this->environment)) {
            \$routes->import(\$confDir.'/routes/'.\$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        }
        \$routes->import(\$confDir.'/routes'.self::CONFIG_EXTS, '/', 'glob');
    }
}

KSTB;

    }

    public static function getAnnotationRoutingActivator($key, $bundle){
        return <<<ACT
$key:
  resource: "{$bundle}"
  type:     annotation
ACT;

    }
}