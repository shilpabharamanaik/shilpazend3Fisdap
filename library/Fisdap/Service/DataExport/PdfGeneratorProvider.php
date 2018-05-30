<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 12/30/14
 * Time: 2:55 PM
 */
namespace Fisdap\Service\DataExport;

use Illuminate\Support\ServiceProvider;

class PdfGeneratorProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('Fisdap\Service\DataExport\PdfGenerator', 'Fisdap\Service\DataExport\WkhtmlPdfGenerator');
    }
}
