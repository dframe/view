<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\View;

use Dframe\Config\Config;
use Dframe\View\Exceptions\ViewException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Twig View.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class TwigView implements ViewInterface
{
    /**
     * @var \Twig\Environment
     */
    public $twig;

    /**
     * @var array
     */
    public $assign;

    /**
     * TwigView constructor.
     */
    public function __construct()
    {
        $twigConfig = Config::load('view/twig');
        $loader = new FilesystemLoader($twigConfig->get('setTemplateDir'));
        $twig = new Environment(
            $loader,
            [
                'cache' => $twigConfig->get('setCompileDir'),
            ]
        );
        $this->twig = $twig;
    }

    /**
     * Set the var to the template.
     *
     * @param string $name
     * @param string $value
     *
     * @return mixed
     */
    public function assign($name, $value)
    {
        try {
            if (isset($this->assign[$name])) {
                throw new ViewException('You can\'t assign "' . $name . '" in Twig');
            }

            $assign = $this->assign[$name] = $value;
        } catch (ViewException $e) {
            die(
                $e->getMessage() . '<br />
         File: ' . $e->getFile() . '<br />
         Code line: ' . $e->getLine() . '<br />
         Trace: ' . $e->getTraceAsString()
            );
        }

        return $assign;
    }

    /**
     * Return code.
     *
     * @param string $name Filename
     * @param string $path Alternative Path
     *
     * @return void
     */
    public function fetch($name, $path = null)
    {
        return $this->renderInclude($name, $path);
    }

    /**
     * Transfers the code to the Smarty template.
     *
     * @param string $name
     * @param string $path
     *
     * @return mixed
     */
    public function renderInclude($name, $path = null)
    {
        $twigConfig = Config::load('twig');
        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
        $path = $twigConfig->get('setTemplateDir') . DIRECTORY_SEPARATOR
            . $folder . $name . $twigConfig->get('fileExtension', '.twig');

        try {
            if (!is_file($path)) {
                throw new ViewException('Can not open template ' . $name . ' in: ' . $path);
            }

            $renderInclude = $this->twig->render($name, $this->assign);
        } catch (ViewException $e) {
            die(
                $e->getMessage() . '<br />
              File: ' . $e->getFile() . '<br />
              Code line: ' . $e->getLine() . '<br />
              Trace: ' . $e->getTraceAsString()
            );
        }

        return $renderInclude;
    }
}
