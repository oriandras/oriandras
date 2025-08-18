# PowerShell

param(
  [string]$SiteName = "oriandras",
  [string]$LaragonWWW = "C:\laragon\www",
  [string]$SiteTitle,
  [string]$AdminUser = "ori.andras",
  [string]$AdminEmail = "andras.ori@gmail.com",
  [string]$DBRootUser = "root",
  [string]$DBRootPassword = ""
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function New-RandomString([int]$Length = 24) {
  $chars = ('a'..'z') + ('A'..'Z') + ('0'..'9')
  -join (1..$Length | ForEach-Object { $chars[Get-Random -Max $chars.Length] })
}

function Test-Exe($name) {
  if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
    throw "$name not found in PATH."
  }
}

# Defaults
if (-not $SiteTitle) { $SiteTitle = $SiteName }
$SiteUrl = "http://$SiteName.test"

$slug = ($SiteName -replace '[^a-zA-Z0-9_]', '_').ToLower()
if ([string]::IsNullOrWhiteSpace($slug)) { $slug = "wp_site" }

$dbName = "wp_$slug"
$dbUser = "wp_$slug"
$dbPass = New-RandomString 24
$tablePrefix = "wp_"
$adminPass = New-RandomString 20

# Paths
$ProjectDir = Join-Path $LaragonWWW $SiteName
New-Item -ItemType Directory -Force -Path $ProjectDir | Out-Null

# Required CLIs from Laragon
Test-Exe "php"
Test-Exe "mysql"

# Ensure wp-cli.phar
$wpCli = Join-Path $ProjectDir "wp-cli.phar"
$globalWpCli = "C:\laragon\bin\wp-cli\wp-cli.phar"
if (-not (Test-Path $wpCli)) {
  if (Test-Path $globalWpCli) {
    $wpCli = $globalWpCli
  } else {
    Write-Host "Downloading wp-cli.phar..."
    Invoke-WebRequest -UseBasicParsing -Uri "https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar" -OutFile $wpCli
  }
}

function Invoke-WP([string[]]$args) {
  & php $wpCli @args
  if ($LASTEXITCODE -ne 0) { throw "wp-cli failed: $($args -join ' ')" }
}

# Download WordPress core
Invoke-WP @("core","download","--path=$ProjectDir","--force")

# Provision database and user
$sql = @"
CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$dbUser'@'localhost' IDENTIFIED BY '$dbPass';
GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUser'@'localhost';
FLUSH PRIVILEGES;
"@

if ([string]::IsNullOrEmpty($DBRootPassword)) {
  & mysql -u $DBRootUser -e $sql
} else {
  & mysql -u $DBRootUser -p$DBRootPassword -e $sql
}
if ($LASTEXITCODE -ne 0) { throw "MySQL provisioning failed." }

# Create wp-config.php
Invoke-WP @(
  "config","create",
  "--path=$ProjectDir",
  "--dbname=$dbName",
  "--dbuser=$dbUser",
  "--dbpass=$dbPass",
  "--dbhost=127.0.0.1:3306",
  "--dbprefix=$tablePrefix",
  "--skip-check",
  "--force"
)

# Install WordPress
Invoke-WP @(
  "core","install",
  "--path=$ProjectDir",
  "--url=$SiteUrl",
  "--title=$SiteTitle",
  "--admin_user=$AdminUser",
  "--admin_password=$adminPass",
  "--admin_email=$AdminEmail",
  "--skip-email"
)

# Pretty permalinks
Invoke-WP @("rewrite","structure","/%postname%/","--hard","--path=$ProjectDir")
Invoke-WP @("rewrite","flush","--hard","--path=$ProjectDir")

# Summary
Write-Host ""
Write-Host "WordPress is ready:"
Write-Host "  URL: $SiteUrl"
Write-Host "  Path: $ProjectDir"
Write-Host "  Admin: $AdminUser"
Write-Host "  Admin password: $adminPass"
Write-Host "  DB: name=$dbName user=$dbUser pass=$dbPass"