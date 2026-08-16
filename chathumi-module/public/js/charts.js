/**
 * Minimal canvas chart helpers — no Chart.js/D3, hand-rolled to
 * comply with the "no external libraries" project rule.
 */

function drawLineChart(canvasId, labels, values, opts = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const w = canvas.clientWidth, h = canvas.clientHeight;
    canvas.width = w * dpr; canvas.height = h * dpr;
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    const pad = { top: 14, right: 14, bottom: 26, left: 14 };
    const plotW = w - pad.left - pad.right;
    const plotH = h - pad.top - pad.bottom;
    const max = Math.max(...values) * 1.15 || 1;
    const stepX = plotW / (labels.length - 1);
    const color = opts.color || '#4f5bd5';

    // gridlines
    ctx.strokeStyle = '#eef0f5';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = pad.top + (plotH / 4) * i;
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(w - pad.right, y); ctx.stroke();
    }

    // area fill
    ctx.beginPath();
    values.forEach((v, i) => {
        const x = pad.left + stepX * i;
        const y = pad.top + plotH - (v / max) * plotH;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.lineTo(pad.left + stepX * (values.length - 1), pad.top + plotH);
    ctx.lineTo(pad.left, pad.top + plotH);
    ctx.closePath();
    const grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + plotH);
    grad.addColorStop(0, color + '33');
    grad.addColorStop(1, color + '00');
    ctx.fillStyle = grad;
    ctx.fill();

    // line
    ctx.beginPath();
    values.forEach((v, i) => {
        const x = pad.left + stepX * i;
        const y = pad.top + plotH - (v / max) * plotH;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.strokeStyle = color;
    ctx.lineWidth = 2.2;
    ctx.lineJoin = 'round';
    ctx.stroke();

    // points
    values.forEach((v, i) => {
        const x = pad.left + stepX * i;
        const y = pad.top + plotH - (v / max) * plotH;
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, Math.PI * 2);
        ctx.fillStyle = '#fff';
        ctx.fill();
        ctx.lineWidth = 2;
        ctx.strokeStyle = color;
        ctx.stroke();
    });

    // x labels
    ctx.fillStyle = '#94a3b8';
    ctx.font = '11px -apple-system, sans-serif';
    ctx.textAlign = 'center';
    labels.forEach((lbl, i) => {
        const x = pad.left + stepX * i;
        ctx.fillText(lbl, x, h - 6);
    });
}

function drawDonut(canvasId, value, max, opts = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const w = canvas.clientWidth, h = canvas.clientHeight;
    canvas.width = w * dpr; canvas.height = h * dpr;
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    const cx = w / 2, cy = h / 2, r = Math.min(w, h) / 2 - 8;
    const pct = Math.max(0, Math.min(1, value / (max || 1)));

    ctx.lineWidth = 10;
    ctx.strokeStyle = '#eef0f5';
    ctx.beginPath();
    ctx.arc(cx, cy, r, 0, Math.PI * 2);
    ctx.stroke();

    ctx.lineWidth = 10;
    ctx.strokeStyle = opts.color || '#4f5bd5';
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.arc(cx, cy, r, -Math.PI / 2, -Math.PI / 2 + pct * Math.PI * 2);
    ctx.stroke();
}
