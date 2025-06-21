# WebHarvester - 智能網頁爬蟲系統

WebHarvester 是一個基於 Laravel 後端與 Nuxt.js 前端的現代化網頁爬蟲平台，整合 FireCrawl API 提供強大的網頁資料擷取功能。

## 🌟 特色功能

- 🔐 **完整用戶系統** - 註冊、登入、個人資料管理
- 🕷️ **智能爬蟲** - 整合 FireCrawl API 進行網頁資料擷取
- 📊 **多格式支援** - 支援 Markdown、HTML、純文字等多種輸出格式
- 📱 **跨平台** - 支援桌面與行動裝置

## 🏗️ 系統架構

### 後端 
- Laravel 12

### 前端 
- Nuxt.js 3

## 📋 系統需求

### 後端需求
- PHP 8.2
- Composer
- MySQL

### 前端需求
- Node.js
- npm 

## 🚀 安裝指南

### 1. clone專案
```bash
git clone https://github.com/your-username/WebHarvester.git
cd WebHarvester
```

### 2. 後端設置 (Laravel)

```bash
cd api

# 安裝 PHP 依賴
composer install

# 複製環境設定檔
cp .env.example .env

# 生成應用程式金鑰
php artisan key:generate

# 設置資料庫
php artisan migrate

# 發布 Sanctum 設定
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 啟動開發伺服器
php artisan serve
```

### 3. 前端設置 (Nuxt.js)

```bash
cd web

# 安裝 Node.js 依賴
npm install

# 複製環境設定檔
cp .env.example .env

# 啟動開發伺服器
npm run dev
```

## ⚙️ 環境設定

### 後端環境變數 (.env)
```env

自行設置資料庫
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

```

### 前端環境變數 (.env)
```env

前綴為後端的IP
API_BASE_URL=http://localhost:8000/api
```
## 🎯 使用方式

### 1. 註冊帳號
訪問 `http://localhost:3000/auth/register` 建立新帳號

### 2. 設定 FireCrawl API
1. 登入後前往設定頁面
2. 在 "Firescrawl設定" 區域輸入您的 API 金鑰
3. 儲存設定

### 3. 開始爬蟲
1. 前往爬蟲頁面
2. 輸入要爬取的網址
3. 選擇輸出格式
4. 點擊開始爬蟲

## ⚠️ 已知問題

### 🔑 認證相關
- **忘記密碼功能** - 目前忘記密碼功能需要整合郵件服務，暫時尚未實作完成
  - **臨時解決方案**: 請聯絡系統管理員協助重設密碼

## 📞 聯絡資訊

- **📧 電子郵件**: [t113318101@ntut.org.tw](mailto:t113318101@ntut.org.tw)
- **🔗 GitHub**: [Quent125/WebHarvester](https://github.com/Quent125/WebHarvester)

