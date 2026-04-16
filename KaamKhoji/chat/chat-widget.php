<?php
// ============================================================
// chat/chat-widget.php — Floating chat widget (LinkedIn-style)
// Include in includes/footer.php just before </body>
// Self-sufficient: handles session and auth internally.
// ============================================================

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bail out if not logged in or not the right role
$_ck_user_id = $_SESSION['user_id'] ?? null;
$_ck_role    = $_SESSION['role']    ?? null;
$_ck_name    = $_SESSION['name']    ?? null;

if (!$_ck_user_id || !in_array($_ck_role, ['seeker', 'employer'])) return;

// BASE_URL is already defined by header.php on every page
$_ck_base = BASE_URL . '/chat';
?>

<!-- ============================================================
     FLOATING CHAT WIDGET
     ============================================================ -->
<div id="ck-chat-root">

  <!-- Tray bar docked at bottom-right -->
  <div id="ck-tray" onclick="ckTogglePanel()">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4a2 2 0 00-2 2v18l4-4h14a2 2 0 002-2V4a2 2 0 00-2-2z"/></svg>
    <span>Messages</span>
    <span id="ck-tray-badge" class="ck-badge" style="display:none">0</span>
    <svg id="ck-tray-chevron" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="margin-left:auto;transition:transform .2s"><path d="M7 14l5-5 5 5z"/></svg>
  </div>

  <!-- Contact list panel -->
  <div id="ck-panel" class="ck-hidden">
    <div class="ck-panel-header">
      <span>Messaging</span>
      <button onclick="ckTogglePanel()" class="ck-icon-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
      </button>
    </div>
    <div class="ck-search-wrap">
      <input type="text" id="ck-search" placeholder="Search messages…" oninput="ckFilterContacts(this.value)">
    </div>
    <div id="ck-contacts"><p class="ck-empty">Loading…</p></div>
    <div class="ck-panel-footer">
      <a href="<?= htmlspecialchars($_ck_base) ?>/chat.php">Open full view</a>
    </div>
  </div>

  <!-- Chat windows (up to 2 open side by side) -->
  <div id="ck-windows"></div>

</div>

<style>
#ck-chat-root *{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',sans-serif}
#ck-chat-root{position:fixed;bottom:0;right:24px;display:flex;align-items:flex-end;gap:10px;z-index:9999}
#ck-tray{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #e0e0e0;border-bottom:none;border-radius:8px 8px 0 0;padding:10px 16px;cursor:pointer;font-size:14px;font-weight:600;color:#000;width:240px;box-shadow:0 -2px 8px rgba(0,0,0,.08);user-select:none;transition:background .15s}
#ck-tray:hover{background:#f3f6fb}
#ck-tray svg:first-child{color:#0a66c2}
#ck-panel{position:absolute;bottom:44px;right:0;width:320px;background:#fff;border:1px solid #e0e0e0;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.15);display:flex;flex-direction:column;max-height:480px;overflow:hidden}
#ck-panel.ck-hidden{display:none}
.ck-panel-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px 10px;font-size:16px;font-weight:700;border-bottom:1px solid #e8e8e8}
.ck-search-wrap{padding:8px 12px;border-bottom:1px solid #f0f0f0}
.ck-search-wrap input{width:100%;padding:7px 12px;border:1px solid #ddd;border-radius:20px;font-size:13px;outline:none;background:#f3f4f6;color:#111}
.ck-search-wrap input:focus{border-color:#0a66c2;background:#fff}
#ck-contacts{flex:1;overflow-y:auto;max-height:320px}
.ck-contact{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background .12s;border-bottom:1px solid #f5f5f5}
.ck-contact:hover{background:#f3f6fb}
.ck-avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;position:relative}
.ck-online{position:absolute;bottom:1px;right:1px;width:10px;height:10px;border-radius:50%;background:#57b96a;border:2px solid #fff}
.ck-cinfo{flex:1;min-width:0}
.ck-cname{font-size:13px;font-weight:600;color:#000;display:flex;align-items:center;justify-content:space-between}
.ck-ctime{font-size:11px;color:#888;white-space:nowrap;margin-left:6px;flex-shrink:0}
.ck-cpreview{font-size:12px;color:#666;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px}
.ck-badge{background:#cc1016;color:#fff;font-size:10px;font-weight:700;border-radius:10px;padding:1px 6px;min-width:18px;text-align:center;flex-shrink:0}
.ck-empty{padding:24px;text-align:center;font-size:13px;color:#999}
.ck-panel-footer{padding:10px 16px;border-top:1px solid #e8e8e8;text-align:center}
.ck-panel-footer a{font-size:12px;color:#0a66c2;text-decoration:none;font-weight:500}
.ck-panel-footer a:hover{text-decoration:underline}
.ck-icon-btn{background:none;border:none;cursor:pointer;color:#666;padding:4px;border-radius:4px;display:flex;align-items:center;justify-content:center}
.ck-icon-btn:hover{background:#f0f0f0;color:#000}
.ck-win{width:300px;background:#fff;border:1px solid #e0e0e0;border-bottom:none;border-radius:8px 8px 0 0;box-shadow:0 -2px 12px rgba(0,0,0,.1);display:flex;flex-direction:column;max-height:440px}
.ck-win-header{display:flex;align-items:center;gap:8px;padding:10px 12px;border-bottom:1px solid #e8e8e8;border-radius:8px 8px 0 0;cursor:pointer;user-select:none;background:#fff}
.ck-win-title{flex:1;min-width:0}
.ck-win-name{font-size:13px;font-weight:600;color:#000;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ck-win-status{font-size:11px;color:#57b96a}
.ck-win-actions{display:flex;gap:2px;flex-shrink:0}
.ck-win-body{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px;min-height:240px}
.ck-win-body.ck-collapsed{display:none}
.ck-win-input{display:flex;align-items:flex-end;gap:8px;padding:8px 10px;border-top:1px solid #e8e8e8}
.ck-win-input.ck-collapsed{display:none}
.ck-win-input textarea{flex:1;resize:none;border:1px solid #ddd;border-radius:20px;padding:8px 12px;font-size:13px;font-family:inherit;line-height:1.35;background:#f9f9f9;outline:none;min-height:36px;max-height:80px;overflow-y:auto}
.ck-win-input textarea:focus{border-color:#0a66c2;background:#fff}
.ck-send-btn{width:34px;height:34px;border-radius:50%;background:#0a66c2;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s,transform .1s}
.ck-send-btn:hover{background:#004182}
.ck-send-btn:active{transform:scale(.92)}
.ck-send-btn svg{fill:#fff;width:15px;height:15px}
.ck-bw{display:flex;flex-direction:column;align-items:flex-end}
.ck-br{display:flex;flex-direction:column;align-items:flex-start}
.ck-bubble{max-width:80%;padding:8px 12px;border-radius:18px;font-size:13px;line-height:1.45;word-break:break-word}
.ck-bw .ck-bubble{background:#0a66c2;color:#fff;border-bottom-right-radius:4px}
.ck-br .ck-bubble{background:#f1f1f1;color:#000;border-bottom-left-radius:4px}
.ck-bmeta{font-size:10px;color:#aaa;margin-top:2px;padding:0 2px}
</style>

<script>
(function(){
const ME   = <?= (int) $_ck_user_id ?>;
const BASE = <?= json_encode($_ck_base) ?>;
const COLS = ['#0a66c2','#7c3aed','#db2777','#d97706','#059669','#e11d48'];

let panelOpen   = false;
let allContacts = [];
let windows     = {};

function col(id) { return COLS[id % COLS.length]; }
function ini(n)  { return n.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase(); }
function esc(s)  { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmt(ts) {
    if (!ts) return '';
    const d = new Date(ts), now = new Date();
    return d.toDateString() === now.toDateString()
        ? d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0')
        : d.toLocaleDateString([], { month:'short', day:'numeric' });
}

// ── Panel toggle ──
window.ckTogglePanel = function() {
    panelOpen = !panelOpen;
    document.getElementById('ck-panel').classList.toggle('ck-hidden', !panelOpen);
    document.getElementById('ck-tray-chevron').style.transform = panelOpen ? 'rotate(180deg)' : '';
    if (panelOpen) loadContacts();
};

// ── Load contacts ──
async function loadContacts() {
    try {
        const r = await fetch(`${BASE}/contacts.php`);
        const d = await r.json();
        allContacts = d.contacts || [];
        renderContacts(allContacts);
        updateBadge();
    } catch(e) {
        document.getElementById('ck-contacts').innerHTML = '<p class="ck-empty">Could not load.</p>';
    }
}

function renderContacts(list) {
    const el = document.getElementById('ck-contacts');
    if (!list.length) { el.innerHTML = '<p class="ck-empty">No contacts yet.</p>'; return; }
    el.innerHTML = list.map(c => `
        <div class="ck-contact" onclick="ckOpenWindow(${c.id}, ${JSON.stringify(c.name)})">
            <div class="ck-avatar" style="background:${col(c.id)}">${ini(c.name)}<span class="ck-online"></span></div>
            <div class="ck-cinfo">
                <div class="ck-cname">${esc(c.name)}<span class="ck-ctime">${fmt(c.last_ts)}</span></div>
                <div class="ck-cpreview">${esc(c.last_message ?? 'No messages yet')}</div>
            </div>
            ${parseInt(c.unread) > 0 ? `<span class="ck-badge">${c.unread}</span>` : ''}
        </div>`).join('');
}

window.ckFilterContacts = function(q) {
    renderContacts(allContacts.filter(c => c.name.toLowerCase().includes(q.toLowerCase())));
};

function updateBadge() {
    const total = allContacts.reduce((s, c) => s + parseInt(c.unread || 0), 0);
    const badge = document.getElementById('ck-tray-badge');
    badge.style.display = total > 0 ? 'inline' : 'none';
    badge.textContent   = total;
}

// ── Open chat window ──
window.ckOpenWindow = function(uid, name) {
    const ids = Object.keys(windows);
    if (!windows[uid] && ids.length >= 2) ckCloseWindow(parseInt(ids[0]));

    if (!windows[uid]) {
        windows[uid] = { collapsed: false, lastTs: null, pollTimer: null };
        renderWindow(uid, name);
    }

    panelOpen = false;
    document.getElementById('ck-panel').classList.add('ck-hidden');
    document.getElementById('ck-tray-chevron').style.transform = '';

    fetchMsgs(uid, true);
    windows[uid].pollTimer = setInterval(() => fetchMsgs(uid, false), 3000);
};

function renderWindow(uid, name) {
    const div = document.createElement('div');
    div.className = 'ck-win';
    div.id = `ck-win-${uid}`;
    div.innerHTML = `
        <div class="ck-win-header" onclick="ckCollapseWindow(${uid})">
            <div class="ck-avatar" style="background:${col(uid)};width:32px;height:32px;font-size:11px">${ini(name)}<span class="ck-online"></span></div>
            <div class="ck-win-title">
                <div class="ck-win-name">${esc(name)}</div>
                <div class="ck-win-status">Active now</div>
            </div>
            <div class="ck-win-actions" onclick="event.stopPropagation()">
                <button class="ck-icon-btn" onclick="ckCloseWindow(${uid})">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </button>
            </div>
        </div>
        <div class="ck-win-body" id="ck-body-${uid}"></div>
        <div class="ck-win-input" id="ck-input-${uid}">
            <textarea placeholder="Write a message…" rows="1"
                onkeydown="ckKey(event,${uid})"
                oninput="ckResize(this)"></textarea>
            <button class="ck-send-btn" onclick="ckSend(${uid})">
                <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>`;
    document.getElementById('ck-windows').appendChild(div);
}

window.ckCollapseWindow = function(uid) {
    if (!windows[uid]) return;
    windows[uid].collapsed = !windows[uid].collapsed;
    document.getElementById(`ck-body-${uid}`)?.classList.toggle('ck-collapsed', windows[uid].collapsed);
    document.getElementById(`ck-input-${uid}`)?.classList.toggle('ck-collapsed', windows[uid].collapsed);
};

window.ckCloseWindow = function(uid) {
    clearInterval(windows[uid]?.pollTimer);
    delete windows[uid];
    document.getElementById(`ck-win-${uid}`)?.remove();
};

// ── Fetch messages ──
async function fetchMsgs(uid, initial) {
    let url = `${BASE}/messages.php?with=${uid}`;
    if (!initial && windows[uid]?.lastTs) url += `&since=${encodeURIComponent(windows[uid].lastTs)}`;
    try {
        const r = await fetch(url);
        const d = await r.json();
        if (!d.success) return;
        if (d.last_ts) windows[uid].lastTs = d.last_ts;

        const body = document.getElementById(`ck-body-${uid}`);
        if (!body) return;

        if (initial) {
            body.innerHTML = (d.messages || []).map(m => bubble(m)).join('');
            body.scrollTop = body.scrollHeight;
        } else if (d.messages?.length > 0) {
            const atBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 60;
            d.messages.forEach(m => body.insertAdjacentHTML('beforeend', bubble(m)));
            if (atBottom) body.scrollTop = body.scrollHeight;
            loadContacts();
        }
    } catch(e) {}
}

function bubble(m) {
    const mine = parseInt(m.from_id) === ME;
    return `<div class="${mine ? 'ck-bw' : 'ck-br'}">
        <div class="ck-bubble">${esc(m.body)}</div>
        <div class="ck-bmeta">${fmt(m.sent_at)}${mine ? ' ✓✓' : ''}</div>
    </div>`;
}

// ── Send ──
window.ckSend = async function(uid) {
    const wrap = document.getElementById(`ck-input-${uid}`);
    if (!wrap) return;
    const ta   = wrap.querySelector('textarea');
    const body = ta.value.trim();
    if (!body) return;
    ta.value = '';
    ta.style.height = 'auto';
    const fd = new FormData();
    fd.append('to_id', uid);
    fd.append('body', body);
    try {
        const r = await fetch(`${BASE}/send.php`, { method: 'POST', body: fd });
        const d = await r.json();
        if (d.success) {
            fetchMsgs(uid, false);
        } else {
            alert(d.error ?? 'Could not send message.');
        }
    } catch(e) {}
};

window.ckKey    = function(e, uid) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); ckSend(uid); } };
window.ckResize = function(el)     { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 80) + 'px'; };

// ── Init ──
loadContacts();
setInterval(loadContacts, 10000);
})();
</script>