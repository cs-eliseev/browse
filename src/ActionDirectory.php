<?php

namespace browse;

use DirectoryIterator;
use Exception;

/**
 * Class ActionDirectory
 *
 * Date: 31.07.2018
 * Time: 20:34
 */

class ActionDirectory
{

    const TYPE_DIR = 'directory';
    const TYPE_FILE = 'file';
    const TYPE_LINK = 'link';

    const ERROR_DIR_NOT_EXIST = 1;
    const ERROR_FILE_MOD_NOT_OCT = 2;

    protected $pathDir = '';

    protected $showLink = false;
    protected $fileMode = false;

    protected $fullScan = false;

    protected $errors = [
        self::ERROR_DIR_NOT_EXIST => 'Directory is not exist',
        self::ERROR_FILE_MOD_NOT_OCT => 'Invalid file mode'
    ];

    public function __construct(string $pathDir = '')
    {
        $this->pathDir = $pathDir;
    }

    /**
     * Show directory
     *
     * @return array
     */
    public function showDir(): array
    {
        return $this->viewDir();
    }

    /**
     * Show directory & sub directory
     *
     * @return array
     */
    public function scanDir(): array
    {
        // enabled full scan
        $this->fullScan = true;

        $res = $this->viewDir();

        // disabled full scan
        $this->fullScan = false;

        return $res;
    }

    /**
     * Recursive function view directory
     *
     * @param string $defaultPath
     * @return array
     */
    protected function viewDir(string $defaultPath = ''): array
    {
        // check directory
        if (!is_dir($this->pathDir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $this->pathDir);
        }

        $iterator = new DirectoryIterator($this->pathDir);

        // set default path start directory
        if (empty($defaultPath)) $defaultPath = $this->pathDir;

        // get relative path for start directory
        $path = str_replace($defaultPath, '', $iterator->getPath());
        $path = $path ? $path . DIRECTORY_SEPARATOR : '';

        $res = [];

        while ($iterator->valid()) {

            $item = $iterator->current();

            // ignore dot
            if (!$item->isDot()) {

                // set attributes item
                $attr = [
                    'name' => $item->getFilename(),
                    'path' => $item->getPath(),
                    'path_name' => $item->getPathname(),
                    'relative_path' => $path,
                    'relative_path_name' => $path . $item->getFilename(),
                ];

                if ($item->isDir()) {

                    $attr['type'] = self::TYPE_DIR;
                    // get sab directory
                    if ($this->fullScan) {

                        $this->pathDir = $item->getPathname();
                        $attr['node'] = $this->viewDir($defaultPath);
                    }

                } elseif($item->isFile()) {

                    $attr['type'] = self::TYPE_FILE;
                    $attr['short_name'] = $item->getBasename('.' . $item->getExtension());
                    $attr['extension'] = $item->getExtension();

                } elseif ($item->isLink()) {

                    $attr['type'] = self::TYPE_LINK;
                    // get sab directory link
                    if ($this->fullScan && $this->showLink) {

                        $this->pathDir = $item->getPathname();
                        $attr['node'] = $this->viewDir($defaultPath);
                    }
                }

                $res[] = $attr;

                unset($attr);
            }

            unset($item);
            $iterator->next();
        }

        $this->pathDir = $defaultPath;

        unset($path);
        unset($defaultPath);
        unset($iterator);

        return $res;
    }

    /**
     * Create new directory to path
     *
     * @param string $newDir
     * @param string $fileMod
     * @return bool
     */
    public function createDir(string $newDir, string $fileMod = '0755'): bool
    {
        // check directory
        if (!is_dir($this->pathDir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $this->pathDir);
        }

        // check file mode
        if (!$this->isOct($fileMod)) $this->throwException(self::ERROR_FILE_MOD_NOT_OCT, 'file mode: ' . $fileMod);

        return mkdir($this->pathDir . DIRECTORY_SEPARATOR . $newDir, $fileMod, true);
    }

    /**
     * Clear directory
     */
    public function clearDir(): void
    {
        // check directory
        if (!is_dir($this->pathDir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $this->pathDir);
        }

        // set default path start directory
        $defaultPath = $this->pathDir;

        $iterator = new DirectoryIterator($this->pathDir);

        while ($iterator->valid()) {

            $item = $iterator->current();

            // ignore dot
            if (!$item->isDot()) {

                if ($item->isDir()) {

                    $this->pathDir = $item->getPathname();
                    $this->clearDir();
                    rmdir($item->getPathname());

                } else {

                    unlink($item->getPathname());

                }
            }

            unset($item);
            $iterator->next();
        }

        $this->pathDir = $defaultPath;

        unset($defaultPath);
        unset($iterator);
    }

    /**
     * Delete files to directory
     */
    public function deleteFilesToDir(): void
    {
        // check directory
        if (!is_dir($this->pathDir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $this->pathDir);
        }

        $iterator = new DirectoryIterator($this->pathDir);

        // set default path start directory
        $defaultPath = $this->pathDir;

        while ($iterator->valid()) {

            $item = $iterator->current();

            // ignore dot
            if (!$item->isDot()) {

                if ($item->isDir() && $this->fullScan) {

                    $this->pathDir = $item->getPathname();
                    $this->deleteFilesToDir();

                } elseif($item->isFile()) {

                    unlink($item->getPathname());

                }
            }

            unset($item);
            $iterator->next();
        }

        $this->pathDir = $defaultPath;

        unset($defaultPath);
        unset($iterator);
    }

    /**
     * Delete files to directory and sub directory
     */
    public function deleteFilesDirAndSubDir(): void
    {
        // enabled full scan
        $this->fullScan = true;

        $this->deleteFilesToDir();

        // disabled full scan
        $this->fullScan = false;
    }

    /**
     * Copy files to directory
     *
     * @param string $dir
     */
    public function copyFilesToDir(string $dir): void
    {
        // check directory
        if (!is_dir($this->pathDir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $this->pathDir);
        }
        if (!is_dir($dir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $dir);
        }

        $iterator = new DirectoryIterator($this->pathDir);

        // set default path start directory
        $defaultPath = $this->pathDir;

        while ($iterator->valid()) {

            $item = $iterator->current();

            // ignore dot
            if (!$item->isDot()) {

                if ($item->isDir() && $this->fullScan) {

                    $this->pathDir = $item->getPathname();

                    // path new directory
                    $newDir = $dir . DIRECTORY_SEPARATOR . $item->getFilename();

                    // create new directory
                    mkdir($newDir);

                    // copy files sub directory
                    $this->copyFilesToDir($newDir);

                    // change file mode
                    if ($this->fileMode) chmod($newDir, $item->getPerms());

                    unset($newDir);

                } elseif($item->isFile()) {

                    copy(
                        $item->getPathname(),
                        $dir . DIRECTORY_SEPARATOR . $item->getFilename()
                    );

                    // change file mode
                    if ($this->fileMode) chmod($dir . DIRECTORY_SEPARATOR . $item->getPathname(), $item->getPerms());

                } elseif (
                    $item->isLink() &&
                    $this->fullScan &&
                    $this->showLink
                ) {

                    copy(
                        $item->getPathname(),
                        $dir . DIRECTORY_SEPARATOR . $item->getFilename()
                    );

                    // change file mode
                    if ($this->fileMode) chmod($dir . DIRECTORY_SEPARATOR . $item->getPathname(), $item->getPerms());
                }
            }

            unset($item);
            $iterator->next();
        }

        $this->pathDir = $defaultPath;

        unset($defaultPath);
        unset($iterator);
    }

    /**
     * Copy files directory & sub directory
     *
     * @param string $dir
     */
    public function copyStructureDir(string $dir): void
    {
        // enabled full scan
        $this->fullScan = true;

        $this->copyFilesToDir($dir);

        // disabled full scan
        $this->fullScan = false;
    }

    /**
     * Delete directory
     */
    public function deleteDir(): void
    {
        // check directory
        if (!is_dir($this->pathDir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $this->pathDir);
        }

        $this->clearDir();
        rmdir($this->pathDir);
    }

    /**
     * Delete directory by name
     *
     * @param string $name
     */
    public function deleteDirByName(string $name): void
    {
        $path = $this->pathDir;
        $this->pathDir = $this->pathDir . DIRECTORY_SEPARATOR . $name;

        $this->deleteDir();

        $this->pathDir = $path;
    }

    /**
     * Move directory new path
     *
     * @param string $newPath
     * @return string
     */
    public function moveDir(string $newPath): string
    {
        // check directory
        if (!is_dir($this->pathDir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $this->pathDir);
        }
        if (!is_dir($newPath)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $newPath);
        }

        // get new path dir
        $path = $newPath . DIRECTORY_SEPARATOR . $this->getCurrentDirName();

        rename($this->pathDir, $path);

        $this->pathDir = $path;

        return $this->pathDir;
    }

    /**
     * Move directory new path by name
     *
     * @param string $name
     * @param string $newPath
     */
    public function moveDirByName(string $name, string $newPath): void
    {
        $path = $this->pathDir;
        $this->pathDir = $this->pathDir . DIRECTORY_SEPARATOR . $name;

        $this->moveDir($newPath);

        $this->pathDir = $path;
    }

    /**
     * Rename directory
     *
     * @param string $newName
     * @return string
     */
    public function renameDir(string $newName): string
    {
        $list_path = explode(DIRECTORY_SEPARATOR, $this->pathDir);

        // get old name directory
        $old_name = array_pop($list_path);

        // get parent directory
        $this->pathDir = implode(DIRECTORY_SEPARATOR, $list_path);

        $this->renameDirByName($old_name, $newName);

        $this->pathDir = $this->pathDir . DIRECTORY_SEPARATOR . $newName;

        return $this->pathDir;
    }

    /**
     * Rename directory by name
     *
     * @param string $oldName
     * @param string $newName
     */
    public function renameDirByName(string $oldName, string $newName): void
    {
        // check directory
        if (!is_dir($this->pathDir . DIRECTORY_SEPARATOR . $oldName)) {
            $this->throwException(
                self::ERROR_DIR_NOT_EXIST,
                'current path: ' . $this->pathDir . DIRECTORY_SEPARATOR . $oldName
            );
        }

        rename(
            $this->pathDir . DIRECTORY_SEPARATOR . $oldName,
            $this->pathDir . DIRECTORY_SEPARATOR . $newName
        );
    }

    /**
     * Set settings show link
     *
     * @param bool $showLink
     */
    public function setShowLink(bool $showLink): void
    {
        $this->showLink = $showLink;
    }

    /**
     * Set settings file mode
     *
     * @param bool $fileMode
     */
    public function setFileMode(bool $fileMode): void
    {
        $this->fileMode = $fileMode;
    }

    /**
     * Set current path directory
     *
     * @param string $pathDir
     */
    public function setPathDir(string $pathDir): void
    {
        // check directory
        if (!is_dir($pathDir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $pathDir);
        }

        $this->pathDir = $pathDir;
    }

    /**
     * Get current path directory
     *
     * @return string
     */
    public function getPathDir(): string
    {
        return $this->pathDir;
    }

    /**
     * Get current name directory
     *
     * @param string $pathDir
     * @return string
     */
    public function getCurrentDirName(string $pathDir = ''): string
    {
        if (empty($pathDir)) {

            $dir = $this->pathDir;

        } else {

            // check directory
            if (!is_dir($pathDir)) {
                $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $pathDir);
            }

            $dir = $pathDir;
        }

        return array_pop(
            explode(DIRECTORY_SEPARATOR, $dir)
        );
    }

    /**
     * Get parent directory
     *
     * @param string $pathDir
     * @return string
     */
    public function getParentDir(string $pathDir = ''): string
    {
        if (empty($pathDir)) {

            $list_path = explode(DIRECTORY_SEPARATOR, $this->pathDir);

        } else {

            // check directory
            if (!is_dir($pathDir)) {
                $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $pathDir);
            }

            $list_path = explode(DIRECTORY_SEPARATOR, $pathDir);
        }

        array_pop($list_path);

        return implode(DIRECTORY_SEPARATOR, $list_path);
    }

    /**
     * Go to parent directory
     *
     * @return string
     */
    public function gotoParentDir(): string
    {
        $this->pathDir = $this->getParentDir();

        return $this->pathDir;
    }

    /**
     * Go to sub directory
     *
     * @param string $subDir
     * @return string
     */
    public function gotoSubDir(string $subDir): string
    {
        $new_dir = $this->pathDir . DIRECTORY_SEPARATOR . $subDir;

        // check directory
        if (!is_dir($new_dir)) {
            $this->throwException(self::ERROR_DIR_NOT_EXIST, 'current path: ' . $new_dir);
        }

        $this->pathDir = $new_dir;

        return $this->pathDir;
    }

    /**
     * Check oct (0777)
     *
     * @param $oct
     * @return bool
     */
    protected function isOct($oct): bool
    {
        return (bool)preg_match("/^[0-7]{4}$/", $oct);
    }

    /**
     * Exceptions
     *
     * @param int $code
     * @param string $msg
     * @throws Exception
     */
    protected function throwException(int $code, string $msg = ''): void
    {
        throw new Exception(
            $this->errors[$code] . ($msg ? ' ' . $msg : ''),
            $code
        );
    }
}