<?php
/**
 * Created by PhpStorm.
 * User: poiz
 * Date: 28.06.18
 * Time: 13:37
 */

namespace App\Extensions\Twig\Helper;

use App\CodePool\Base\Poiz\Helpers\WorkHorse;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\Common\Persistence\ObjectManager;
use Twig\TwigFunction;

class TwigHelper extends AbstractExtension
{

    protected $doctrine;
    protected $twigEnvironment;

    public function __construct(ObjectManager $doctrine, \Twig_Environment $twigEnvironment)
    {
        $this->doctrine = $doctrine;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     *
     * @inheritdoc
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('sbb', array($this, 'sbb')),
        );
    }

    /**
     *
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('render_search_item', array($this, 'renderSearchItem'), ['is_safe'=>['html']]),
            new TwigFunction('render_time_strip', array($this, 'renderTimeStrip'), ['is_safe'=>['html']]),
        );
    }

    /**
     *
     * @param \App\Entity\Users $data
     *
     * @return string
     */
    public function renderTimeStrip($data)
    {
        WorkHorse::setEntityManager($this->doctrine);
        $numbering = null;
        return  WorkHorse::build_employee_strip($data->getFullName(), $data->getId(), $data->getAvatar(), $numbering);
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'TwigExtensions';
    }


    /**
     *
     * @param \App\Entity\Users $data
     *
     * @return string
     */
    public function renderSearchItem($data)
    {
        if(!$data) { return "";
        }
        $strUserSearchPane  = "";
        $userID             = $data->getId();
        $userFullName       = $data->getFullName();
        $userThumbPix       = $this->fetch_thumb($data->getAvatar());
        $userStripID        = "#pz-user-id-" . $userID;
        $userStripClass     = ".pz-user-unique.pz-user-id-" . $userID;
        $strUserSearchPane .= "<div class='pz-scrollable-search-pod'
                                    id='pz-scrollable-search-pod-{$userID}'
                                    data-uid='{$userID}'
                                    data-strip-id='{$userStripID}'
                                    data-strip-class='{$userStripClass}'
                                    >
                                    <img    src='{$userThumbPix}'
                                            alt='{$userFullName}'
                                            class='pz-scroll-search-img'
                                            id='pz-scroll-search-img-{$userID}'
                                            data-uid='{$userID}'
                                            data-strip-id='{$userStripID}'
                                            data-strip-class='{$userStripClass}'
                                    />
                                    {$userFullName}
                                    <span class=\"fa fa-hand-pointer-o pz-search-click-pointer\" aria-hidden=\"true\"></span>
                                </div>" . PHP_EOL;
        return $strUserSearchPane;
    }

    /**
     * SIMPLY FILTERS IMAGE URL AND CONVERTS IT TO
     * THE URL THAT MATCHES THE PATH TO THE IMAGE'S THUMBNAIL
     *
     * @param  string $pix_url
     * @return null|string
     */
    protected function fetch_thumb($pix_url)
    {
        if(!$pix_url) {
            return null;
        }
        $parts      = preg_split("#\/|\\\#", $pix_url);
        $thumb_name = array_pop($parts);
        $thumb_path = implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR . "thumbs" . DIRECTORY_SEPARATOR . $thumb_name;
        return $thumb_path;

    }
}