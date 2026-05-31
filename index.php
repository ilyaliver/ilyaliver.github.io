<?php
// index.php — ITD Banner Changer
// Токен не хранится на сервере — только в sessionStorage браузера.
// Все запросы к API идут напрямую из браузера через JS.
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ITD · Баннер</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Mulish:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
/* ─── RESET & BASE ─────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:        #0d0d0f;
  --surface:   #16161a;
  --border:    #2a2a32;
  --amber:     #f5a623;
  --amber-dim: #c47e0f;
  --text:      #e8e6e1;
  --muted:     #6b6878;
  --danger:    #e05c5c;
  --success:   #5ce07a;
  --radius:    14px;
  --font-head: 'Bebas Neue', sans-serif;
  --font-body: 'Mulish', sans-serif;
}

html, body {
  height: 100%;
  background: var(--bg);
  color: var(--text);
  font-family: var(--font-body);
  font-weight: 400;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}

/* ─── NOISE OVERLAY ────────────────────────────────────────── */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
  opacity: 0.025;
  pointer-events: none;
  z-index: 0;
}

/* ─── PAGES ────────────────────────────────────────────────── */
.page {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  z-index: 1;
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.page.hidden {
  opacity: 0;
  pointer-events: none;
  transform: translateY(12px);
}

/* ─── LOGIN PAGE ───────────────────────────────────────────── */
#login-page {
  flex-direction: column;
  gap: 0;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 40px 36px 36px;
  position: relative;
  overflow: hidden;
}
.login-card::after {
  content: '';
  position: absolute;
  top: -60px; right: -60px;
  width: 200px; height: 200px;
  background: radial-gradient(circle, rgba(245,166,35,0.12) 0%, transparent 70%);
  pointer-events: none;
}

.login-logo {
  font-family: var(--font-head);
  font-size: 52px;
  letter-spacing: 2px;
  color: var(--amber);
  line-height: 1;
  margin-bottom: 6px;
}
.login-sub {
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 32px;
}

.field-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 8px;
}
.field-hint {
  font-size: 11px;
  color: var(--muted);
  margin-top: 8px;
  line-height: 1.5;
}

.token-input {
  width: 100%;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 13px 16px;
  color: var(--text);
  font-family: 'Courier New', monospace;
  font-size: 13px;
  outline: none;
  transition: border-color 0.2s;
  letter-spacing: 0.02em;
}
.token-input::placeholder { color: var(--muted); }
.token-input:focus { border-color: var(--amber); }
.token-input.error { border-color: var(--danger); }

.btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  margin-top: 20px;
  padding: 14px;
  background: var(--amber);
  color: #0d0d0f;
  border: none;
  border-radius: 8px;
  font-family: var(--font-body);
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.04em;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s, opacity 0.2s;
}
.btn-primary:hover { background: #fbb940; }
.btn-primary:active { transform: scale(0.98); }
.btn-primary:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }

.login-error {
  margin-top: 14px;
  padding: 10px 14px;
  background: rgba(224,92,92,0.12);
  border: 1px solid rgba(224,92,92,0.3);
  border-radius: 8px;
  color: var(--danger);
  font-size: 13px;
  display: none;
}
.login-error.show { display: block; }

/* ─── DASHBOARD PAGE ───────────────────────────────────────── */
#dashboard-page {
  align-items: flex-start;
  justify-content: flex-start;
  flex-direction: column;
  padding: 0;
}

.dash-inner {
  width: 100%;
  max-width: 720px;
  margin: 0 auto;
  padding: 48px 24px 48px;
  display: flex;
  flex-direction: column;
  gap: 32px;
}

/* Header row */
.dash-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.user-greeting {
  flex: 1;
  min-width: 0;
}
.greeting-line {
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex-wrap: wrap;
}
.greeting-time {
  font-size: 15px;
  font-weight: 300;
  color: var(--muted);
}
.greeting-name {
  font-family: var(--font-head);
  font-size: 42px;
  line-height: 1;
  color: var(--text);
  letter-spacing: 1px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 100%;
}
.greeting-name span {
  color: var(--amber);
}

.stats-row {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 10px;
  flex-wrap: wrap;
}
.stat-chip {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--muted);
}
.stat-chip strong {
  font-size: 16px;
  font-weight: 700;
  color: var(--text);
}
.stat-sep {
  width: 3px; height: 3px;
  border-radius: 50%;
  background: var(--border);
}

.btn-logout {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--muted);
  font-family: var(--font-body);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s, background 0.2s;
  white-space: nowrap;
}
.btn-logout:hover {
  border-color: var(--danger);
  color: var(--danger);
  background: rgba(224,92,92,0.06);
}

/* Divider */
.divider {
  height: 1px;
  background: var(--border);
}

/* Banner upload zone */
.banner-section {}
.section-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 16px;
}

.drop-zone {
  position: relative;
  width: 100%;
  aspect-ratio: 3 / 1;
  min-height: 160px;
  max-height: 280px;
  border: 2px dashed var(--border);
  border-radius: var(--radius);
  background: var(--surface);
  cursor: pointer;
  overflow: hidden;
  transition: border-color 0.25s, background 0.25s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.drop-zone:hover,
.drop-zone.drag-over {
  border-color: var(--amber);
  background: rgba(245,166,35,0.04);
}
.drop-zone.drag-over { border-style: solid; }

.drop-zone input[type=file] {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
  width: 100%;
  height: 100%;
}

.drop-content {
  text-align: center;
  padding: 24px;
  pointer-events: none;
  transition: opacity 0.2s;
}
.drop-icon {
  width: 44px; height: 44px;
  margin: 0 auto 12px;
  color: var(--muted);
  transition: color 0.2s;
}
.drop-zone:hover .drop-icon,
.drop-zone.drag-over .drop-icon { color: var(--amber); }

.drop-title {
  font-size: 15px;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 4px;
}
.drop-hint {
  font-size: 12px;
  color: var(--muted);
}

/* Preview overlay */
.banner-preview {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  opacity: 0;
  transition: opacity 0.35s;
  pointer-events: none;
}
.banner-preview.show { opacity: 1; }
.banner-preview-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.65) 100%);
  opacity: 0;
  transition: opacity 0.2s;
  pointer-events: none;
}
.drop-zone:hover .banner-preview-overlay.show { opacity: 1; }

.preview-label {
  position: absolute;
  bottom: 12px;
  left: 14px;
  font-size: 12px;
  font-weight: 600;
  color: rgba(255,255,255,0.7);
  opacity: 0;
  transition: opacity 0.2s;
  pointer-events: none;
}
.drop-zone:hover .preview-label.show { opacity: 1; }

/* Upload button */
.btn-upload {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  margin-top: 14px;
  width: 100%;
  padding: 14px;
  background: transparent;
  border: 1px solid var(--amber);
  border-radius: 8px;
  color: var(--amber);
  font-family: var(--font-body);
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.03em;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, transform 0.1s, opacity 0.2s;
}
.btn-upload:hover {
  background: var(--amber);
  color: #0d0d0f;
}
.btn-upload:active { transform: scale(0.98); }
.btn-upload:disabled { opacity: 0.35; cursor: not-allowed; transform: none; }
.btn-upload.hidden { display: none; }

/* Status toast */
.toast {
  position: fixed;
  bottom: 28px;
  left: 50%;
  transform: translateX(-50%) translateY(20px);
  padding: 12px 22px;
  border-radius: 100px;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s, transform 0.3s;
  z-index: 999;
  max-width: calc(100vw - 32px);
  text-align: center;
}
.toast.show {
  opacity: 1;
  transform: translateX(-50%) translateY(0);
}
.toast.success {
  background: rgba(92,224,122,0.15);
  border: 1px solid rgba(92,224,122,0.35);
  color: var(--success);
}
.toast.error {
  background: rgba(224,92,92,0.15);
  border: 1px solid rgba(224,92,92,0.35);
  color: var(--danger);
}
.toast.info {
  background: rgba(245,166,35,0.12);
  border: 1px solid rgba(245,166,35,0.3);
  color: var(--amber);
}

/* Spinner */
@keyframes spin { to { transform: rotate(360deg); } }
.spinner {
  width: 16px; height: 16px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.65s linear infinite;
  flex-shrink: 0;
}

/* Fade-in anim */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
.dash-inner > * {
  animation: fadeUp 0.45s ease both;
}
.dash-inner > *:nth-child(1) { animation-delay: 0.05s; }
.dash-inner > *:nth-child(2) { animation-delay: 0.12s; }
.dash-inner > *:nth-child(3) { animation-delay: 0.19s; }
.dash-inner > *:nth-child(4) { animation-delay: 0.26s; }

/* ─── RESPONSIVE ───────────────────────────────────────────── */
@media (max-width: 480px) {
  .login-card { padding: 32px 22px 28px; }
  .greeting-name { font-size: 34px; }
  .dash-header { flex-wrap: wrap; }
  .btn-logout { order: -1; align-self: flex-end; margin-left: auto; }
}
</style>
</head>
<body>

<!-- ══════════════ LOGIN PAGE ══════════════ -->
<div id="login-page" class="page">
  <div class="login-card">
    <div class="login-logo">ИТД</div>
    <div class="login-sub">Управление баннером профиля</div>

    <label class="field-label" for="token-input">Refresh Token</label>
    <input
      id="token-input"
      class="token-input"
      type="password"
      placeholder="Вставьте токен..."
      autocomplete="off"
      spellcheck="false"
    >
    <p class="field-hint">
      Токен не передаётся на сервер — используется только в вашем браузере.
    </p>

    <button id="login-btn" class="btn-primary">
      Войти
    </button>

    <div id="login-error" class="login-error"></div>
  </div>
</div>

<!-- ══════════════ DASHBOARD PAGE ══════════════ -->
<div id="dashboard-page" class="page hidden">
  <div class="dash-inner">

    <!-- Header -->
    <div class="dash-header">
      <div class="user-greeting">
        <div class="greeting-line">
          <span class="greeting-time" id="greeting-time">Добрый день,</span>
        </div>
        <div class="greeting-name" id="greeting-name">
          <span id="username-display">—</span>
        </div>
        <div class="stats-row">
          <div class="stat-chip">
            <strong id="followers-count">—</strong>
            подписчиков
          </div>
          <div class="stat-sep"></div>
          <div class="stat-chip">
            подписан на <strong id="following-count">—</strong>
          </div>
        </div>
      </div>
      <button id="logout-btn" class="btn-logout">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Выйти
      </button>
    </div>

    <div class="divider"></div>

    <!-- Banner section -->
    <div class="banner-section">
      <p class="section-label">Баннер профиля</p>

      <div class="drop-zone" id="drop-zone">
        <input type="file" id="file-input" accept="image/*">

        <div class="banner-preview" id="banner-preview"></div>
        <div class="banner-preview-overlay" id="preview-overlay"></div>
        <div class="preview-label" id="preview-label">Нажмите, чтобы сменить</div>

        <div class="drop-content" id="drop-content">
          <svg class="drop-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
          <div class="drop-title">Перетащите изображение сюда</div>
          <div class="drop-hint">или нажмите, чтобы выбрать файл · JPG, PNG, WEBP</div>
        </div>
      </div>

      <button id="upload-btn" class="btn-upload hidden">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
        Загрузить баннер
      </button>
    </div>

  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<!-- ══════════════ SCRIPT ══════════════ -->
<script>
(function () {
  'use strict';

  const API = 'https://xn--d1ah4a.com/api';

  // ── State ─────────────────────────────────────────────────
  let refreshToken = null;
  let accessToken  = null;
  let selectedFile = null;

  // ── Elements ──────────────────────────────────────────────
  const loginPage      = document.getElementById('login-page');
  const dashPage       = document.getElementById('dashboard-page');
  const tokenInput     = document.getElementById('token-input');
  const loginBtn       = document.getElementById('login-btn');
  const loginError     = document.getElementById('login-error');
  const logoutBtn      = document.getElementById('logout-btn');

  const greetingTime   = document.getElementById('greeting-time');
  const usernameEl     = document.getElementById('username-display');
  const followersEl    = document.getElementById('followers-count');
  const followingEl    = document.getElementById('following-count');

  const dropZone       = document.getElementById('drop-zone');
  const fileInput      = document.getElementById('file-input');
  const dropContent    = document.getElementById('drop-content');
  const bannerPreview  = document.getElementById('banner-preview');
  const previewOverlay = document.getElementById('preview-overlay');
  const previewLabel   = document.getElementById('preview-label');
  const uploadBtn      = document.getElementById('upload-btn');
  const toast          = document.getElementById('toast');

  // ── Greeting ──────────────────────────────────────────────
  function getGreeting() {
    const h = new Date().getHours();
    if (h >= 5  && h < 12) return 'Доброе утро,';
    if (h >= 12 && h < 18) return 'Добрый день,';
    if (h >= 18 && h < 23) return 'Добрый вечер,';
    return 'Доброй ночи,';
  }

  // ── Toast ─────────────────────────────────────────────────
  let toastTimer = null;
  function showToast(msg, type = 'info', duration = 3500) {
    clearTimeout(toastTimer);
    toast.textContent = msg;
    toast.className = `toast ${type} show`;
    toastTimer = setTimeout(() => {
      toast.classList.remove('show');
    }, duration);
  }

  // ── Show/hide pages ───────────────────────────────────────
  function showDashboard() {
    loginPage.classList.add('hidden');
    dashPage.classList.remove('hidden');
  }
  function showLogin() {
    dashPage.classList.add('hidden');
    loginPage.classList.remove('hidden');
    tokenInput.value = '';
    loginError.classList.remove('show');
  }

  // ── Error display ─────────────────────────────────────────
  function setLoginError(msg) {
    loginError.textContent = msg;
    loginError.classList.add('show');
    tokenInput.classList.add('error');
  }
  function clearLoginError() {
    loginError.classList.remove('show');
    tokenInput.classList.remove('error');
  }

  // ── API helpers ───────────────────────────────────────────
  async function refreshAccessToken(rToken) {
    const res = await fetch(`${API}/v1/auth/refresh`, {
      method: 'POST',
      headers: { 'Cookie': `refresh_token=${rToken}` },
      // Токен передаём вручную, без credentials: 'include',
      // чтобы не трогать куки сессии браузера на итд.com
      credentials: 'omit',
      body: JSON.stringify({ refreshToken: rToken }),
    });

    // Платформа принимает токен как в куках (через include), так и в теле.
    // Пробуем оба варианта: сначала тело, потом credentials.
    if (!res.ok) {
      // Второй вариант — credentials: include (если пользователь уже авторизован в браузере)
      const res2 = await fetch(`${API}/v1/auth/refresh`, {
        method: 'POST',
        credentials: 'include',
      });
      if (!res2.ok) throw new Error(`Ошибка авторизации (${res2.status})`);
      const d2 = await res2.json();
      if (!d2.accessToken) throw new Error('Сервер не вернул accessToken');
      return d2.accessToken;
    }

    const d = await res.json();
    if (!d.accessToken) throw new Error('Сервер не вернул accessToken');
    return d.accessToken;
  }

  async function getMe(aToken) {
    const res = await fetch(`${API}/users/me`, {
      headers: { 'Authorization': `Bearer ${aToken}` },
    });
    if (!res.ok) throw new Error(`Не удалось получить профиль (${res.status})`);
    return res.json();
  }

  async function uploadFile(aToken, file) {
    const fd = new FormData();
    fd.append('file', file);
    const res = await fetch(`${API}/files/upload`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${aToken}` },
      body: fd,
    });
    if (!res.ok) throw new Error(`Ошибка загрузки файла (${res.status})`);
    const d = await res.json();
    if (!d.id) throw new Error('Сервер не вернул id файла');
    return d.id;
  }

  async function updateBanner(aToken, bannerId) {
    const res = await fetch(`${API}/users/me`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${aToken}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ bannerId }),
    });
    if (!res.ok) throw new Error(`Ошибка обновления баннера (${res.status})`);
    return res.json();
  }

  // ── Login flow ─────────────────────────────────────────────
  async function doLogin() {
    const token = tokenInput.value.trim();
    if (!token) {
      setLoginError('Введите refresh token');
      return;
    }

    clearLoginError();
    loginBtn.disabled = true;
    loginBtn.innerHTML = `<div class="spinner"></div> Проверка...`;

    try {
      // 1. Обмениваем refresh token на access token
      const aToken = await refreshAccessToken(token);
      accessToken  = aToken;
      refreshToken = token;

      // 2. Получаем профиль
      const me = await getMe(aToken);

      // 3. Заполняем UI
      greetingTime.textContent = getGreeting();
      usernameEl.textContent   = me.username || me.displayName || 'пользователь';
      followersEl.textContent  = formatNum(me.followersCount ?? 0);
      followingEl.textContent  = formatNum(me.followingCount ?? 0);

      // 4. Переходим на дашборд
      showDashboard();

    } catch (err) {
      setLoginError(err.message || 'Неизвестная ошибка');
    } finally {
      loginBtn.disabled = false;
      loginBtn.innerHTML = 'Войти';
    }
  }

  // ── Logout ─────────────────────────────────────────────────
  // Намеренно НЕ вызываем /api/v1/auth/logout,
  // чтобы не сбить сессию пользователя на итд.com
  function doLogout() {
    refreshToken = null;
    accessToken  = null;
    selectedFile = null;
    resetDropZone();
    showLogin();
  }

  // ── File preview ───────────────────────────────────────────
  function setPreview(file) {
    const url = URL.createObjectURL(file);
    bannerPreview.style.backgroundImage = `url('${url}')`;
    bannerPreview.classList.add('show');
    previewOverlay.classList.add('show');
    previewLabel.classList.add('show');
    dropContent.style.opacity = '0';
    uploadBtn.classList.remove('hidden');
  }

  function resetDropZone() {
    bannerPreview.style.backgroundImage = '';
    bannerPreview.classList.remove('show');
    previewOverlay.classList.remove('show');
    previewLabel.classList.remove('show');
    dropContent.style.opacity = '1';
    uploadBtn.classList.add('hidden');
    fileInput.value = '';
    selectedFile = null;
  }

  // ── Upload flow ────────────────────────────────────────────
  async function doUpload() {
    if (!selectedFile) return;

    uploadBtn.disabled = true;
    uploadBtn.innerHTML = `<div class="spinner"></div> Загрузка...`;

    try {
      showToast('Обновляем токен...', 'info', 60000);

      // Обновляем токен перед загрузкой
      accessToken = await refreshAccessToken(refreshToken);

      showToast('Загружаем файл...', 'info', 60000);
      const fileId = await uploadFile(accessToken, selectedFile);

      showToast('Применяем баннер...', 'info', 60000);
      await updateBanner(accessToken, fileId);

      showToast('Баннер успешно обновлён!', 'success');

    } catch (err) {
      showToast(err.message || 'Ошибка загрузки', 'error', 5000);
    } finally {
      uploadBtn.disabled = false;
      uploadBtn.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/>
          <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
        </svg>
        Загрузить баннер
      `;
    }
  }

  // ── Helpers ────────────────────────────────────────────────
  function formatNum(n) {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1).replace('.0','') + 'M';
    if (n >= 1_000)     return (n / 1_000).toFixed(1).replace('.0','') + 'K';
    return String(n);
  }

  // ── Events ─────────────────────────────────────────────────
  loginBtn.addEventListener('click', doLogin);
  tokenInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') doLogin();
    clearLoginError();
  });
  tokenInput.addEventListener('input', clearLoginError);

  logoutBtn.addEventListener('click', doLogout);
  uploadBtn.addEventListener('click', doUpload);

  // File input change
  fileInput.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    selectedFile = file;
    setPreview(file);
  });

  // Drag & drop
  dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
  });
  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag-over');
  });
  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) {
      showToast('Поддерживаются только изображения', 'error');
      return;
    }
    selectedFile = file;
    setPreview(file);
  });

})();
</script>
</body>
</html>
