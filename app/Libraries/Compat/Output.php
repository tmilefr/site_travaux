<?php

namespace App\Libraries\Compat;

/**
 * Sous-ensemble de CI_Output.
 */
class Output
{
    public function enable_profiler($enable = true)
    {
        // Le profiler CI3 est remplace par la Debug Toolbar de CI4 (Config\Toolbar).
        return $this;
    }

    public function set_status_header($code = 200, $text = '')
    {
        service('response')->setStatusCode((int) $code);
        return $this;
    }

    public function set_content_type($mime_type, $charset = null)
    {
        service('response')->setContentType($mime_type, $charset ?: 'UTF-8');
        return $this;
    }
}
