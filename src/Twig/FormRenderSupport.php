<?php

	namespace App\Twig;

	use App\Entity\Genus;
	use App\Entity\GenusNote;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\DBAL\Connection;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\PersistentCollection;
    use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

    class FormRenderSupport extends \Twig_Extension {

		/**
		 * @var EntityManagerInterface
		 */
		private $em;

        /**
         * @var MarkdownParserInterface
         */
        private $mdp;

        public function __construct(EntityManagerInterface $em, MarkdownParserInterface $mdp){
			$this->em = $em;
            $this->mdp = $mdp;
        }


	    /**
	     * @return array|\Twig_Function[]
	     */
	    public function getFunctions() {
		    return [
			    new \Twig_SimpleFunction('get_random_artist_pix', [$this, 'getRandomArtistPix'], ['is_safe'=>['html']]),
			    new \Twig_SimpleFunction('get_artist_by_id', [$this, 'getArtistByID'], ['is_safe'=>['html']]),
		    ];
	    }

		public function getFilters() {
			return [
				new \Twig_SimpleFilter('filter_name_1', [$this, 'useFilterNr1'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('filter_name_2', [$this, 'useFilterNr2'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('joy', [$this, 'joy'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('loop_and_render_vals', [$this, 'loopAndRender'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('loop_and_render_keys', [$this, 'renderKeys'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('render_genus_notes', [$this, 'renderGenusNotes'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('render_genus_list', [$this, 'renderGenusList'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('render_table_extreme', [$this, 'renderTableExtreme'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('summarize', [$this, 'summarize'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('summarizeNote', [$this, 'summarizeNote'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('sequence_length', [$this, 'sequence_length'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('ordered_by_created_at', [$this, 'orderedByCreatedAt'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('get_page', [$this, 'get_page'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('parse_markdown', [$this, 'parse_markdown'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('ordered_by_max_songs', [$this, 'orderedByMaxSongs'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('ordered_by_artist_with_max_songs', [$this, 'orderedByArtistWithMaxSongs'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('getArtistSongCount', [$this, 'getArtistSongCount'], ['is_safe'=>['html']]),
				new \Twig_SimpleFilter('remove_path_prefix', [$this, 'remove_path_prefix'], ['is_safe'=>['html']]),
			];
		}

		public function useFilterNr1($str){
			return strtolower($str);
		}

		public function useFilterNr2($str){
			return strtoupper($str);
		}

		public function sequence_length($sequence){
			return sizeof($sequence);
		}

		/**
		 * Renders a String 20 Times, encapsulated in Div/H4 Blocks..
		 * @param $str
		 * @return string
		 */
		public function joy($str){
			$res = "";
			for($i=0; $i<=3; $i++){
				$res .= "<div class=''><h4 style='color:rebeccapurple'>" . strtoupper($str) . "</h4></div>\n";
			}
			return $res;
		}

		/**
		 * Renders a String 20 Times, encapsulated in Div/H4 Blocks..
		 * @param $array
		 * @return string
		 */
		public function loopAndRender($array){
			$res = "";
			foreach($array as $str){
				$res .= "<div class=''><h4 style='color:#345257'>" . $str . "</h4></div>\n";
			}
			return $res;
		}

		public function get_page(){
            return basename($_SERVER['REQUEST_URI']);
		}

		/**
         *
		 */
		public function summarize($string, $maxStringLength=200){
            $res        = "";
		    if(strlen($string)>=$maxStringLength){
		        $res    = substr($string, 0, $maxStringLength);
                $res       .= " [...]";
            }
            return $res;
		}
		/**
         *
		 */
		public function parse_markdown($string){
            return $this->mdp->transformMarkdown($string);
		}

		/**
         *
		 */
		public function summarizeNote(GenusNote $genusNote, $uri, $maxStringLength=200){
		    $string     = $genusNote->getNote();
            $res        = "";
		    if(strlen($string)>=$maxStringLength){
		        $res    = substr($string, 0, $maxStringLength);
                $res   .= " <a href='{$uri}' class='follow'> [...]</a>";
            }else{
                $res   .= $string . " <a href='{$uri}' class='follow'> [...]</a>";
            }
            return $res;
		}

		/**
         *
		 */
		public function remove_path_prefix($path){
		    return preg_replace("#^.*public#", "", $path);
		}

		/**
         *
		 */
		public function orderedByCreatedAt($notes){
		    /**@var array $notes */
		   $notes   = ($notes instanceof PersistentCollection) ? $notes->toArray() : $notes;
		    usort($notes, function($a, $b){
		        return $a->getCreatedAt() < $b->getCreatedAt();
            });
		    return $notes;
		}

		/**
         *
		 */
		public function orderedByMaxSongs($artists){
		    /**@var array $artists */
            $artists   = ($artists instanceof PersistentCollection) ? $artists->toArray() : $artists;
		    usort($artists, function($a, $b){
		        $x = ($i1=$a->getSongs())?sizeof($i1):0;
		        $y = ($i2=$b->getSongs())?sizeof($i2):0;
		        return $x <  $y;
            });
		    return $artists;
		}

		/**
         *
		 */
		public function orderedByArtistWithMaxSongs($songs){
		    /**@var array $songs */
            $songs   = ($songs instanceof PersistentCollection) ? $songs->toArray() : $songs;
		    usort($songs, function($a, $b){
		        $x = ($i1=$a->getSongs())?sizeof($i1):0;
		        $y = ($i2=$b->getSongs())?sizeof($i2):0;
		        return $x <  $y;
            });
		    return $songs;
		}

		public function getArtistSongCount($artistID){
            /**@var Connection $conn */
            $conn       = $this->em->getConnection() ;
            /*
            $query      = $this->get_create_get_artist_song_count_by_id_procedure_sql();
            $statement  = $conn->prepare($query);
            $statement->execute();
            */
            $query      = "CALL get_artist_song_count_by_id(:AID)";
            $statement  = $conn->prepare($query);
            $statement->execute(['AID'=>intval($artistID)]);
            $result     = (int)$statement->fetch(\PDO::FETCH_COLUMN);
            return $result;
        }

		public function getRandomArtistPix($artistID){
            /**@var Connection $conn */
            $conn       = $this->em->getConnection() ;

            $query      = "	SELECT ca.`image` FROM `song` AS sg
							LEFT JOIN `cover_art` AS ca 
							ON sg.`cover_art_id`=ca.`id` 
							WHERE sg.`artist_id`=:AID
							LIMIT 1";
            $statement  = $conn->prepare($query);
            $statement->execute(['AID'=>intval($artistID)]);
            $result     = $statement->fetch(\PDO::FETCH_COLUMN);
            return $result;
        }

		public function getArtistByID($artistID){
            return $this->em->getRepository('App:Artist')->find($artistID);
        }

        private function get_create_get_artist_song_count_by_id_procedure_sql(){
            $procedure  =<<<PRC
-- DELIMITER $$
DROP PROCEDURE IF EXISTS `get_artist_song_count_by_id`;
CREATE PROCEDURE `get_artist_song_count_by_id`(
  IN aid INT(11)
)
   BEGIN
  		SELECT COUNT(s.id)
  		FROM `song` AS s
  		LEFT JOIN `artist` AS a
  		ON s.`artist_id` = a.`id`
  		WHERE s.`artist_id` = aid; 
   END;
-- DELIMITER ;
PRC;
            return "";  //$procedure;

        }

		/**
		 * Renders a String 20 Times, encapsulated in Div/H4 Blocks..
		 * @param $array
		 * @return string
		 */
		public function renderKeys($array){
			$res    = "";
			$keys   = array_keys($array);
			foreach($keys as $str){
				$res .= "<div class=''><h4 style='color:#345257'>" . $str . "</h4></div>\n";
			}
			return $res;
		}

		public function renderGenusNotes($genusID){
			/***
			 * @var GenusNote $note
			 * @var Genus $genus
			 */
			$genus  = $this->em->getRepository('App\Entity\Genus')->find($genusID);
			$notes  = $genus->getNotes();
			$res    = "";
			/*
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `genus_id` int(11) DEFAULT NULL,
			  `username` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `user_avatar_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `note` longtext COLLATE utf8_unicode_ci,
			  `created_at` datetime NOT NULL,
			 */
			foreach($notes as $note){
				$res .= "<div class=''>
							<h4 style='color:#345257'>" . $note->getUsername() . "</h4>
							<p style='color:#345257'>" . $note->getUsername() . "</p>
							<div class='well'>" . $note->getNote() . "</p>
						</div>\n";
			}
			return $res;
		}

		public function renderTableExtreme($topBottom12=1){
			$res            = "";
			if($topBottom12 == 1){
				$res       .= "<table class='table table-stripped table-bordered'>";
				$res       .= "<thead>";
				$res       .= "<tr>";
				$res       .= "<th>Name</th>";
				$res       .= "<th>Sub-Family</th>";
				$res       .= "<th>Species Count</th>";
				//$res       .= "<th>Tasks</th>";
				//$res       .= "<tr>";
				//$res       .= "</thead>";
				//$res       .= "<tbody>";

			}else{
				$res       .= "</tbody></table>";
			}
			return $res;
		}

		public function renderGenusList($genusList){
			/***
			 * @var GenusNote $note
			 * @var Genus $genus
			 */
			$res        = "<table class='table table-stripped table-bordered'>";
			$res       .= "<thead>";
			$res       .= "<tr>";
			$res       .= "<th>Name</th>";
			$res       .= "<th>Sub-Family</th>";
			$res       .= "<th>Species Count</th>";
			$res       .= "<th>Tasks</th>";
			$res       .= "<tr>";
			$res       .= "</thead>";
			$res       .= "<tbody>";
			dump($this);
			foreach($genusList as $genus){
				$editURI    = "";
				$deleteURI  = "";
				$res   .=<<<DKK
				<tr class=''>
					<td><h4 style='color:#345257'>{$genus->getName()}</h4></td>
					<td><p style='color:#345257'>{$genus->getSubFamily()}</p></td>
					<td><p style='color:#345257'>{$genus->getSpeciesCount()}</p></td>
					<td>
						<div class=''>
							<a href='{$editURI}'><span class='fa fa-pencil'></span></a>
							<a href='{$deleteURI}'><span class='fa fa-trash'></span></a>
						</div>
					</td>
				</tr>
DKK;
			}
			$res       .= "</tbody></table>";
			return $res;
		}

	}
