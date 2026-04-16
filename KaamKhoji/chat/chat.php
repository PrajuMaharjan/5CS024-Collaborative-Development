<?php
// ============================================================
// chat/chat.php - Main Chat Page
// Usage: chat.php  (shows contact list)
//        chat.php?with=USER_ID  (opens a specific conversation)
// ============================================================

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

// Define BASE_URL if not already set (chat.php is standalone - no header.php)
if (!defined('BASE_URL')) {
    $docRoot     = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    define('BASE_URL', str_replace($docRoot, '', $projectRoot));
}

$openWith = (int) ($_GET['with'] ?? 0);
$withUser = null;

if ($openWith > 0) {
    $pdo  = getPDO();
    $stmt = $pdo->prepare("SELECT id, name, role, location FROM users WHERE id = ?");
    $stmt->execute([$openWith]);
    $withUser = $stmt->fetch();
    if (!$withUser) { $openWith = 0; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - KaamKhoji</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; height: 100vh; display: flex; flex-direction: column; }

        .chat-wrapper   { display: flex; flex: 1; max-width: 1100px; width: 100%; margin: 24px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); overflow: hidden; height: calc(100vh - 48px); }

        .sidebar        { width: 280px; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 18px 16px 14px; border-bottom: 1px solid #e5e7eb; }
        .sidebar-header h2 { font-size: 16px; font-weight: 600; color: #111; }
        .sidebar-header p  { font-size: 12px; color: #6b7280; margin-top: 2px; }

        .contacts-list  { flex: 1; overflow-y: auto; }
        .contact-item   { display: flex; align-items: center; gap: 12px; padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background .12s; text-decoration: none; color: inherit; }
        .contact-item:hover, .contact-item.active { background: #f0f4ff; }
        .avatar         { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; flex-shrink: 0; color: #fff; }
        .contact-info   { flex: 1; min-width: 0; }
        .contact-name   { font-size: 14px; font-weight: 500; color: #111; }
        .contact-preview { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
        .unread-badge   { background: #2563eb; color: #fff; font-size: 11px; font-weight: 600; border-radius: 10px; padding: 2px 7px; min-width: 20px; text-align: center; flex-shrink: 0; }
        .no-contacts    { padding: 32px 16px; text-align: center; color: #9ca3af; font-size: 13px; }

        .chat-panel     { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .chat-header    { padding: 14px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 12px; }
        .chat-header .name { font-size: 15px; font-weight: 600; color: #111; }
        .chat-header .meta { font-size: 12px; color: #6b7280; }

        .messages-area  { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 10px; }

        .bubble-wrap    { display: flex; flex-direction: column; }
        .bubble-wrap.sent     { align-items: flex-end; }
        .bubble-wrap.received { align-items: flex-start; }
        .bubble         { max-width: 68%; padding: 9px 14px; border-radius: 16px; font-size: 14px; line-height: 1.5; word-break: break-word; }
        .sent     .bubble { background: #2563eb; color: #fff; border-bottom-right-radius: 4px; }
        .received .bubble { background: #f3f4f6; color: #111; border-bottom-left-radius: 4px; }
        .bubble-meta    { font-size: 11px; color: #9ca3af; margin-top: 3px; padding: 0 4px; }

        .input-area     { padding: 14px 20px; border-top: 1px solid #e5e7eb; display: flex; gap: 10px; align-items: flex-end; }
        .input-area textarea { flex: 1; resize: none; border: 1px solid #d1d5db; border-radius: 10px; padding: 10px 14px; font-size: 14px; font-family: inherit; line-height: 1.4; background: #f9fafb; color: #111; outline: none; min-height: 42px; max-height: 120px; overflow-y: auto; }
        .input-area textarea:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .send-btn       { width: 42px; height: 42px; border-radius: 50%; background: #2563eb; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background .15s, transform .1s; }
        .send-btn:hover { background: #1d4ed8; }
        .send-btn:active { transform: scale(.92); }
        .send-btn svg   { width: 18px; height: 18px; fill: #fff; }

        .empty-state    { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #9ca3af; gap: 10px; }
        .empty-state svg { width: 48px; height: 48px; opacity: .35; }
        .empty-state p  { font-size: 14px; }
    </style>
</head>
<body>
<div class="chat-wrapper">

    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Messages</h2>
            <p>Logged in as <strong><?= htmlspecialchars(getUserName()) ?></strong></p>
        </div>
        <div class="contacts-list" id="contactsList">
            <p class="no-contacts">Loading conversations...</p>
        </div>
    </div>

    <div class="chat-panel" id="chatPanel">
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p>Select a conversation to start chatting</p>
        </div>
    </div>

</div>

<script>
const ME_ID  = <?= (int) getUserId() ?>;
const BASE   = '<?= BASE_URL ?>/chat';

let activeWith = <?= $openWith ?>;
let lastTs     = null;
let pollTimer  = null;

const COLOURS = ['#2563eb','#7c3aed','#db2777','#d97706','#059669','#dc2626'];
function avatarColor(id) { return COLOURS[id % COLOURS.length]; }
function initials(name)  { return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase(); }
function esc(str)        { return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

async function loadContacts() {
    try {
        const res  = await fetch(`${BASE}/contacts.php`);
        const data = await res.json();
        const el   = document.getElementById('contactsList');

        if (!data.contacts || data.contacts.length === 0) {
            el.innerHTML = '<p class="no-contacts">No contacts yet.</p>';
            return;
        }

        el.innerHTML = data.contacts.map(c => `
            <a class="contact-item${activeWith === parseInt(c.id) ? ' active' : ''}"
               href="javascript:void(0)" onclick="openChat(${c.id}, ${JSON.stringify(c.name)})">
                <div class="avatar" style="background:${avatarColor(c.id)}">${initials(c.name)}</div>
                <div class="contact-info">
                    <div class="contact-name">${esc(c.name)}</div>
                    <div class="contact-preview">${esc(c.last_message ?? 'No messages yet')}</div>
                </div>
                ${parseInt(c.unread) > 0 ? `<div class="unread-badge">${c.unread}</div>` : ''}
            </a>
        `).join('');
    } catch(e) {
        document.getElementById('contactsList').innerHTML = '<p class="no-contacts">Failed to load.</p>';
    }
}

function openChat(uid, name) {
    activeWith = uid;
    lastTs     = null;
    clearInterval(pollTimer);
    loadContacts();

    document.getElementById('chatPanel').innerHTML = `
        <div class="chat-header">
            <div class="avatar" style="background:${avatarColor(uid)};width:38px;height:38px;font-size:13px">${initials(name)}</div>
            <div>
                <div class="name">${esc(name)}</div>
                <div class="meta" id="chatMeta">Loading...</div>
            </div>
        </div>
        <div class="messages-area" id="messagesArea"></div>
        <div class="input-area">
            <textarea id="msgInput" placeholder="Type a message..." rows="1"
                onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
            <button class="send-btn" onclick="sendMsg()">
                <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>`;
    document.getElementById('msgInput').focus();
    fetchMessages(true);
    pollTimer = setInterval(() => fetchMessages(false), 3000);
}

async function fetchMessages(initial) {
    let url = `${BASE}/messages.php?with=${activeWith}`;
    if (!initial && lastTs) url += `&since=${encodeURIComponent(lastTs)}`;
    try {
        const res  = await fetch(url);
        const data = await res.json();
        if (!data.success) return;
        if (data.last_ts) lastTs = data.last_ts;

        const area = document.getElementById('messagesArea');
        if (!area) return;

        if (initial) {
            area.innerHTML = (data.messages || []).map(renderBubble).join('');
            area.scrollTop = area.scrollHeight;
        } else if (data.messages?.length > 0) {
            const atBottom = area.scrollHeight - area.scrollTop - area.clientHeight < 60;
            data.messages.forEach(m => area.insertAdjacentHTML('beforeend', renderBubble(m)));
            if (atBottom) area.scrollTop = area.scrollHeight;
            loadContacts();
        }

        const meta = document.getElementById('chatMeta');
        if (meta) meta.textContent = 'Active recently';
    } catch(e) {}
}

function renderBubble(m) {
    const mine = parseInt(m.from_id) === ME_ID;
    const time = new Date(m.sent_at).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
    return `<div class="bubble-wrap ${mine ? 'sent' : 'received'}">
        <div class="bubble">${esc(m.body)}</div>
        <div class="bubble-meta">${time}${mine ? ' ✓✓' : ''}</div>
    </div>`;
}

async function sendMsg() {
    const input = document.getElementById('msgInput');
    if (!input || !activeWith) return;
    const body = input.value.trim();
    if (!body) return;
    input.value = '';
    input.style.height = 'auto';

    const form = new FormData();
    form.append('to_id', activeWith);
    form.append('body', body);

    try {
        const res  = await fetch(`${BASE}/send.php`, { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) {
            fetchMessages(false);
        } else {
            alert(data.error ?? 'Could not send message.');
        }
    } catch(e) {}
}

function handleKey(e)  { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); } }
function autoResize(el){ el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 120) + 'px'; }

loadContacts();
if (activeWith > 0) {
    const name = <?= $withUser ? json_encode($withUser['name']) : 'null' ?>;
    if (name) openChat(activeWith, name);
}
</script>
</body>
</html>