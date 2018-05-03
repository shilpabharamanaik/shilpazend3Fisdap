<?php namespace Fisdap\Attachments\Categories\Console;

use Doctrine\Common\Inflector\Inflector;
use Fisdap\Attachments\Categories\Commands\Creation\CreateAttachmentCategoriesCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Console command for adding attachment categories
 *
 * @package Fisdap\Attachments\Categories\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AddAttachmentCategories extends Command
{
    /**
     * @inheritdoc
     */
    protected $name = 'attachments:categories:add';

    /**
     * @inheritdoc
     */
    protected $description = 'Add one or more attachment categories.';

    /**
     * @var Dispatcher
     */
    private $dispatcher;


    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();
        $this->dispatcher = $dispatcher;
    }


    /**
     * @inheritdoc
     */
    public function fire()
    {
        $attachmentType = $this->argument('attachmentType');
        $names = $this->argument('names');

        $this->dispatcher->dispatch(new CreateAttachmentCategoriesCommand($attachmentType, $names));

        $categoryInflection = (count($names) > 1) ? Inflector::pluralize('category') : 'category';

        $this->info("Created '$attachmentType' attachment $categoryInflection named " . implode(', ', $names));
    }


    /**
     * @inheritdoc
     */
    protected function getArguments()
    {
        return [
            ['attachmentType', InputArgument::REQUIRED, 'The attachment type'],
            ['names', InputArgument::IS_ARRAY, 'The name of the category']
        ];
    }
}
