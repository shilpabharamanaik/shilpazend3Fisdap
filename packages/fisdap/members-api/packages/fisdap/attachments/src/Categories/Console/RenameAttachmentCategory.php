<?php namespace Fisdap\Attachments\Categories\Console;

use Fisdap\Attachments\Categories\Commands\Modification\RenameAttachmentCategoryCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Console command for renaming an attachment category
 *
 * @package Fisdap\Attachments\Categories\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class RenameAttachmentCategory extends Command
{
    /**
     * @inheritdoc
     */
    protected $name = 'attachments:categories:rename';

    /**
     * @inheritdoc
     */
    protected $description = 'Rename an attachment category.';

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
        $newName = $this->argument('newName');
        $oldName = $this->argument('oldName');

        $this->dispatcher->dispatch(new RenameAttachmentCategoryCommand($newName, $oldName, null, $attachmentType));

        $this->info("Renamed '$attachmentType' attachment category '$oldName' to '$newName'");
    }


    /**
     * @inheritdoc
     */
    protected function getArguments()
    {
        return [
            ['attachmentType', InputArgument::REQUIRED, 'The attachment type'],
            ['oldName', InputArgument::REQUIRED, 'The current name of the category'],
            ['newName', InputArgument::REQUIRED, 'The new name of the category']
        ];
    }
}
