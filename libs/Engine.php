<?php
namespace NanoFrameworkPlugins\ViewEngine\Php;
use NanoFramework\Kernel;
use NanoFramework\Utilities;

/**
* Php
*
* @package NanoFrameworkPlugins\View
* @author Stéphane BRUN <stephane.brun@sbnet.fr>
* @version 0.0.1
* @version 0.0.2 Seringue support
*/
class Engine extends Kernel\Event\Observable implements Kernel\View\iViewEngine
{
    protected $layout;
    protected $view_name;

    private $request;
    private $response;
    private $flash;
    private $route;
    private $security;
    protected $seringue;

    public function __construct($layout, $seringue)
    {
        $this->layout = $this->set_layout($layout);
        $this->request = Kernel\Request::get_instance();
        $this->response = Kernel\Response::get_instance();
        $this->flash = Utilities\Flash::get_instance();
        $this->route = Kernel\Route::get_instance();
        $this->security = Kernel\Security::get_instance();

        $this->seringue = $seringue;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }

    public function __unset($name)
    {
        unset($this->$name);
    }

    /**
    * Set the layout
    * @param string $layout The layout to use
    * @todo check if the layout file exists
    */
    public function set_layout($layout)
    {
        $this->layout = $layout;
        return true;
    }

    /**
    * Set the view name
    *
    * @params string $view_name
    */
    public function set_view_name($view_name)
    {
        $this->view_name = $view_name;
        return true;
    }
    /**
    * Render the view
    *
    * @param string $controller
    * @param string $action
    * @param bool $no_layout
    * @return string the content for the corresponding view
    */
    public function _render($controller, $action, $no_layout=false)
    {

        if(isset($this->view_name))
        {
            $view_file = $this->get_controller_view_path($controller).'/'.$this->view_name.'.php';
        }
        else
        {
            $view_file = $this->get_controller_view_path($controller).'/'.$action.'.php';
        }

        // No layout for ajax calls and response of types different to html
        if($this->request->is_ajax() || ($this->response->get_current_type()!=='html'))
        {
            $this->layout = null;
        }

        if(is_file($view_file))
        {

            $layout_file = $GLOBALS['env']['DIR_VIEWS'].'layouts/'.$this->layout.'.php';

            // We start buffering the output
            ob_start();

            // Render the content
            include($view_file);
            $content_for_layout = ob_get_contents();
            ob_clean();

            // Then put it in the layout if any
            if(is_file($layout_file) and $no_layout==false)
            {
                include($layout_file);
                $full_content = ob_get_contents();
            }
            else
            {
                $full_content = $content_for_layout;
            }

            // The buffering is finished
            ob_end_clean();
        }
        else
        {
            $full_content = null;
        }

        return $full_content;
    }

    private function get_controller_view_path($controller)
    {
        $parts = explode("\\", $controller);
        $path = DIR_APP.$parts[1].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR.$parts[3];
        return $path;
    }
}
