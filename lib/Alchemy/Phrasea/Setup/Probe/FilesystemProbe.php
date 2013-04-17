<?php

namespace Alchemy\Phrasea\Setup\Probe;

use Alchemy\Phrasea\Application;
use Alchemy\Phrasea\Setup\Requirements\FilesystemRequirements;

class FilesystemProbe extends FilesystemRequirements implements ProbeInterface
{
    public function __construct(\registryInterface $registry)
    {
        parent::__construct();

        $baseDir = realpath(__DIR__ . '/../../../../../');
        
        $paths = array(
            $baseDir . '/config/config.yml',
            $baseDir . '/config/connexions.yml',
            $baseDir . '/config/services.yml',
            $baseDir . '/config/binaries.yml',
        );

        foreach ($paths as $path) {
            $this->addRecommendation(
                "00" === substr(sprintf('%o', fileperms($path)), -2),
                "$path should not be readable or writeable for other users, current mode is (".substr(sprintf('%o', fileperms($path)), -4).")",
                "Change the permissions of the \"<strong>$path</strong>\" file to 0600"
            );
        }

        $path = array();

        if ($registry->is_set('GV_base_datapath_noweb')) {
            $paths[] = $registry->get('GV_base_datapath_noweb');
        }

        foreach ($paths as $path) {
            $this->addRequirement(
                is_writable($path),
                "$path directory must be writable",
                "Change the permissions of the \"<strong>$path</strong>\" directory so that the web server can write into it."
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return FilesystemProbe
     */
    public static function create(Application $app)
    {
        return new static($app['phraseanet.registry']);
    }
}
