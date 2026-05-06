param(
	[switch]$DryRun,
	[switch]$SkipBackup,
	[switch]$SkipRollbackTest,
	[string]$EnvFile = ".env"
)

$ErrorActionPreference = "Stop"

function Write-Section {
	param([string]$Title)
	Write-Host ""
	Write-Host "=== $Title ===" -ForegroundColor Cyan
}

function Get-EnvValue {
	param(
		[string]$Path,
		[string]$Key
	)

	if (-not (Test-Path $Path)) {
		return $null
	}

	$line = Get-Content $Path | Where-Object {
		$_ -match "^\s*${Key}\s*="
	} | Select-Object -First 1

	if (-not $line) {
		return $null
	}

	$value = ($line -split "=", 2)[1].Trim()
	if ($value.StartsWith('"') -and $value.EndsWith('"')) {
		$value = $value.Trim('"')
	}
	if ($value.StartsWith("'") -and $value.EndsWith("'")) {
		$value = $value.Trim("'")
	}

	return $value
}

function Invoke-PHPCheck {
	param([string]$PhpCode)

	$tmpFile = Join-Path $env:TEMP ("rms-check-" + [guid]::NewGuid().ToString() + ".php")
	Set-Content -Path $tmpFile -Value $PhpCode -Encoding ASCII

	try {
		& php $tmpFile
		if ($LASTEXITCODE -ne 0) {
			throw "PHP check failed with exit code $LASTEXITCODE"
		}
	}
	finally {
		Remove-Item $tmpFile -ErrorAction SilentlyContinue
	}
}

Write-Section "Context"
Write-Host "Working directory: $PWD"
Write-Host "DryRun: $DryRun"
Write-Host "SkipBackup: $SkipBackup"

Write-Section "Load DB Env"
$dbHost = Get-EnvValue -Path $EnvFile -Key "database.default.hostname"
$dbPort = Get-EnvValue -Path $EnvFile -Key "database.default.port"
$dbName = Get-EnvValue -Path $EnvFile -Key "database.default.database"
$dbUser = Get-EnvValue -Path $EnvFile -Key "database.default.username"
$dbPass = Get-EnvValue -Path $EnvFile -Key "database.default.password"

if (-not $dbHost) { $dbHost = "127.0.0.1" }
if (-not $dbPort) { $dbPort = "3306" }

Write-Host "DB Host: $dbHost"
Write-Host "DB Port: $dbPort"
Write-Host "DB Name: $dbName"
Write-Host "DB User: $dbUser"

if (-not $dbName -or -not $dbUser) {
	throw "Missing DB settings in .env (database.default.database / username)."
}

$env:RMS_DB_HOST = $dbHost
$env:RMS_DB_PORT = $dbPort
$env:RMS_DB_NAME = $dbName
$env:RMS_DB_USER = $dbUser
$env:RMS_DB_PASS = $dbPass

if (-not $SkipBackup -and -not $DryRun) {
	Write-Section "Backup"
	$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
	$backupDir = Join-Path $PWD "build"
	if (-not (Test-Path $backupDir)) {
		New-Item -ItemType Directory -Path $backupDir | Out-Null
	}
	$backupFile = Join-Path $backupDir ("db-backup-$($dbName)-$timestamp.sql")

	$mysqldump = Get-Command mysqldump -ErrorAction SilentlyContinue
	if (-not $mysqldump) {
		throw "mysqldump not found. Install MySQL client or re-run with -SkipBackup."
	}

	$env:MYSQL_PWD = $dbPass
	try {
		& mysqldump --host=$dbHost --port=$dbPort --user=$dbUser --single-transaction --routines --triggers $dbName > $backupFile
		if ($LASTEXITCODE -ne 0) {
			throw "mysqldump failed with exit code $LASTEXITCODE"
		}
		Write-Host "Backup created: $backupFile" -ForegroundColor Green
	}
	finally {
		Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
	}
}
elseif ($DryRun) {
	Write-Section "Backup"
	Write-Host "DryRun enabled: backup step skipped."
}

Write-Section "Preflight FK Orphan Checks"
$phpPreflight = @'
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');


$host = getenv('RMS_DB_HOST') ?: '127.0.0.1';
$port = getenv('RMS_DB_PORT') ?: '3306';
$db   = getenv('RMS_DB_NAME');
$user = getenv('RMS_DB_USER');
$pass = getenv('RMS_DB_PASS') ?: '';

if (!$db || !$user) {
	fwrite(STDERR, "Missing DB settings in env.\n");
	exit(2);
}

$mysqli = new mysqli($host, $user, $pass, $db, (int)$port);
if ($mysqli->connect_error) {
	fwrite(STDERR, "Connection failed: " . $mysqli->connect_error . "\n");
	exit(3);
}

$sql = "
SELECT
	SUM(CASE WHEN bt.created_by IS NOT NULL AND u1.user_id IS NULL THEN 1 ELSE 0 END) AS created_by_orphans,
	SUM(CASE WHEN bt.approved_by IS NOT NULL AND u2.user_id IS NULL THEN 1 ELSE 0 END) AS approved_by_orphans,
	SUM(CASE WHEN bt.created_by IS NULL THEN 1 ELSE 0 END) AS created_by_null,
	SUM(CASE WHEN bt.approved_by IS NULL THEN 1 ELSE 0 END) AS approved_by_null
FROM borrow_transactions bt
LEFT JOIN users u1 ON u1.user_id = bt.created_by
LEFT JOIN users u2 ON u2.user_id = bt.approved_by
";

$res = $mysqli->query($sql);
if (!$res) {
	fwrite(STDERR, "Query failed: " . $mysqli->error . "\n");
	exit(4);
}

$row = $res->fetch_assoc();
echo "created_by_orphans: " . ($row['created_by_orphans'] ?? 0) . PHP_EOL;
echo "approved_by_orphans: " . ($row['approved_by_orphans'] ?? 0) . PHP_EOL;
echo "created_by_null: " . ($row['created_by_null'] ?? 0) . PHP_EOL;
echo "approved_by_null: " . ($row['approved_by_null'] ?? 0) . PHP_EOL;

if ((int)$row['created_by_orphans'] > 0 || (int)$row['approved_by_orphans'] > 0) {
	fwrite(STDERR, "Orphans detected. Resolve before migrate.\n");
	exit(5);
}

echo "preflight_status: OK" . PHP_EOL;
'@
Invoke-PHPCheck -PhpCode $phpPreflight

Write-Section "Migration Status (Before)"
& php spark migrate:status
if ($LASTEXITCODE -ne 0) {
	throw "migrate:status failed"
}

if ($DryRun) {
	Write-Section "Dry Run Result"
	Write-Host "Dry-run completed. No schema changes were applied." -ForegroundColor Green
	exit 0
}

Write-Section "Run Migrations"
& php spark migrate
if ($LASTEXITCODE -ne 0) {
	throw "migrate failed"
}

Write-Section "Migration Status (After)"
& php spark migrate:status
if ($LASTEXITCODE -ne 0) {
	throw "migrate:status after migrate failed"
}

Write-Section "Post-Migration FK Validation"
$phpFkCheck = @'
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$host = getenv('RMS_DB_HOST') ?: '127.0.0.1';
$port = getenv('RMS_DB_PORT') ?: '3306';
$db   = getenv('RMS_DB_NAME');
$user = getenv('RMS_DB_USER');
$pass = getenv('RMS_DB_PASS') ?: '';

$mysqli = new mysqli($host, $user, $pass, $db, (int)$port);
if ($mysqli->connect_error) {
	fwrite(STDERR, "Connection failed: " . $mysqli->connect_error . "\n");
	exit(2);
}

$sql = "
SELECT constraint_name
FROM information_schema.referential_constraints
WHERE constraint_schema = DATABASE()
  AND table_name = 'borrow_transactions'
  AND constraint_name IN ('fk_borrow_transactions_created_by', 'fk_borrow_transactions_approved_by')
ORDER BY constraint_name
";

$res = $mysqli->query($sql);
if (!$res) {
	fwrite(STDERR, "Query failed: " . $mysqli->error . "\n");
	exit(3);
}

$found = [];
while ($row = $res->fetch_assoc()) {
	$found[] = $row['constraint_name'];
}

echo "fk_found: " . implode(',', $found) . PHP_EOL;

$need = ['fk_borrow_transactions_created_by', 'fk_borrow_transactions_approved_by'];
$missing = array_diff($need, $found);
if (!empty($missing)) {
	fwrite(STDERR, "Missing FK(s): " . implode(',', $missing) . "\n");
	exit(4);
}

echo "fk_validation: OK" . PHP_EOL;
'@
Invoke-PHPCheck -PhpCode $phpFkCheck

Write-Section "Done"
Write-Host "Production-safe migration flow completed successfully." -ForegroundColor Green
