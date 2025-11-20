============
User Manual v1.0.3
============

.. contents:: Table of Contents
   :depth: 2
   :local:

Introduction
============

The *Tiny PNG* extension connects your TYPO3 installation with the
`TinyPNG API <https://tinypng.com/developers>`__ to compress PNG and JPG images.

You can:
- Automatically compress images during upload
- Resize them if a maximum width is set
- Bulk-optimize existing images using a Scheduler task
- Exclude specific folders from optimization

----

System Requirements
===================

- **PHP:** 8.0 or higher
- **Composer:** 2.4 or higher
- **TYPO3:** 12 LTS
- Required PHP extensions: `gd`, `curl`, `json`, `pdo`

----

Installation
============

1. Install via Composer:

.. code-block:: bash

   composer require twoh/twoh_tiny_png

or upload the extension manually to `/typo3conf/ext/`.

2. Include the extension’s TypoScript template in your **Root Template**.

3. Obtain your API key from `https://tinypng.com/developers`.

4. Configure your API key:
- **Recommended:** via environment variable `TINIFY_API_KEY`
  *(preferred for security)*
- Or via TypoScript constant:

  ```
.. code-block:: typoscript

   plugin.tx_twohtinypng.settings.apiKey = your_api_key
  ```

5. (Optional) Create a backend folder for TinyPNG records and note its **PID**.
Set it in TypoScript:

plugin.tx_twohtinypng.settings.pid = 123


----

Configuration
=============

**Width on upload**

If you want to resize images during upload, define the target width (height is
calculated automatically):

plugin.tx_twohtinypng.settings.width = 1920

**Ignore folders**

To skip optimization for certain folders, add their names (comma-separated):

``plugin.tx_twohtinypng.settings.ignoreImagesByFolderName = tmp, icons, private``

**Full example**

.. code-block:: typoscript

   plugin.tx_twohtinypng.settings {
     apiKey = abc123xyz
     pid = 12
     width = 1920
     ignoreImagesByFolderName = tmp, icons
   }

----

Scheduler Setup (Bulk Optimization)
===================================

1. Go to **System → Scheduler → Add Task**
2. Choose **"Tiny PNG: Bulk Compression"** command
3. Save and start manually or add to a cronjob

⚠️ Note: bulk optimization can take a while depending on the number of images.

----

How It Works
============

- The extension hooks into TYPO3’s file upload process.
  When an image (JPG/PNG) is uploaded, it is automatically compressed
  using the TinyPNG API.

- When using the Scheduler command, all images in `fileadmin/` are scanned
  and compressed if they were not already processed.

- Metadata (file size and dimensions) are updated automatically.

----

Troubleshooting
===============

- If nothing happens on upload, check your API key and log file
  (`var/log/typo3_tinypng_upload.log`).

- The free TinyPNG API plan allows up to **500 compressions/month**.

- If your quota is reached, new uploads will be skipped until the next month.

----

Changelog
=========

**1.0.1**
---------
- Replaced deprecated methods
- Refactored TinyPNG API initialization (lazy initialization pattern)

**1.0.0**
---------
- Initial release
- Upload hook and bulk optimization via Scheduler
- TypoScript configuration for API key, PID, and resize options
