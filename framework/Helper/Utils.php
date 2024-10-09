<?php
/**
 * Get the base path of the project.
 *
 * @param string $path
 * @return string
 */
function basePath(string $path = ''): string
{
    return dirname(__DIR__, 2) . $path;
}