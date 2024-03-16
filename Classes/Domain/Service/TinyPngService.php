<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Domain\Service;

use Doctrine\DBAL\Driver\Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Tinify\AccountException;
use TWOH\TwohTinyPng\Domain\Utilities\DatabaseUtility;
use TWOH\TwohTinyPng\Domain\Utilities\MetaDataUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use function Tinify\fromFile;
use function Tinify\setKey;
use function Tinify\validate;

/**
 * Class TinyPngService
 * @package TWOH\TwohTinyPng\Domain\Service
 */
class TinyPngService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    protected array $args = [];

    /**
     * @var DatabaseUtility
     */
    protected DatabaseUtility $databaseUtility;

    /**
     * @var MetaDataUtility
     */
    protected MetaDataUtility $metaDataUtility;

    /**
     * @throws AccountException
     * @throws InvalidConfigurationTypeException
     */
    public function __construct()
    {
        $this->args = $this->getTsSetup();
        $this->databaseUtility = GeneralUtility::makeInstance(DatabaseUtility::class);
        $this->metaDataUtility = GeneralUtility::makeInstance(MetaDataUtility::class);

        setKey($this->args['apiKey']);
        validate();
    }

    /**
     * @return mixed
     * @throws InvalidConfigurationTypeException
     */
    public function getTsSetup(): mixed
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            'twoh_tiny_png'
        );
        return $settings['plugin.']['tx_twohtinypng.']['settings.'];
    }

    /**
     * @param string $path
     * @param string $img
     * @throws Exception
     */
    public function imageCompression(
        string $path,
        string $img
    ): void
    {
        $folderAllowed = true;

        if ($this->args['ignoreImagesByFolderName']) {
            $ignoreImagesByFolderNames = explode(',', $this->args['ignoreImagesByFolderName']);
            if (count($ignoreImagesByFolderNames) > 0) {
                foreach ($ignoreImagesByFolderNames as $ignoreImagesByFolderName) {
                    if (str_contains($img, trim($ignoreImagesByFolderName))) {
                        $folderAllowed = false;
                    }
                }
            }
        }

        if ($folderAllowed) {
            try {
                $width = $this->args['width'] ?? 2560;
                $pid = $this->args['pid'] ?? 1;

                if ($this->databaseUtility->findByIdentifier($img)) {
                    $source = fromFile($path . $img);
                    $resized = $source->resize([
                        "method" => "scale",
                        "width" => (int) $width
                    ]);

                    $tinyModel = [];
                    $tinyModel['dimension'] = (int) $width;
                    $tinyModel['pid'] = (int) $pid;
                    $tinyModel['identifier'] = $img;

                    $compressedFile = $resized->toFile($path . $img);

                    if ($compressedFile) {
                        $this->databaseUtility->add($tinyModel);

                        // update meta values
                        $this->metaDataUtility->updateMetaData(
                            $path,
                            $img
                        );
                    }
                }
            } catch (\Exception $e) {
                // @extensionScannerIgnoreLine
                $this->logger->error($e->getMessage());
            }
        }
    }
}
