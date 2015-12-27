<?php

namespace THCFrame\Core;

/**
 * Description of BagInterface
 *
 * @author Tomy
 */
interface BagInterface
{

    public function get($key, $default = null);

    public function set($key, $value);

    public function erase($key);

    public function clear();

    public function hashKey($key);
}
