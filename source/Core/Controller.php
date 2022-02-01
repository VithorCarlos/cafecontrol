<?php

namespace Source\Core;

use Source\Support\Seo;

/**
 * Class Controller
 *
 * @package Source\Core
 */
class Controller
{
    /**
     * @var View
     */
    protected $view;
    /**
     * @var Seo
     */
    protected $seo;

    //caminho de visoes para o controlador. cd controlador tem seu template

    /**
     * Controller constructor.
     *
     * @param  string|null  $pathToViews
     */
    public function __construct(string $pathToViews = null)
    {
        $this->view = new View($pathToViews);
        $this->seo = new Seo();
    }
}