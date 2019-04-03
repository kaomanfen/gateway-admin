<?php
/**
 *
 * @package   udc
 * @filename  Http.php
 * @author    renyineng <renyineng@enhance.cn>
 * @license   http://www.kaomanfen.com/ kaomanfen license
 * @datetime  17/2/9 下午3:30
 */
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Http extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'http';
    }
}