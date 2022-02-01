<?php
/*
 * vai diminuir as requisições do site.
 *  - fazer a minificação dos arquivos.
 */

//Só vai  executar a minificação qd estiver no ambiente de teste
if (strpos(url(), "localhost"))
{
    //qd se tratar de ambinete compartilhado, será feito manualmente e do tema sera automatizado
    /*
     * CSS
     */
    $minCSS = new MatthiasMullie\Minify\CSS();
    //incluir arquivo. (Sempre chame da ordem correta)
    $minCSS->add(__DIR__ . "/../../shared/styles/styles.css");
    $minCSS->add(__DIR__ . "/../../shared/styles/boot.css");

    //theme CSS (automatizado)

    //abrir a pasta do tema que vai conter os arquivos
    $cssDir = scandir(__DIR__ . "/../../themes/".CONF_VIEW_THEME."/assets/css");
    foreach ($cssDir as $css) {
        $cssFile = __DIR__ . "/../../themes/".CONF_VIEW_THEME."/assets/css/{$css}";
        if (is_file($cssFile) && pathinfo($cssFile, PATHINFO_EXTENSION) == "css"){
            $minCSS->add($cssFile);
        }
    }

    /**
     * Minify css
     */
    //aonde quero o arquivo minificado e o nome
    $minCSS->minify(__DIR__ . "/../../themes/".CONF_VIEW_THEME."/assets/style.css");

    /*
     * JS
     */
    $minJS = new MatthiasMullie\Minify\JS();
    //nessa ordem pq o plugin abaixo precisar está presente para que os outros funcionem
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery.min.js");
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery.form.js");
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery-ui.js");

    $jsDir = scandir(__DIR__ . "/../../themes/".CONF_VIEW_THEME."/assets/js");
    foreach ($jsDir as $js) {
        $jsFile = __DIR__ . "/../../themes/".CONF_VIEW_THEME."/assets/js/{$js}";
        if (is_file($jsFile) && pathinfo($jsFile, PATHINFO_EXTENSION) == "js"){
            $minJS->add($jsFile);
        }
    }

    $minJS->minify(__DIR__ . "/../../themes/".CONF_VIEW_THEME."/assets/scripts.js");
}