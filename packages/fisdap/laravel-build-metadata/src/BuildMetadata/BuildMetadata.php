<?php namespace Fisdap\BuildMetadata;

/**
 * Class BuildMetadata
 *
 * @package Fisdap\BuildMetadata
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class BuildMetadata
{
    const FILENAME = 'build-metadata.json';


    /**
     * @var string
     */
    public $projectName;

    /**
     * @var string
     */
    public $projectVersion;

    /**
     * @var string
     */
    public $vcsBranch;

    /**
     * @var string
     */
    public $vcsRevision;

    /**
     * @var int
     */
    public $buildNumber;

    /**
     * @var string
     */
    public $buildTimestamp;


    public function load()
    {
        /*
         * {
         * "projectName":"idms",
         * "projectVersion":"1.1.0",
         * "vcsBranch":"default",
         * "vcsRevision":"23lkjdlfkj3993493fk",
         * "buildNumber":"134",
         * "buildTimestamp":"1:00"
         * }
         */
        $versionData = json_decode(file_get_contents(base_path() . '/' . self::FILENAME));

        $this->projectName = $versionData->projectName;
        $this->projectVersion = $versionData->projectVersion;
        $this->vcsBranch = $versionData->vcsBranch;
        $this->vcsRevision = $versionData->vcsRevision;
        $this->buildNumber = $versionData->buildNumber;
        $this->buildTimestamp = $versionData->buildTimestamp;
    }


    /**
     * @param string|null $path
     */
    public function save($path = null)
    {
        $this->validate();

        file_put_contents(
            $path ?: getcwd() . '/' . self::FILENAME,
            json_encode(
                [
                    'projectName'    => $this->projectName,
                    'projectVersion' => $this->projectVersion,
                    'vcsBranch'      => $this->vcsBranch,
                    'vcsRevision'    => $this->vcsRevision,
                    'buildNumber'    => $this->buildNumber,
                    'buildTimestamp' => $this->buildTimestamp
                ],
                JSON_PRETTY_PRINT
            )
        );
    }


    public function validate()
    {
        foreach (get_object_vars($this) as $propName => $prop) {
            if (is_null($this->$propName)) {
                throw new \RuntimeException("BuildMetadata::\$${propName} cannot be null");
            }
        }
    }
}
