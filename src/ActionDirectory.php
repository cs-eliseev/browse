<?php

namespace browse;

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
        self::ERROR_DIR_NOT_EXIST => 'Дирректория не найдена',
        self::ERROR_FILE_MOD_NOT_OCT => 'Неверный формат прав'
    ];

    public function __construct($pathDir = '')
    {
        $this->pathDir = $pathDir;
    }

    /**
     * Show directory
     *
     * @param string $defaultPath
     * @return array
     * @throws Exception
     * @throws SystemException
     */
    public function showDir($defaultPath = '')
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
                        $attr['node'] = $this->scanDir($defaultPath);
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
                        $attr['node'] = $this->scanDir($defaultPath);
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
     * Show directory & sub directory
     *
     * @param string $defaultPath
     * @return array
     * @throws Exception
     * @throws SystemException
     */
    public function scanDir($defaultPath = '')
    {
        // enabled full scan
        $this->fullScan = true;

        $res = $this->showDir($defaultPath);

        // disabled full scan
        $this->fullScan = false;

        return $res;
    }

    /**
     * Create new directory to path
     *
     * @param $newDir
     * @param string $fileMod
     * @return bool
     * @throws Exception
     */
        public function createDir($newDir, $fileMod = '0755')
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
     *
     * @throws Exception
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
     *
     * @throws Exception
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
     * @param $dir
     * @throws Exception
     */
    public function copyFilesToDir($dir): void
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
     * @param $dir
     * @throws Exception
     */
    public function copyStructureDir($dir): void
    {
        // enabled full scan
        $this->fullScan = true;

        $this->copyFilesToDir($dir);

        // disabled full scan
        $this->fullScan = false;
    }

    /**
     * Delete directory
     *
     * @throws Exception
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
     * @param $name
     * @throws Exception
     */
    public function deleteDirByName($name): void
    {
        $path = $this->pathDir;
        $this->pathDir = $this->pathDir . DIRECTORY_SEPARATOR . $name;

        $this->deleteDir();

        $this->pathDir = $path;
    }

    /**
     * Move directory new path
     *
     * @param $newPath
     * @return bool|string
     * @throws Exception
     */
    public function moveDir($newPath)
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
     * @param $name
     * @param $newPath
     * @throws Exception
     */
    public function moveDirByName($name, $newPath)
    {
        $path = $this->pathDir;
        $this->pathDir = $this->pathDir . DIRECTORY_SEPARATOR . $name;

        $this->moveDir($newPath);

        $this->pathDir = $path;
    }

    /**
     * Rename directory
     *
     * @param $newName
     * @return bool|string
     * @throws Exception
     */
    public function renameDir($newName)
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
     * @param $oldName
     * @param $newName
     * @throws Exception
     */
    public function renameDirByName($oldName, $newName): void
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
    public function setFileMode(bool $fileMode)
    {
        $this->fileMode = $fileMode;
    }

    /**
     * Set current path directory
     *
     * @param $pathDir
     * @throws Exception
     */
    public function setPathDir($pathDir)
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
     * @return bool|string
     */
    public function getPathDir()
    {
        return $this->pathDir;
    }

    /**
     * Get current name directory
     *
     * @param string $pathDir
     * @return mixed
     * @throws Exception
     */
    public function getCurrentDirName($pathDir = '')
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
     * @throws Exception
     */
    public function getParentDir($pathDir = '')
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
    public function gotoParentDir()
    {
        $this->pathDir = $this->getParentDir();

        return $this->pathDir;
    }

    /**
     * Go to sub directory
     *
     * @param $subDir
     * @return string
     */
    public function gotoSubDir($subDir)
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
     * @return false|int
     */
    protected function isOct($oct)
    {
        return preg_match("/^[0-7]{4}$/", $oct);
    }

    /**
     * Exceptions
     *
     * @param $code
     * @param string $msg
     * @throws Exception
     */
    protected function throwException($code, $msg = '')
    {
        throw new Exception(
            $this->errors[$code] . ($msg ? ' ' . $msg : ''),
            $code
        );
    }
}