<?php
	// bootstrap.php

	namespace App\CodePool\config;

	use Doctrine\ORM\Tools\Setup;
	use Doctrine\ORM\EntityManager;
	use Doctrine\Common\Cache\ArrayCache;
	use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
	use Doctrine\Common\Annotations\AnnotationReader;

	class bootstrap{}

	//CREATE A POINTER TO ENTITIES PATH - POINTER MUST BE AN ARRAY CONTAINING FULL PATH NAMES
	$paths 		= array( realpath(ENTITY_ROOT) );      //__DIR__ . "/../CodePool/DataObjects"


	//CREATE A SIMPLE "DEFAULT" DOCTRINE ORM CONFIGURATION FOR ANNOTATIONS
	$isDevMode 	= true;

	// DATABASE CONFIGURATION PARAMETERS:
	// SQL LITE CONFIGURATION
	$sqLiteConn = array(
		'driver'    => 'pdo_sqlite',
		'path'      => DB_FILE     ,
	);



	$cache      = new ArrayCache();
	$reader     = new AnnotationReader();
	$driver     = new AnnotationDriver($reader, $paths);
	$DConfig    = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);


	$DConfig->setMetadataCacheImpl( $cache );
	$DConfig->setQueryCacheImpl( $cache );
	$DConfig->setMetadataDriverImpl( $driver );



	if(!class_exists("E_MAN")){
		class E_MAN{
			/**
			 * @var EntityManager
			 */
			protected static $entityManager;

			/**
			 * @return mixed
			 */
			public static function getEntityManager(){
				return self::$entityManager;
			}

			/**
			 * @param EntityManager $entityManager
			 */
			public static function setEntityManager(EntityManager $entityManager){
				self::$entityManager = $entityManager;
			}

		}
	}


	// INSTANTIATE THE ENTITY MANAGER
	$entityManager = EntityManager::create($sqLiteConn, $DConfig);      //<== SQLITE DB CONNECTION


	// - ADD SUPPORT FOR MYSQL ENUM-TYPES....
	$platform = $entityManager->getConnection()->getDatabasePlatform();
	$platform->registerDoctrineTypeMapping('enum', 'string');

	// STATICALLY SET THE ENTITY MANAGER SO IT CAN BE ACCESSED BY CLASSES NEEDING IT....
	E_MAN::setEntityManager($entityManager);
