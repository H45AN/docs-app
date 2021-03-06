<?php

namespace Docs\Cli\Tasks;

use function file_put_contents;

use function implode;
use Phalcon\CLI\Task;

use function Docs\Functions\app_path;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use SplFileInfo;

/**
 * GenerateSitemapTask
 */
class GenerateSitemapTask extends Task
{
    public function mainAction()
    {
        $output   = app_path('public/sitemap.xml');

        $elements    = [];
        $path        = app_path('docs/');
        $dirIterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $iterator    = new RecursiveIteratorIterator(
            $dirIterator,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ('md' === $file->getExtension() || 'html' === $file->getExtension()) {
                $docsPath = $file->getPath();
                $temp     = str_replace($path, '', $file->getPath());
                $parts    = explode('/', $temp);
                if (count($parts) > 1) {
                    /**
                     * Check if we have 2 or 3 elements; 3 is the API
                     */
                    $first    = $parts[0];
                    $parts[0] = $parts[1];
                    $parts[1] = $first;
                    $docsPath = implode('/', $parts);
                }

                $fullFile   = $docsPath . '/' . $file->getFilename();
                $elements[] = str_replace(
                    [
                        app_path('docs/'),
                        '.md',
                        '.html',
                    ],
                    [
                        '',
                        '',
                        '',
                    ],
                    $fullFile
                );
            }
        }

        sort($elements);

        $contents = $this->viewSimple->render(
            'index/sitemap',
            [
                'elements' => $elements,
            ]
        );

        file_put_contents($output, $contents);
    }
}
