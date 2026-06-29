param(
    [Parameter(Mandatory = $true)][string]$GmailUser,
    [Parameter(Mandatory = $true)][string]$GmailPass
)

$GmailUser = $GmailUser.Trim()
$GmailPass = $GmailPass.Trim() -replace '\s+', ''

if ($GmailUser -notmatch '@') {
    Write-Host 'Email Gmail tidak valid.' -ForegroundColor Red
    exit 1
}

if ($GmailPass.Length -lt 16) {
    Write-Host 'App Password harus 16 karakter (dari Google).' -ForegroundColor Red
    exit 1
}

$envFile = Join-Path $PSScriptRoot '..' '.env'
$envFile = [System.IO.Path]::GetFullPath($envFile)

if (-not (Test-Path $envFile)) {
    Write-Host ".env tidak ditemukan: $envFile" -ForegroundColor Red
    exit 1
}

$lines = Get-Content $envFile -Encoding UTF8
$found = @{
    SMTPHost   = $false
    SMTPUser   = $false
    SMTPPass   = $false
    SMTPPort   = $false
    SMTPCrypto = $false
    fromEmail  = $false
}

$newLines = foreach ($line in $lines) {
    if ($line -match '^email\.SMTPHost\s*=') {
        $found.SMTPHost = $true
        'email.SMTPHost = smtp.gmail.com'
    } elseif ($line -match '^email\.SMTPUser\s*=') {
        $found.SMTPUser = $true
        "email.SMTPUser = $GmailUser"
    } elseif ($line -match '^email\.SMTPPass\s*=') {
        $found.SMTPPass = $true
        "email.SMTPPass = $GmailPass"
    } elseif ($line -match '^email\.SMTPPort\s*=') {
        $found.SMTPPort = $true
        'email.SMTPPort = 587'
    } elseif ($line -match '^email\.SMTPCrypto\s*=') {
        $found.SMTPCrypto = $true
        'email.SMTPCrypto = tls'
    } elseif ($line -match '^email\.fromEmail\s*=') {
        $found.fromEmail = $true
        "email.fromEmail = $GmailUser"
    } else {
        $line
    }
}

Set-Content -Path $envFile -Value $newLines -Encoding UTF8

$verify = Get-Content $envFile -Raw
if ($verify -notmatch [regex]::Escape($GmailUser)) {
    Write-Host 'Gagal menulis ke .env. Isi manual baris email.SMTPUser dan email.SMTPPass.' -ForegroundColor Red
    exit 1
}

Write-Host "Berhasil! Gmail SMTP disimpan untuk $GmailUser" -ForegroundColor Green
Write-Host "Restart Apache / php spark serve lalu uji: php spark test-email $GmailUser" -ForegroundColor Cyan
