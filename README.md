Browse PHP
==========

This library contains pure PHP implementations for directory managing.


# Install


### Composer

Execute the following command to get the latest version of the package:
>composer require cs-eliseev/browse

### Usage

Usage all method exec relative set path directory.

**Init**

Example:

```php
$dir = new ActionDirectory(__DIR__);
```

**Set path directory**

Example:

```php
$dir->setPathDir(__DIR__);
```

**Get current name directory**

Example:

```php
$dir->getCurrentDirName();
```

**Get parent directory**

Example:

```php
$dir->getParentDir();
```

**Get current path directory**

Example:

```php
$dir->getPathDir();
```

**Go to parent directory**

Example:

```php
$dir->gotoParentDir();
```

**Go to sub directory**

Example:

```php
$dir->gotoSubDir('subDir/subDir');
```

**Show directory**

Example:

```php
$dir->showDir();
```

**Show directory & sub directory**

Example:

```php
$dir->scanDir();
```

**Create new directory to path**

Example:

```php
$dir->createDir('newDir/newSubDir/', "0755");
```

**Clear directory**

Example:

```php
$dir->clearDir();
```

**Delete files to directory**

Example:

```php
$dir->deleteFilesToDir();
```

**Delete files to directory and sub directory**

Example:

```php
$dir->deleteFilesDirAndSubDir();
```

**Copy files to directory**

Example:

```php
$dir->copyFilesToDir($dirCopy);
```

**Copy files directory & sub directory**

Example:

```php
$dir->copyStructureDir($dirCopy);
```

**Delete directory and all files**

Example:

```php
$dir->deleteDir();
```

**Delete directory by name in curent directory**

Example:

```php
$dir->deleteDirByName($name);
```

**Move directory new path**

Example:

```php
$dir->moveDir($dirMove);
```

**Move directory new path by name in curent directory**

Example:

```php
$dir->moveDirByName($name, $dirMove);
```

**Rename directory**

Example:

```php
$dir->renameDir($newName);
```

**Rename directory by name in curent directory**

Example:

```php
$dir->renameDirByName($oldName, $newName);
```


