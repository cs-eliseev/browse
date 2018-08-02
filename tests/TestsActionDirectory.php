<?php

/**
 * Class TestsActionDirectory
 *
 * User: Alexey
 * Date: 01.08.2018
 * Time: 22:28
 */

use browse\ActionDirectory;

include_once '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'ActionDirectory.php';

class TestsActionDirectory
{
    protected $dir;

    protected $resultFileName;
    protected $resultFileDir;

    public function __construct($resultFileName, $resultFileDir)
    {
        $this->dir = new ActionDirectory();
        $this->resultFileName = $resultFileName;
        $this->resultFileDir = $resultFileDir;
    }

    public function createTestFile($dir)
    {
        $file_name =  strtotime('NOW') . '.txt';
        $file_path = $dir . DIRECTORY_SEPARATOR . $file_name;
        $file_content = 'date create: ' . date('d.m.Y H:i:s') . PHP_EOL
                      . 'file name: ' . $file_name . PHP_EOL
                      . 'path create: ' . $dir . PHP_EOL
                      . '************' . PHP_EOL . 'test' . PHP_EOL . '************';

        file_put_contents($file_path, $file_content);

        return $file_name;
    }


    public function setResultFile($name, $dir, $msg)
    {
        $file_name =  'result_' . $name . '.txt';
        $file_path = $dir . DIRECTORY_SEPARATOR . $file_name;
        $file_content = file_get_contents($file_path);
        $file_content .= PHP_EOL . '************' . PHP_EOL . $msg . PHP_EOL . '************' . PHP_EOL;

        file_put_contents($file_path, $file_content);

        return $file_name;
    }

    // test 3
    public function testShowDir($dir)
    {
        $this->dir->setPathDir($dir);

        $msg = 'SHOW DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL
             . print_r($this->dir->showDir() ,1);

        $this->log($msg);
        return $msg;
    }

    // test 4
    public function testScanDir($dir)
    {
        $this->dir->setPathDir($dir);

        $msg = 'SCAN DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL
             . print_r($this->dir->scanDir() ,1);

        $this->log($msg);
        return $msg;
    }

    // test 5
    public function testCreateDir($dir, $newDir)
    {
        $this->dir->setPathDir($dir);

        $this->dir->createDir($newDir);

        $msg = 'CREATE DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL . 'new dir: ' . $newDir . PHP_EOL
             . 'status: ' . (is_dir($dir . DIRECTORY_SEPARATOR . $newDir) ? 'Yes' : 'No');

        $this->log($msg);
        return $msg;
    }

    // test 12
    public function testClearDir($dir)
    {
        $this->dir->setPathDir($dir);

        $this->dir->clearDir();

        $msg = 'CLEAR DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL
             . 'status: ' . (empty($this->dir->scanDir()) ? 'Yes' : 'No');

        $this->log($msg);
        return $msg;
    }

    // test 14
    public function testDeleteFilesToDir($dir)
    {
        $this->dir->setPathDir($dir);

        $this->dir->deleteFilesToDir();

        $msg = 'DELETE FILES TO DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL
             . 'result: ' . print_r($this->dir->showDir(), 1);

        $this->log($msg);
        return $msg;
    }

    // test 15
    public function testDeleteFilesDirAndSubDir($dir)
    {
        $this->dir->setPathDir($dir);

        $this->dir->deleteFilesDirAndSubDir();

        $msg = 'DELETE FILES TO DIR AND SUB DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL
            . 'result: ' . print_r($this->dir->scanDir(), 1);

        $this->log($msg);
        return $msg;
    }

    // test 6
    public function testCopyFilesToDir($dir, $dirCopy)
    {
        $this->dir->setPathDir($dir);

        $this->dir->copyFilesToDir($dirCopy);

        $this->dir->setPathDir($dirCopy);

        $msg = 'COPY FILES TO DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL . 'copy path: ' . $dirCopy . PHP_EOL
            . 'result: ' . print_r($this->dir->showDir(), 1);

        $this->log($msg);
        return $msg;
    }

    // test 7
    public function testCopyStructureDir($dir, $dirCopy)
    {
        $this->dir->setPathDir($dir);

        $this->dir->copyStructureDir($dirCopy);

        $this->dir->setPathDir($dirCopy);

        $msg = 'COPY STRUCTURE DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL . 'copy path: ' . $dirCopy . PHP_EOL
             . 'result: ' . print_r($this->dir->scanDir(), 1);

        $this->log($msg);
        return $msg;
    }

    // test 13
    public function testDeleteDir($dir)
    {
        $this->dir->setPathDir($dir);

        $this->dir->deleteDir();

        $msg = 'DELETE DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL
             . 'status: ' . (!file_exists($dir) ? 'Yes' : 'No');

        $this->log($msg);
        return $msg;
    }

    // test 16
    public function testDeleteDirByName($dir, $name)
    {
        $this->dir->setPathDir($dir);

        $this->dir->deleteDirByName($name);

        $msg = 'DELETE DIR BY NAME' . PHP_EOL . 'path: ' . $dir . PHP_EOL . 'dir name: ' . $name . PHP_EOL
             . 'status: ' . (!file_exists($dir . DIRECTORY_SEPARATOR . $name) ? 'Yes' : 'No');

        $this->log($msg);
        return $msg;
    }

    // test 9
    public function testMoveDir($dir, $dirMove)
    {
        $this->dir->setPathDir($dir);

        $newPath = $dirMove . DIRECTORY_SEPARATOR . $this->dir->getCurrentDirName();

        $this->dir->moveDir($dirMove);

        $msg = 'MOVE DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL . 'dir move: ' . $dirMove . PHP_EOL
            . 'status: ' . (
                !file_exists($dir) & file_exists($newPath) ? 'Yes' : 'No'
            );

        $this->log($msg);
        return $msg;
    }

    // test 10
    public function testMoveDirByName($dir, $name, $dirMove)
    {
        $this->dir->setPathDir($dir);

        $this->dir->moveDirByName($name, $dirMove);

        $msg = 'MOVE DIR BY NAME' . PHP_EOL . 'path: ' . $dir . PHP_EOL . 'dir name: ' . $name . PHP_EOL
             . 'dir move: ' . $dirMove . PHP_EOL
             . 'status: ' . (
                !file_exists($dir . DIRECTORY_SEPARATOR . $name) &
                file_exists($dirMove . DIRECTORY_SEPARATOR . $name)
                    ? 'Yes' : 'No'
             );

        $this->log($msg);
        return $msg;
    }

    // test 8
    public function testRenameDir($dir, $newName)
    {
        $this->dir->setPathDir($dir);

        $parentPath = $this->dir->getParentDir($dir);

        $this->dir->renameDir($newName);

        $msg = 'RENAME DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL . 'new name: ' . $newName . PHP_EOL
            . 'status: ' . (
                !file_exists($dir) & file_exists($parentPath . DIRECTORY_SEPARATOR . $newName) ? 'Yes' : 'No'
            );

        $this->log($msg);
        return $msg;
    }

    // test 11
    public function testRenameDirByName($dir, $oldName, $newName)
    {
        $this->dir->setPathDir($dir);

        $this->dir->renameDirByName($oldName, $newName);

        $msg = 'RENAME DIR BY NAME' . PHP_EOL . 'path: ' . $dir . PHP_EOL . 'old name: ' . $oldName . PHP_EOL
             . 'new name: ' . $newName . PHP_EOL
             . 'status: ' . (
                !file_exists($dir . DIRECTORY_SEPARATOR . $oldName) &
                file_exists($dir . DIRECTORY_SEPARATOR . $newName)
                    ? 'Yes' : 'No'
             );

        $this->log($msg);
        return $msg;
    }

    // test 1
    public function testGetCurrentDirName($dir)
    {
        $msg = 'GET CORRENT DIR NAME' . PHP_EOL . 'path: ' . $dir . PHP_EOL
             . 'result: ' . $this->dir->getCurrentDirName($dir);

        $this->log($msg);
        return $msg;
    }

    // test 2
    public function testGetParentDir($dir)
    {
        $msg = 'GET PARENT DIR' . PHP_EOL . 'path: ' . $dir . PHP_EOL
             . 'result: ' . $this->dir->getParentDir($dir);

        $this->log($msg);
        return $msg;
    }

    protected function log($msg){

        $this->setResultFile($this->resultFileName, $this->resultFileDir, $msg);
    }
}