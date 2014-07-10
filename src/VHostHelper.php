<?php

namespace Mkvhost;

/**
 * Class VHostHelper
 * @package Mkvhost
 */
class VHostHelper
{
    /**
     * @param string $path
     * @param string $content
     * @return int
     */
    public function write($path, $content)
    {
        if (is_writable("$path.conf")) {
            return file_put_contents("$path.conf", $content);
        }

        return false;
    }
}