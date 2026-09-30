# Node.js Upgrade Guide for Playwright

## 🎯 Objective

**Goal:** Upgrade Node.js from v16.20.2 to v20+ to enable Playwright for real-time cricket data scraping.

**Current Status:** Node.js v16.20.2 (Playwright requires v20.0.0+)

---

## 📋 Why Upgrade is Needed

Playwright requires Node.js 20.0.0 or higher to function properly. The current system has Node.js v16.20.2, which is incompatible with Playwright's requirements.

### Current State
- **Node.js Version:** v16.20.2
- **Playwright Status:** ⚠️ Not Active
- **Current Method:** HTTP Fallback (still functional but limited)
- **Real Team Names:** ✅ Working (from database)

### After Upgrade
- **Node.js Version:** v20.x.x
- **Playwright Status:** ✅ Active
- **Current Method:** Playwright (JavaScript rendering)
- **Real Data:** ✅ Enhanced (can access JavaScript-rendered content)

---

## 🚀 Upgrade Instructions

### Option 1: Using Node Version Manager (NVM) - Recommended

NVM allows you to manage multiple Node.js versions easily.

#### Step 1: Install NVM (if not already installed)

**For Windows:**
```bash
# Download NVM for Windows from: https://github.com/coreybutler/nvm-windows/releases
# Download the latest nvm-setup.exe and run it
```

**For macOS/Linux:**
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
```

#### Step 2: Install Node.js v20
```bash
nvm install 20
nvm use 20
```

#### Step 3: Set as Default (Optional)
```bash
nvm alias default 20
```

#### Step 4: Verify Installation
```bash
node --version
# Should output: v20.x.x
```

---

### Option 2: Direct Download from Node.js Website

#### Step 1: Download Node.js v20
1. Visit: https://nodejs.org/
2. Download the LTS version (v20.x.x) for your operating system
3. Run the installer

#### Step 2: Verify Installation
```bash
node --version
# Should output: v20.x.x
```

---

### Option 3: Using Chocolatey (Windows)

#### Step 1: Install Chocolatey (if not already installed)
```powershell
# Run PowerShell as Administrator
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

#### Step 2: Install Node.js v20
```powershell
choco install nodejs-lts -y
```

#### Step 3: Verify Installation
```bash
node --version
# Should output: v20.x.x
```

---

## 🔧 Post-Upgrade Steps

### Step 1: Install Playwright Browsers

After upgrading Node.js, you need to install the Playwright browser binaries:

```bash
cd C:\wamp\www\criclivem\DevCricket
vendor/bin/playwright-install --with-deps
```

**What this does:**
- Downloads Chromium, Firefox, and WebKit browsers
- Installs system dependencies for Playwright
- Configures Playwright for your system

### Step 2: Verify Playwright Installation

```bash
vendor/bin/playwright-install --dry-run
```

This will show what would be installed without actually installing.

### Step 3: Test the Implementation

```bash
php test_scraping.php
```

Expected output should show:
```
Using Playwright for live matches scraping
Successfully fetched Cricbuzz live matches with Playwright
```

---

## 🧪 Verification Steps

### 1. Check Node.js Version
```bash
node --version
```
Expected: `v20.x.x` (where x.x is any version)

### 2. Check Playwright Availability
```bash
php -r "echo class_exists('Playwright\Playwright') ? 'Playwright available' : 'Playwright not available';"
```
Expected: `Playwright available`

### 3. Run Test Script
```bash
php test_scraping.php
```
Expected: Should show "Using Playwright" in the output

---

## 🔄 Rollback (If Needed)

If you encounter issues after upgrading, you can rollback:

### Using NVM
```bash
nvm use 16
nvm alias default 16
```

### Using Direct Installation
1. Uninstall Node.js v20 from your system
2. Reinstall Node.js v16 from https://nodejs.org/

---

## 📊 Performance Comparison

| Method | Response Time | Real Data | JavaScript | Node.js Required |
|--------|---------------|-----------|-------------|------------------|
| **Playwright (v20+)** | 2-5s | ✅ High | ✅ Yes | v20+ |
| **HTTP Fallback (v16)** | 0.02-0.18s | ⚠️ Medium | ❌ No | Any |

---

## ⚠️ Troubleshooting

### Issue: Playwright Installation Fails

**Error:** `Installation failed: Node.js 20.0.0+ is required`

**Solution:**
1. Verify Node.js version: `node --version`
2. If still v16, restart your terminal/command prompt
3. If still v16, reboot your computer
4. Reinstall Node.js v20

### Issue: Browser Download Fails

**Error:** `Failed to download Chromium`

**Solution:**
```bash
# Try installing without system dependencies first
vendor/bin/playwright-install --browsers

# If that fails, try with dependencies
vendor/bin/playwright-install --with-deps
```

### Issue: Playwright Class Not Found

**Error:** `Class 'Playwright\Playwright' not found`

**Solution:**
```bash
# Reinstall Playwright package
composer require --dev playwright-php/playwright

# Regenerate autoload
composer dump-autoload
```

---

## 🎯 Expected Results After Upgrade

### Before Upgrade (Current)
```
Using HTTP for live matches scraping
Data Source: HTTP (Real Team Names)
Response Time: 0.18 seconds
```

### After Upgrade
```
Using Playwright for live matches scraping
Successfully fetched Cricbuzz live matches with Playwright
Data Source: Playwright (Real JavaScript Data)
Response Time: 2-5 seconds
```

---

## 📝 Summary

**Current State:**
- ✅ Playwright package installed
- ✅ Code implementation complete
- ✅ Automatic fallback working
- ⚠️ Node.js needs upgrade

**After Upgrade:**
- ✅ Playwright fully functional
- ✅ JavaScript rendering enabled
- ✅ Enhanced real data extraction
- ✅ Better Cricbuzz data access

**Important:** The system currently works with HTTP fallback and displays real team names. The upgrade to Node.js v20+ will enhance data extraction capabilities but is not required for basic functionality.

---

## 🆘 Support

If you encounter issues during the upgrade:

1. Check Node.js version: `node --version`
2. Check Composer packages: `composer show`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Run test script: `php test_scraping.php`

For more information:
- Playwright PHP: https://github.com/playwright-php/playwright
- Node.js: https://nodejs.org/
- NVM: https://github.com/nvm-sh/nvm
