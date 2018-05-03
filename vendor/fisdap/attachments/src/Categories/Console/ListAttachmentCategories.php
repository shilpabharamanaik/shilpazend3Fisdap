<?php namespace Fisdap\Attachments\Categories\Console;

use Fisdap\Attachments\Categories\Queries\FindsAttachmentCategories;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Console command for listing attachment categories
 *
 * @package Fisdap\Attachments\Categories\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ListAttachmentCategories extends Command
{
    /**
     * @inheritdoc
     */
    protected $name = 'attachments:categories:list';

    /**
     * @inheritdoc
     */
    protected $description = 'List categories for an attachment type.';

    /**
     * @var FindsAttachmentCategories
     */
    private $finder;


    /**
     * @param FindsAttachmentCategories $finder
     */
    public function __construct(FindsAttachmentCategories $finder)
    {
        parent::__construct();

        $this->finder = $finder;
    }


    /**
     * @inheritdoc
     */
    public function fire()
    {
        $attachmentType = $this->argument('attachmentType');

        $categories = $this->finder->findAll($attachmentType);

        // delete the 'type' column
        array_walk($categories, function (&$item) {
            unset($item['type']);
        });

        $this->comment(ucfirst($attachmentType) . " Attachment Categories");
        $this->table(['id', 'name'], $categories);
    }


    /**
     * @inheritdoc
     */
    protected function getArguments()
    {
        return [
            ['attachmentType', InputArgument::REQUIRED, 'The attachment type'],
        ];
    }
}
