<?php namespace Fisdap\ErrorHandling;

use Closure;
use Fisdap\ErrorHandling\Exceptions\PostMaxSizeExceeded;
use Illuminate\Http\Request;


/**
 * Gracefully handle files that exceed PHP INI 'post_max_size'
 *
 * According to PHP documentation at http://us2.php.net/set-error-handler:
 * If errors occur before the script is executed (e.g. on file uploads)
 * the custom error handler cannot be called since it is not registered at that time.
 *
 * @package Fisdap\ErrorHandling
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class PostMaxSizeMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $maxPostSize = $this->iniGetBytes('post_max_size');

        if ( ! isset($_SERVER['CONTENT_LENGTH'])) {
            return $next($request);
        }
        
        if ($_SERVER['CONTENT_LENGTH'] > $maxPostSize) {
            throw new PostMaxSizeExceeded(
                "Received {$_SERVER['CONTENT_LENGTH']} bytes, but limit is $maxPostSize bytes." .
                " If uploading a file, it has exceeded the maximum allowed file size."
            );
        }

        return $next($request);
    }


    /**
     * @param $val
     *
     * @return int|string
     */
    private function iniGetBytes($val)
    {
        $val = trim(ini_get($val));
        if ($val != '') {
            $last = strtolower(
                $val{strlen($val) - 1}
            );
        } else {
            $last = '';
        }
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $val *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}