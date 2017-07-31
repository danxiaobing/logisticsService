<?php
/*/**
 * 视图引擎定义
 *
 * @author  James
 * @date    2011-08-01 15:00
 * @version $Id$
 */
use Jenssegers\Blade\Blade;
// 视图引擎实现类
class View implements Yaf_View_Interface
{
    /**
     * template object
     * @var template
     */
    public $_T;
    private $header = null;
    private $tail = null;

    /**
     * Constructor
     *
     * @param   string      $tmplPath
     * @param   array       $extraParams
     * @return  void
     */
    public function __construct($viewPath = null, $cachePath= null)
    {
        $this->_T = new Blade($viewPath,$cachePath);

        $this->_T->addExtension('html', 'php');

        $this->_T->compiler()->directive('trans', function ($expression) {

     //       $key = substr($expression,2,strlen($expression)-4);

            return "<?php echo Gear::trans".$expression."; ?>";

        });

    }

    //一般用来载入css等头部
    public  function setHeader($headerPath){
        $this->header=$headerPath;
    }

    //一般用来载入js等尾部
    public  function setTail($tailPath){
        $this->tail=$tailPath;
    }
    /**
     * Assign variables to the template
     *
     * Allows setting a specific key to the specified value, OR passing
     * an array of key => value pairs to set en masse.
     *
     * @param   string|array    $spec   The assignment strategy to use
     * @param   mixed           $value  (Optional)
     * @return  void
     */
    public function assign($name, $value = NULL)
    {
        // echo $this->_T->make($path,$data);
    }


    public function render($view_file, $tpl_vars = null)
    {

    }

    /**
     * Processes a template and display the output.
     *
     * @param   string          $view_file
     * @param   array           $tpl_vars  (Optional)
     * @return  string
     */
    public function display($view_file, $tpl_vars = null)
    {

    }

    /**
     * setting the path of template.
     *
     * @param   string          $view_directory
     * @return  boolean
     */
    public function setScriptPath($view_directory)
    {

    }

    /**
     * return the path of template.
     *
     * @param   void
     * @return  string
     */
    public function getScriptPath()
    {
        return '';
    }


    public function make($path, $data)
    {
        $locale = Yaf_Registry::get("locale");
        if($this->header){
            if($this->_T->exists($locale.'.'.$this->header))
                echo $this->_T->make($locale.'.'.$this->header,$data);
            else
                echo $this->_T->make($this->header,$data);
        }

        if($this->_T->exists($locale.'.'.$path))
            echo $this->_T->make($locale.'.'.$path,$data);
        else
            echo $this->_T->make($path,$data);

        if($this->tail){
            if($this->_T->exists($locale.'.'.$this->tail))
                echo $this->_T->make($locale.'.'.$this->tail,$data);
            else
                echo $this->_T->make($this->tail,$data);
        }

    }

    

}

