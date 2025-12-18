.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

TypoScript Configuration
========================

API Key
-------

Configure your TinyPNG API key via environment variable (recommended):

.. code-block:: bash

   TINIFY_API_KEY=your_api_key_here

Or via TypoScript constant:

.. code-block:: typoscript

   plugin.tx_twohtinypng.settings.apiKey = your_api_key

Storage PID
-----------

.. code-block:: typoscript

   plugin.tx_twohtinypng.settings.pid = 123

Image Width
-----------

Set maximum width for resizing on upload:

.. code-block:: typoscript

   plugin.tx_twohtinypng.settings.width = 1920

Ignore Folders
--------------

Exclude specific folders from optimization:

.. code-block:: typoscript

   plugin.tx_twohtinypng.settings.ignoreImagesByFolderName = tmp, icons, private

Scheduler Setup (Bulk Optimization)
====================================

To optimize existing images in bulk:

1. Navigate to **System → Scheduler**
2. Click **Add Task**
3. Select **"Tiny PNG: Bulk Compression"** from the command dropdown
4. Configure the task frequency (e.g., daily, weekly)
5. Save and run manually or wait for the scheduled execution

.. important::
   Bulk optimization can take considerable time depending on the number of images
   in your fileadmin. Monitor the API quota to avoid exceeding your monthly limit.

How It Works
============

Automatic Compression on Upload
--------------------------------

The extension hooks into TYPO3's file upload process. When a JPG or PNG image
is uploaded to the file system:

1. The image is automatically sent to the TinyPNG API
2. The compressed version replaces the original file
3. File metadata (size, dimensions) is updated in the database
4. If a maximum width is configured, the image is resized before compression

Bulk Optimization via Scheduler
--------------------------------

When executing the Scheduler command:

1. All images in ``fileadmin/`` are scanned
2. Images that haven't been processed yet are queued for compression
3. Each image is compressed via the TinyPNG API
4. Metadata is updated automatically
5. Already optimized images are skipped (no duplicate processing)

.. note::
   The extension tracks which images have already been optimized to prevent
   unnecessary API calls and preserve your monthly quota.
