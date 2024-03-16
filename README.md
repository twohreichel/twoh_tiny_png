# Tiny PNG Extension

## Minimum requirements
* **PHP** 8
* **composer** ^2.4
* **TYPO3** 12

## Setup Tiny PNG API Key
Please visit the Tiny PNG Webside (https://tinypng.com/developers):
* Enter your Full Name
* Enter your E-Mail 
* **Get your API Key**

## Setup 

##### Extension
* install Extension via Composer or FTP
* include Extension in TypoScript **ROOT Template**
* set current **API Key** in TypoScript Settings
* create some **Backendfolder** for your TinyPNG Records
* set current **PID** in TypoScript Settings
* if you want to Resize the Image on Upload, please set current **Width (Height is calculated automatically)** in TypoScript Settings

##### Scheduler (Bulk Optimization)
* create Scheduler Task:
    * **Add Tiny PNG Bulk Compression Command**
* start cron, **The cron may take longer depending on the number of images.**

## Notes
##### Ignore Folders
If you need to ignore Images from a specific Folder add them to your TypoScript Constant (comma separated):
````typo3_typoscript
ignoreImagesByFolderName = {$plugin.tx_twohtinypng.settings.ignoreImagesByFolderName}
````
##### File Upload Process
The Extension hooks into the FileUploadProcess and compress on Upload