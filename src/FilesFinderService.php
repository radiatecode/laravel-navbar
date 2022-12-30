<?php

namespace RadiateCode\LaravelNavbar;

use Illuminate\Support\Str;

class FilesFinderService
{
    public static function make()
    {
        return new self();
    }

    /**
     * Find files
     *
     * @param string $path // for unix path should contains forward slash / instead of backward slash \
     * @param string $ext 
     * @return void
     */
    public function findFiles($path, string $ext = '\*')
    {
        $files = [];

        $basePath = base_path();

        $direactory = Str::contains($path, $basePath) ? $path : base_path($path);

        $results = glob($direactory . $ext, GLOB_BRACE);

        // for unix system glob ext should be forward slash /*
        if (!in_array($ext, ['\*', '*', '/*'])) {
            return $results;
        }

        foreach ($results as $item) {
            if (is_dir($item)) {
                $files = array_merge($files, $this->findFiles($item, $ext));
            } elseif (is_file($item)) {
                $files[] = $item;
            }
        }

        return $files;
    }

    public function findClasses($path): array
    {
        $globePattern = strtolower(PHP_OS) == 'windows' ?  '\*' : '/*'; // php_uname('s') also shows os name

        $files = $this->findFiles($path, $globePattern);

        $basePath = base_path();

        $classList = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                //$fileName = pathinfo($file, PATHINFO_FILENAME);

                $class = str_replace([$basePath . '/', '.php'], '', $file);
                
                if(strtolower(PHP_OS) != 'windows' ){
                    $class = str_replace('/', '\\', $class);
                }

                $classList[] = ucfirst($class);
            }
        }

        return $classList;
    }
}
