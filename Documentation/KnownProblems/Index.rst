.. include:: /Includes.rst.txt

.. _known-problems:

==============
Known Problems
==============

API Quota Limits
================

The free TinyPNG API allows up to 500 compressions per month.
If quota is exceeded, new uploads will be skipped until the next month.

Troubleshooting
===============

If compression doesn't work:

1. Check your API key configuration
2. Review log file: `var/log/typo3_tinypng_upload.log`
3. Verify PHP extensions (gd, curl) are installed
4. Check API quota at https://tinypng.com/dashboard
