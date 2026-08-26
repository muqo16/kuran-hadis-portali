[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "  Kur'an-ı Kerim Arama & Okuma Uygulaması" -ForegroundColor Green
Write-Host "  Meal: Prof. Dr. Yaşar Nuri Öztürk" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "PHP Yerleşik Sunucusu Başlatılıyor: http://localhost:8000" -ForegroundColor Cyan
Start-Process "http://localhost:8000"
php -S localhost:8000
