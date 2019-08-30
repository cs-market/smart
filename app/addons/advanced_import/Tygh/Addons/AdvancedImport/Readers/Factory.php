<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Addons\AdvancedImport\Readers;

use Tygh\Addons\AdvancedImport\Exceptions\FileNotFoundException;
use Tygh\Addons\AdvancedImport\Exceptions\ReaderNotFoundException;
use Tygh\Enum\Addons\AdvancedImport\PresetFileTypes;
use Tygh\Exceptions\AException;
use Tygh\Exceptions\PermissionsException;

class Factory
{
    /** @var array $file_dirs */
    protected $file_dirs;

    /** @var int|null $company_id */
    protected $company_id;

    /**
     * Factory constructor.
     *
     * @param int|null $company_id Current user company ID
     */
    public function __construct($company_id)
    {
        $this->company_id = (int) $company_id;

        $this->file_dirs = $this->initFilesDirectories($company_id);
    }

    /**
     * Gets file reader.
     *
     * @param array $preset Preset to read file for
     *
     * @return \Tygh\Addons\AdvancedImport\Readers\IReader Reader instance
     * @throws \Tygh\Exceptions\PermissionsException
     * @throws \Tygh\Addons\AdvancedImport\Exceptions\FileNotFoundException
     * @throws \Tygh\Addons\AdvancedImport\Exceptions\ReaderNotFoundException
     */
    public function get(array $preset)
    {
        $file_to_load = $preset['file'];

        if (!$this->company_id && isset($preset['company_id'])) {
            $company_id = $preset['company_id'];
            if (preg_match('!^(?P<company_id_in_path>\d+)/(?P<file_to_load>.+)!', $file_to_load, $matches)
                && $matches['company_id_in_path'] != $company_id
            ) {
                throw new PermissionsException();
            }
            $file_to_load = preg_replace("!^{$company_id}/!", '', $file_to_load);
        } else {
            $company_id = $this->company_id;
        }

        if ($preset['file_type'] == PresetFileTypes::URL) {
            $file = $this->download($preset['file'], $company_id);
            $file_to_load = $file['name'];
        }

        $file_path = $this->getFilePath($file_to_load, $company_id);
        if (!$file_path) {
            throw new FileNotFoundException();
        }

        $ext = fn_get_file_ext($file_to_load);
        $reader_class = '\Tygh\Addons\AdvancedImport\Readers\\' . fn_camelize($ext);
        if (!class_exists($reader_class)) {
            throw new ReaderNotFoundException();
        }

        $options = isset($preset['options'])
            ? $preset['options']
            : array();

        /** @var \Tygh\Addons\AdvancedImport\Readers\IReader $reader */
        $reader = new $reader_class($file_path, $options);

        return $reader;
    }

    /**
     * Downloads file.
     *
     * @param string   $url        Url
     * @param int|null $company_id Company to download file for
     *
     * @return array|null
     */
    public function download($url, $company_id = null)
    {
        $company_id = $this->getCompanyId($company_id);

        $fileinfo = fn_get_url_data($url);

        $ext = fn_get_file_ext($fileinfo['name']);

        if (!$ext) {
            $mime_to_ext = fn_get_ext_mime_types('mime');

            $ext = isset($mime_to_ext[$fileinfo['type']])
                ? $mime_to_ext[$fileinfo['type']]
                : null;
        }

        if (!$ext) {
            return null;
        }

        if ($fileinfo['name'] == '') {
            $fileinfo['name'] = md5($url);
        }

        if (substr($fileinfo['name'], -fn_strlen($ext)) !== $ext) {
            $fileinfo['name'] .= '.' . $ext;
        }

        $this->moveUpload($fileinfo['name'], $fileinfo['path'], $company_id);

        return $fileinfo;
    }

    /**
     * Gets filepath to a file on server.
     *
     * @param string     $filename   Filename
     * @param int|null   $company_id Company to search file for
     * @param array|null $file_dirs  Directories to search in
     *
     * @return null|string
     */
    public function getFilePath($filename, $company_id = null, array $file_dirs = null)
    {
        $company_id = $this->getCompanyId($company_id);

        if ($file_dirs === null) {
            if ($company_id == $this->company_id) {
                $file_dirs = $this->file_dirs;
            } else {
                $file_dirs = $this->initFilesDirectories($company_id);
            }
        }

        foreach ($file_dirs as $dir) {
            if (file_exists($dir . $filename)) {
                return $dir . $filename;
            }
        }

        return null;
    }

    /**
     * Gets path to private files directory.
     * Creates missing private files directory.
     *
     * @param int|null $company_id Company to get path for
     *
     * @return string Private files directory path
     */
    protected function getPrivateFilesPath($company_id = null)
    {
        $company_id = $this->getCompanyId($company_id);

        $path = fn_get_files_dir_path($company_id);

        fn_mkdir($path);

        return $path;
    }

    /**
     * Gets path to public files directory.
     * Creates missing public files directory.
     *
     * @param int|null $company_id Company to get path for
     *
     * @return string Public files directory path
     */
    protected function getPublicFilesPath($company_id = null)
    {
        $company_id = $this->getCompanyId($company_id);

        $path = fn_get_public_files_path($company_id);

        fn_mkdir($path);

        return $path;
    }

    public function initFilesDirectories($company_id = null)
    {
        $company_id = $this->getCompanyId($company_id);

        return array(
            'private' => $this->getPrivateFilesPath($company_id),
            'public'  => $this->getPublicFilesPath($company_id),
        );
    }

    /**
     * Moves file to a private files directory of a company.
     *
     * @param string   $filename    Filename in the target directory
     * @param string   $source_path Current file location
     * @param int|null $company_id  Owning company of the file
     */
    public function moveUpload($filename, $source_path, $company_id = null)
    {
        $company_id = $this->getCompanyId($company_id);

        $uploaded_file_location = $this->getPrivateFilesPath($company_id) . $filename;

        fn_rename($source_path, $uploaded_file_location);
    }

    /**
     * Provides corrected company ID for assorted checks.
     *
     * @param int|null $company_id Company ID to check
     *
     * @return int|null
     */
    protected function getCompanyId($company_id = null)
    {
        return $this->company_id ?: $company_id;
    }
}