<?php
/**
 * 统一的异常抛出接口
 *
 * @version $Id$
 * @access  public
 */
class ErrorController extends yaf_controller_abstract
{
	/**
	 * 异常处理函数
	 *
	 * @param  object  $exception
	 * @return string  html
	 */
	public function ErrorAction($exception)
    {
        echo $exception->getMessage();
 

	}

}
