<?php namespace Fisdap\BuildMetadata;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


/**
 * Class BuildMetadataMakeCommand
 *
 * @package Fisdap\BuildMetadata
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class BuildMetadataMakeCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected $name = 'build:metadata:make';

    /**
     * @inheritdoc
     */
    protected $description = 'Generates a JSON file containing build metadata';


    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $build = new BuildMetadata();

        $build->projectName = $this->option('project-name');
        $build->projectVersion = $this->option('project-version');
        $build->vcsBranch = $this->option('vcs-branch');
        $build->vcsRevision = $this->option('vcs-revision');
        $build->buildNumber = $this->option('build-number');
        $build->buildTimestamp = $this->option('build-timestamp');

        $build->save($this->argument('file-path'));
    }


    /**
     * @inheritdoc
     */
    protected function getArguments()
    {
        // array($name, $mode, $description, $defaultValue)
        return [
            ['file-path', InputArgument::OPTIONAL, 'Where to save build metadata file', null],
        ];
    }


    /**
     * @inheritdoc
     */
    protected function getOptions()
    {
        return [
            // array($name, $shortcut, $mode, $description, $defaultValue)
            ['project-name', 'a', InputOption::VALUE_REQUIRED, 'The project name'],
            ['project-version', 'e', InputOption::VALUE_REQUIRED, 'The project version'],
            ['vcs-branch', 'b', InputOption::VALUE_REQUIRED, 'The VCS branch name'],
            ['vcs-revision', 'r', InputOption::VALUE_REQUIRED, 'The VCS revision number (changeset)'],
            ['build-number', 'u', InputOption::VALUE_REQUIRED, 'The build number'],
            ['build-timestamp', 't', InputOption::VALUE_REQUIRED, 'The build timestamp']
        ];
    }

}
