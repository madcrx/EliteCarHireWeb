@echo off
echo ================================================
echo Elite Car Hire - Windows Setup Helper
echo ================================================
echo.
echo This script will help you set up Elite Car Hire
echo for deployment to GitHub and cPanel.
echo.
echo Press any key to continue...
pause > nul

echo.
echo Step 1: Checking Git installation...
git --version
if errorlevel 1 (
    echo Git is NOT installed!
    echo Please download from: https://git-scm.com/download/win
    pause
    exit /b 1
)
echo Git is installed successfully!
echo.

echo Step 2: Current directory:
cd
echo.

echo Step 3: Would you like to initialize Git repository? (Y/N)
set /p INIT_GIT=
if /i "%INIT_GIT%"=="Y" (
    echo Initializing Git repository...
    git init
    git add .
    git commit -m "Initial commit - Elite Car Hire"
    echo.
    echo Git repository initialized!
    echo.
    echo Next steps:
    echo 1. Create repository on GitHub
    echo 2. Run: git remote add origin https://github.com/YOUR-USERNAME/elite-car-hire.git
    echo 3. Run: git push -u origin main
    echo.
)

echo.
echo Step 4: Creating deployment checklist...
echo.
echo DEPLOYMENT CHECKLIST:
echo [ ] Extract elite-car-hire folder
echo [ ] Install Git for Windows
echo [ ] Push to GitHub
echo [ ] Upload to cPanel via FTP or File Manager
echo [ ] Create MySQL database in cPanel
echo [ ] Import database/complete_schema.sql
echo [ ] Edit .env file with credentials
echo [ ] Set folder permissions (storage: 775)
echo [ ] Test website
echo [ ] Login and change admin password
echo [ ] Configure settings
echo [ ] Install SSL certificate
echo.

echo Setup helper complete!
echo.
echo For detailed instructions, see:
echo - DEPLOYMENT_WINDOWS.md
echo - INSTALLATION.md
echo - README.md
echo.
echo Press any key to exit...
pause > nul
