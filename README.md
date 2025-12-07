# 🐼 TinyPNG TYPO3 Extension

> Optimize your TYPO3 images automatically using the TinyPNG API

[![TYPO3 12](https://img.shields.io/badge/TYPO3-12-orange.svg)](https://get.typo3.org/version/12)
[![TYPO3 13](https://img.shields.io/badge/TYPO3-13-green.svg)](https://get.typo3.org/version/13)
[![PHP 8.x](https://img.shields.io/badge/PHP-8.x-blue.svg)](https://www.php.net/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

---

## 📋 Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | >= 8.2  |
| Composer    | >= 2.4  |
| TYPO3       | 12.x    |
| TYPO3       | 13.x    |

---

## 🔑 Get Your TinyPNG API Key

1. Visit [TinyPNG Developers](https://tinypng.com/developers)
2. Enter your **Full Name**
3. Enter your **E-Mail**
4. Receive your **API Key**

---

## 🚀 Installation & Setup

### Extension Setup

1. Install the extension via Composer:
   ```bash
   composer require twoh/twoh_tiny_png
   ```

2. Include the extension in your TypoScript **ROOT Template**

3. Configure the following TypoScript settings:
   - Set your **API Key**
   - Create a **Backend folder** for TinyPNG records
   - Set the **PID** of your backend folder
   - *(Optional)* Set **Width** for image resizing on upload (height is calculated automatically)

### Scheduler Setup (Bulk Optimization)

1. Create a new Scheduler Task
2. Select **TinyPNG Bulk Compression Command**
3. Start the cron job

> ⚠️ **Note:** The cron may take longer depending on the number of images.

---

## ⚙️ Configuration

### Ignore Specific Folders

To exclude images from specific folders, add them to your TypoScript Constants (comma-separated):

```typoscript
ignoreImagesByFolderName = {$plugin.tx_twohtinypng.settings.ignoreImagesByFolderName}
```

### File Upload Process

The extension hooks into the File Upload Process and automatically compresses images on upload.

---

## 🛠️ Development

### Code Quality with PHP CS Fixer

This extension uses PHP CS Fixer for code style enforcement following TYPO3 best practices.

| Command | Description |
|---------|-------------|
| `composer cs:fix` | Auto-fix code style issues |
| `composer cs:check` | Check for violations (dry-run) |

---

## 👥 Authors

| Name | Role | Contact |
|------|------|---------|
| Andreas Reichel | Developer | [a.reichel91@outlook.com](mailto:a.reichel91@outlook.com) |
| Igor Smertin | Developer | [igor.smertin@web.de](mailto:igor.smertin@web.de) |

---

## 📚 Documentation

For detailed documentation, visit: [TYPO3 Documentation](https://docs.typo3.org/p/twoh/twoh_tiny_png/main/en-us/)

---

## 🐛 Issues & Support

Found a bug or have a feature request? Please open an issue on [GitHub](https://github.com/twohreichel/twoh_tiny_png/issues).

---

## 📄 License

This project is licensed under the **GPL-2.0-or-later** license.