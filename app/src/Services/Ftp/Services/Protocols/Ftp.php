<?php

namespace App\Services\Ftp\Services\Protocols;

use App\Services\Ftp\Domain\DTO\FtpDTO;
use App\Services\Ftp\Domain\Exception\MissingFtpConfigException;
use DirectoryIterator;
use Exception;

final readonly class Ftp
{
    /**
     * @param FtpDTO $ftp
     * @param string $localPath
     * @param string $remotePath
     *
     * @return array|true[]
     */
    public function uploadFiles(
        FtpDTO $ftp,
        string  $localPath,
        string  $remotePath,
    ): array {
        try {
            $logsFilePath = $options['logs_file_path'] ?? null;
            $exportLog = $options['export_log'] ?? false;

            $export = true === $exportLog && !is_null($logsFilePath) ? "set log:file/xfer {$logsFilePath};" : '';

            $command = "lftp -c \"open -u {$ftp->username},{$ftp->password} {$ftp->host}; set ftp:ssl-allow no; set cmd:interactive yes; set dns:cache-expire never; set mirror:use-pget-n 10; {$export} mirror -R --parallel=50 {$localPath} {$remotePath}\" 1>&2";

            exec($command, $output, $error);

            if ($error) {
                throw new Exception("Upload failed! " . json_encode($error));
            }

            // Clean-up
            $this->cleanUploadFolder($localPath);

            return [
                'success' => true,
            ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    /**
     * @param string $location
     *
     * @return void
     */
    public function createFilesLocation(string $location): void
    {
        if (!is_dir($location)) {
            mkdir($location, 0777, true);
        }
    }

    /**
     * @param array $files
     * @param string $location
     *
     * @return void
     */
    public function moveFilesToUploadLocation(array $files, string $location): void
    {
        if (count($files) > 0) {
            $filesValues = array_values($files);
            if (is_array($filesValues[0]['tmp_name'])) {
                $files = $this->formatMultipleUploadedFiles($files);
            }

            foreach ($files as $file) {
                @move_uploaded_file($file['tmp_name'], $location . '/' . $file['name']);
            }
        }
    }

    /**
     * @param string $folder
     * @param string $location
     *
     * @return void
     */
    public function moveFolderToUploadLocation(string $folder, string $location): void
    {
        $location = rtrim($location, '/');

        if (is_dir($folder)) {
            if (!is_dir($location)) {
                $this->createFilesLocation($location);
            }

            $files = new DirectoryIterator($folder);
            foreach ($files as $file) {
                if ($file->isFile()) {
                    copy($file->getRealPath(), "$location/" . $file->getFilename());
                } else if (!$file->isDot() && $file->isDir()) {
                    $this->moveFolderToUploadLocation($file->getRealPath(), "$location/$file");
                }
            }
        }
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function cleanUploadFolder(string $path): void
    {
        if (is_dir($path)) {
            $folderToClean = rtrim($path, '/');
            system("rm -rf {$folderToClean}");
        }
    }

    /**
     * @return bool
     * @throws MissingFtpConfigException
     */
    public static function checkFtpDefaultConfig(): bool
    {
        if (empty($_ENV['FTP_HOST'])) {
            throw new MissingFtpConfigException('Ftp host must be set');
        }

        if (empty($_ENV['FTP_USERNAME'])) {
            throw new MissingFtpConfigException('Ftp username must be set');
        }

        if (empty($_ENV['FTP_PASSWORD'])) {
            throw new MissingFtpConfigException('Ftp password must be set');
        }

        if (empty($_ENV['FTP_PATH'])) {
            throw new MissingFtpConfigException('Ftp remote path must be set');
        }

        return true;
    }

    /**
     * @param array $files
     *
     * @return array
     */
    public function determineFilePathOrFilesSource(array $files = []): array
    {
        $folderPath = $this->getFolderPathFromFilesArray(array_values($files));

        if (empty($folderPath)) {
            return [
                'filesPath' => null,
                'files' => $files
            ];
        } else {
            if ($this->isFile($folderPath)) {
                return [
                    'filesPath' => null,
                    'files' => $this->formatMultipleUploadedFiles($files)
                ];
            } else {
                return [
                    'filesPath' => '/' . ltrim($folderPath, '/'),
                    'files' => []
                ];
            }
        }
    }

    /**
     * @param array $files
     *
     * @return string|null
     */
    private function getFolderPathFromFilesArray(array $files): ?string
    {
        $folderPath = null;

        if (count($files) > 0 && is_array($files[0]['full_path']) && count($files[0]['full_path']) > 0) {
            $firstFullPath = explode('/', $files[0]['full_path'][0]);
            $folderPath = count($firstFullPath) > 1 ? $firstFullPath[0] : null;
        }

        return $folderPath;
    }

    /**
     * @param string $filePath
     *
     * @return bool
     */
    private function isFile(string $filePath): bool
    {
        return str_contains($filePath, '.');
    }

    /**
     * @param array $files
     *
     * @return array
     */
    private function formatMultipleUploadedFiles(array $files): array
    {
        $formattedFiles = [];

        $filesValues = array_values($files);
        if (count($filesValues) === 1) {
            $contents = $filesValues[0];
            $numOfItems = $contents['name'][0] ? count($contents['name']) : 0;

            for ($i = 0; $i < $numOfItems; $i++) {
                $formattedFiles[] = [
                    'name' => $contents['name'][$i],
                    'full_path' => $contents['full_path'][$i],
                    'type' => $contents['type'][$i],
                    'tmp_name' => $contents['tmp_name'][$i],
                    'error' => $contents['error'][$i],
                    'size' => $contents['size'][$i],
                ];
            }
        }

        return $formattedFiles;
    }
}
